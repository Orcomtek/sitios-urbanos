<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const { show: showToast } = useToast();

const props = defineProps<{
    units: Array<{
        id: number;
        identifier: string;
        amenities: string[];
        status: string;
    }>;
}>();

const emit = defineEmits(['finish']);
const page = usePage();
const communitySlug = computed(() => (page.props.tenant as any)?.community?.slug);

const selectedUnitIds = ref<Set<number>>(new Set());

// Form for bulk assignment
const form = useForm({
    unit_ids: [] as number[],
    amenities: [] as string[],
});

// Mock/fallback for taxonomies if not yet defined in the backend
const availableAmenities = computed(() => {
    return (page.props.taxonomies as any)?.['amenities'] || [
        { value: 'balcony', label: 'Balcón', meta: { color: 'bg-green-400' } },
        { value: 'patio', label: 'Patio', meta: { color: 'bg-yellow-400' } },
        { value: 'storage', label: 'Depósito Interno', meta: { color: 'bg-blue-400' } },
    ];
});

// Dynamic Parser to group units for the "Seat Map" UI
const groupedBlocks = computed(() => {
    const blockMap: Record<string, any> = {};

    props.units.forEach(unit => {
        let blockName = 'General';
        let floor = 1;
        let line = 1;

        // Try to extract numbers at the end
        const match = unit.identifier.match(/^(.*?)(\d+)$/);
        if (match) {
            blockName = match[1].trim() || 'General';
            const numberStr = match[2];
            const num = parseInt(numberStr, 10);
            
            if (numberStr.length >= 3) {
                // Usually formats like 101, 204
                floor = Math.floor(num / 100);
                line = num % 100;
            } else {
                // Formats like 1, 2, 3
                floor = 1;
                line = num;
            }
        } else {
            blockName = unit.identifier;
        }

        if (!blockMap[blockName]) {
            blockMap[blockName] = { name: blockName, floors: {}, lines: new Set() };
        }
        
        if (!blockMap[blockName].floors[floor]) {
            blockMap[blockName].floors[floor] = {};
        }
        
        blockMap[blockName].floors[floor][line] = unit;
        blockMap[blockName].lines.add(line);
    });

    // Format for rendering
    return Object.values(blockMap).map(b => ({
        name: b.name,
        lines: Array.from(b.lines as Set<number>).sort((x, y) => x - y),
        floors: Object.keys(b.floors)
            .map(f => Number(f))
            .sort((x, y) => y - x) // Highest floor on top
            .map(f => ({
                floorNumber: f,
                unitsMap: b.floors[f]
            }))
    }));
});

const selectedSector = ref<string | null>(null);

watch(groupedBlocks, (blocks) => {
    if (blocks.length > 0 && !selectedSector.value) {
        selectedSector.value = blocks[0].name;
    }
}, { immediate: true });

const filteredBlocks = computed(() => {
    if (!selectedSector.value) return [];
    return groupedBlocks.value.filter(b => b.name === selectedSector.value);
});

// Selection Logic
const getAmenityColor = (val: string) => {
    const amenity = availableAmenities.value.find((a: any) => a.value === val);
    return amenity?.meta?.color || 'bg-gray-400';
};
const toggleSelection = (unitId: number) => {
    if (selectedUnitIds.value.has(unitId)) {
        selectedUnitIds.value.delete(unitId);
    } else {
        selectedUnitIds.value.add(unitId);
    }
};

const toggleRow = (blockName: string, floorNumber: number) => {
    const block = groupedBlocks.value.find(b => b.name === blockName);
    if (!block) return;
    
    const floor = block.floors.find(f => f.floorNumber === floorNumber);
    if (!floor) return;

    const unitsInRow = Object.values(floor.unitsMap).map((u: any) => u.id);
    const allSelected = unitsInRow.every(id => selectedUnitIds.value.has(id));

    unitsInRow.forEach(id => {
        if (allSelected) selectedUnitIds.value.delete(id);
        else selectedUnitIds.value.add(id);
    });
};

const toggleColumn = (blockName: string, lineNumber: number) => {
    const block = groupedBlocks.value.find(b => b.name === blockName);
    if (!block) return;
    
    const unitsInCol: number[] = [];
    block.floors.forEach(f => {
        const u = f.unitsMap[lineNumber];
        if (u) unitsInCol.push(u.id);
    });

    const allSelected = unitsInCol.every(id => selectedUnitIds.value.has(id));

    unitsInCol.forEach(id => {
        if (allSelected) selectedUnitIds.value.delete(id);
        else selectedUnitIds.value.add(id);
    });
};

const submitAmenities = () => {
    form.unit_ids = Array.from(selectedUnitIds.value);
    
    if (form.unit_ids.length === 0) {
        showToast('Selecciona al menos una unidad', 'error');
        return;
    }

    form.post(route('tenant.admin.core.units.generator.bulk-amenities', { community_slug: communitySlug.value }), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('¡Amenidades aplicadas con éxito!', 'success');
            selectedUnitIds.value.clear();
            form.reset();
        }
    });
};

