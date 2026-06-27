<?php

use App\Exceptions\SplitConfigurationException;
use App\Models\FinancialSetting;
use App\Services\Financial\SplitEngineService;

it('falls back to config defaults when settings have null commission values', function () {
    config([
        'finance.commission.type' => 'fixed',
        'finance.commission.value' => 2000,
    ]);

    $settings = new FinancialSetting([
        'commission_type' => null,
        'commission_value' => null,
        'epayco_allied_account_id' => 'allied_fallback',
    ]);

    $service = app(SplitEngineService::class);
    $result = $service->calculate(100000, $settings, 1);

    expect($result['platform_commission'])->toBe(2000)
        ->and($result['net_amount'])->toBe(98000);
});

it('throws when settings are completely null and no allied account in config', function () {
    config([
        'finance.commission.type' => 'fixed',
        'finance.commission.value' => 1500,
    ]);

    $service = app(SplitEngineService::class);
    $service->calculate(100000, null, 99);
})->throws(SplitConfigurationException::class);
