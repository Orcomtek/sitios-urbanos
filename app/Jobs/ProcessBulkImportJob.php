<?php

namespace App\Jobs;

use App\Models\BulkImport;
use App\Models\Unit;
use App\Models\Resident;
use App\Enums\ResidentType;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Throwable;

class ProcessBulkImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public BulkImport $import) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->import->update(['status' => 'processing']);

        $filePath = Storage::disk('local')->path($this->import->file_path);

        if (! file_exists($filePath)) {
            $this->import->update([
                'status' => 'failed',
                'errors' => ['general' => 'File not found.'],
            ]);

            return;
        }

        try {
            // First pass to count total rows (optional but good for progress)
            // Or just process and increment. Let's just process.
            // Using LazyCollection for memory-safe processing
            $errors = [];
            $processedCount = 0;
            $failedCount = 0;
            $totalCount = 0;

            // Count total lines to update total_rows (minus header)
            $handle = fopen($filePath, 'r');
            while (! feof($handle)) {
                $line = fgets($handle);
                if (trim($line) !== '') {
                    $totalCount++;
                }
            }
            fclose($handle);

            $totalCount = max(0, $totalCount - 1); // Subtract header

            $this->import->update(['total_rows' => $totalCount]);

            LazyCollection::make(function () use ($filePath) {
                $handle = fopen($filePath, 'r');
                $header = fgetcsv($handle); // Read header
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) === 1 && $row[0] === null) {
                        continue; // Skip empty rows
                    }
                    // Combine header with row data if counts match
                    if (count($header) === count($row)) {
                        yield array_combine($header, $row);
                    } else {
                        yield $row; // Yield raw if mismatch, handle in logic
                    }
                }
                fclose($handle);
            })->chunk(100)->each(function ($chunk) use (&$processedCount, &$failedCount, &$errors) {
                foreach ($chunk as $index => $row) {
                    try {
                        // TODO: Implement specific import logic based on $this->import->type
                        // Example switch statement for future implementation
                        switch ($this->import->type) {
                            case 'residents':
                                if (empty($row['unidad_identificador'])) {
                                    throw new Exception('Falta el identificador de la unidad');
                                }
                                $unit = Unit::where('community_id', $this->import->community_id)
                                    ->where('identifier', trim($row['unidad_identificador']))
                                    ->first();
                                
                                if (!$unit) {
                                    throw new Exception("La unidad '" . trim($row['unidad_identificador']) . "' no existe en la matriz");
                                }
                                
                                if (empty($row['nombre'])) {
                                    throw new Exception('Falta el nombre del residente');
                                }
                                
                                $residentTypeMap = [
                                    'propietario' => ResidentType::OWNER,
                                    'inquilino' => ResidentType::TENANT,
                                    'dependiente' => ResidentType::DEPENDENT,
                                ];
                                $tipoStr = strtolower(trim($row['tipo_residente'] ?? 'propietario'));
                                $residentType = $residentTypeMap[$tipoStr] ?? ResidentType::OWNER;
                                
                                Resident::updateOrCreate(
                                    [
                                        'community_id' => $this->import->community_id,
                                        'unit_id' => $unit->id,
                                        'full_name' => trim($row['nombre']),
                                    ],
                                    [
                                        'email' => !empty($row['email']) ? trim($row['email']) : null,
                                        'phone' => !empty($row['telefono']) ? trim($row['telefono']) : null,
                                        'resident_type' => $residentType,
                                    ]
                                );
                                break;
                            case 'balances':
                                // Process balance
                                break;
                            case 'units':
                                if (empty($row['identificador'])) {
                                    throw new Exception('Falta el identificador');
                                }
                                
                                $typeMap = [
                                    'apartamento' => 'apartment',
                                    'casa' => 'house',
                                    'local' => 'commercial',
                                ];
                                
                                $statusMap = [
                                    'ocupada' => 'occupied',
                                    'disponible' => 'available',
                                    'mantenimiento' => 'maintenance',
                                ];
                                
                                $csvType = strtolower(trim($row['tipo_propiedad'] ?? 'apartamento'));
                                $csvStatus = strtolower(trim($row['estado'] ?? 'ocupada'));
                                
                                Unit::updateOrCreate(
                                    [
                                        'community_id' => $this->import->community_id,
                                        'identifier' => trim($row['identificador']),
                                    ],
                                    [
                                        'property_type' => $typeMap[$csvType] ?? 'apartment',
                                        'status' => $statusMap[$csvStatus] ?? 'occupied',
                                    ]
                                );
                                break;
                            default:
                                throw new Exception("Unknown import type: {$this->import->type}");
                        }
                        $processedCount++;
                    } catch (Exception $e) {
                        $failedCount++;
                        $errors[] = [
                            'row' => $processedCount + $failedCount, // Approximate row number
                            'error' => $e->getMessage(),
                        ];
                    }
                }

                // Update progress in database every 100 rows
                $this->import->update([
                    'processed_rows' => $processedCount,
                    'failed_rows' => $failedCount,
                    'errors' => $errors,
                ]);
            });

            $this->import->update([
                'status' => 'completed',
                'processed_rows' => $processedCount,
                'failed_rows' => $failedCount,
                'errors' => $errors,
            ]);

        } catch (Throwable $e) {
            $this->import->update([
                'status' => 'failed',
                'errors' => array_merge($this->import->errors ?? [], ['general' => $e->getMessage()]),
            ]);
        } finally {
            // Ensure the file is deleted from local disk
            if (Storage::disk('local')->exists($this->import->file_path)) {
                Storage::disk('local')->delete($this->import->file_path);
            }
        }
    }
}