const toggleAmenity = (val: string) => {
    const index = form.amenities.indexOf(val);
    if (index > -1) {
        form.amenities.splice(index, 1);
    } else {
        form.amenities.push(val);
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Sticky Action Bar -->
        <div class="sticky top-0 z-10 bg-white border border-gray-200 shadow-sm rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4 flex-wrap">
                <span class="text-sm font-medium text-gray-700">
                    Seleccionadas: <span class="font-bold text-indigo-600">{{ selectedUnitIds.size }}</span>
                </span>
                
                <div class="h-6 w-px bg-gray-300 hidden sm:block"></div>
                
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Amenidades:</span>
                    <label v-for="amenity in availableAmenities" :key="amenity.value" class="inline-flex items-center cursor-pointer group">
                        <input type="checkbox" :checked="form.amenities.includes(amenity.value)" @change="toggleAmenity(amenity.value)" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-600" />
                        <span class="ml-2 flex items-center gap-1.5 text-sm text-gray-700 group-hover:text-gray-900 transition">
                            <span :class="['w-2.5 h-2.5 rounded-full', getAmenityColor(amenity.value)]"></span>
                            {{ amenity.label }}
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button type="button" @click="submitAmenities" :disabled="form.processing || selectedUnitIds.size === 0" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                    {{ form.processing ? 'Aplicando...' : 'Aplicar Amenidades' }}
                </button>
                <button type="button" @click="emit('finish')" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Finalizar
                </button>
            </div>
        </div>

        <!-- Sector Selector Tabs -->
        <div v-if="groupedBlocks.length > 1" class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 overflow-x-auto scrollbar-hide" aria-label="Tabs">
                <button v-for="block in groupedBlocks" :key="block.name"
                    @click="selectedSector = block.name"
                    :class="[
                        selectedSector === block.name
                            ? 'border-indigo-500 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors'
                    ]"
                >
                    {{ block.name }}
                </button>
            </nav>
        </div>

        <!-- Seat Map Matrix -->
        <div v-for="block in filteredBlocks" :key="block.name" class="bg-white shadow sm:rounded-lg overflow-hidden mt-4">
            <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ block.name }}</h3>
            </div>
            <div class="p-4 sm:p-6 overflow-x-auto">
                <div class="inline-block min-w-full">
                    <table class="min-w-full border-separate border-spacing-2">
                        <thead>
                            <tr>
                                <th class="w-12"></th> <!-- Empty corner -->
                                <th v-for="line in block.lines" :key="line" 
                                    @click="toggleColumn(block.name, line)"
                                    class="w-16 sm:w-20 cursor-pointer p-2 rounded bg-gray-50 text-center text-xs font-semibold text-gray-500 hover:bg-gray-200 transition"
                                    title="Seleccionar columna">
                                    L-{{ line }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="floor in block.floors" :key="floor.floorNumber">
                                <td @click="toggleRow(block.name, floor.floorNumber)"
                                    class="cursor-pointer p-2 rounded bg-gray-50 text-center text-xs font-semibold text-gray-500 hover:bg-gray-200 transition"
                                    title="Seleccionar piso">
                                    P{{ floor.floorNumber }}
                                </td>
                                <td v-for="line in block.lines" :key="line" class="p-1">
                                    <div v-if="floor.unitsMap[line]" 
                                         @click="toggleSelection(floor.unitsMap[line].id)"
                                         :class="[
                                            'relative w-full h-16 sm:h-20 rounded-md border-2 cursor-pointer transition flex flex-col items-center justify-center p-1',
                                            selectedUnitIds.has(floor.unitsMap[line].id) 
                                                ? 'border-indigo-600 bg-indigo-50 text-indigo-700 shadow-inner' 
                                                : 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-gray-50 text-gray-900'
                                         ]">
                                        <span class="text-xs font-bold">{{ floor.unitsMap[line].identifier.split('-').pop() || floor.unitsMap[line].identifier.split(' ').pop() }}</span>
                                        <div class="mt-1 flex gap-1 flex-wrap justify-center">
                                            <span v-for="am in (floor.unitsMap[line].amenities || []).slice(0, 3)" :key="am" 
                                                  :class="['w-2 h-2 rounded-full', getAmenityColor(am)]" :title="am"></span>
                                        </div>
                                    </div>
                                    <div v-else class="w-full h-16 sm:h-20 rounded-md bg-transparent border-2 border-dashed border-gray-200 opacity-50"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="groupedBlocks.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
            <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay unidades</h3>
            <p class="mt-1 text-sm text-gray-500">Genera la matriz topológica para verlas aquí.</p>
        </div>
    </div>
</template>
