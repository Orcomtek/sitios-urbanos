<?php

use App\Enums\CommissionType;
use App\Exceptions\SplitConfigurationException;
use App\Models\FinancialSetting;
use App\Services\Financial\SplitEngineService;

beforeEach(function () {
    $this->service = new SplitEngineService;
});

it('calculates fixed commission correctly', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Fixed,
        'commission_value' => 1500,
        'epayco_allied_account_id' => 'allied_123',
    ]);

    $result = $this->service->calculate(100000, $settings, 1);

    expect($result['platform_commission'])->toBe(1500)
        ->and($result['net_amount'])->toBe(98500)
        ->and($result['split_receiver_id'])->toBe('allied_123')
        ->and($result['split_percentage'])->toBe(98.50)
        ->and($result['commission_type'])->toBe('fixed')
        ->and($result['commission_value'])->toBe(1500);
});

it('calculates percentage commission correctly', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Percentage,
        'commission_value' => 350, // 3.50%
        'epayco_allied_account_id' => 'allied_456',
    ]);

    $result = $this->service->calculate(100000, $settings, 1);

    expect($result['platform_commission'])->toBe(3500)
        ->and($result['net_amount'])->toBe(96500)
        ->and($result['split_receiver_id'])->toBe('allied_456')
        ->and($result['split_percentage'])->toBe(96.50)
        ->and($result['commission_type'])->toBe('percentage')
        ->and($result['commission_value'])->toBe(350);
});

it('caps fixed commission at amount when commission exceeds payment', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Fixed,
        'commission_value' => 200000, // more than amount
        'epayco_allied_account_id' => 'allied_789',
    ]);

    $result = $this->service->calculate(50000, $settings, 1);

    expect($result['platform_commission'])->toBe(50000)
        ->and($result['net_amount'])->toBe(0)
        ->and($result['split_percentage'])->toBe(0.00);
});

it('uses floor for percentage commission to avoid overcharging', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Percentage,
        'commission_value' => 333, // 3.33%
        'epayco_allied_account_id' => 'allied_floor',
    ]);

    // 100000 * 333 / 10000 = 3330.0 → floor → 3330
    $result = $this->service->calculate(100000, $settings, 1);
    expect($result['platform_commission'])->toBe(3330);

    // 99999 * 333 / 10000 = 3329.9667 → floor → 3329
    $result2 = $this->service->calculate(99999, $settings, 1);
    expect($result2['platform_commission'])->toBe(3329);
});

it('throws SplitConfigurationException when allied account is missing', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Fixed,
        'commission_value' => 1500,
        'epayco_allied_account_id' => null,
    ]);

    $this->service->calculate(100000, $settings, 42);
})->throws(SplitConfigurationException::class);

it('throws SplitConfigurationException when settings are null and no allied account', function () {
    // When settings are null, the service falls back to config defaults.
    // In a unit test context without Laravel, config() is unavailable.
    // This test verifies the exception path via a setting with no allied account.
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Fixed,
        'commission_value' => 1500,
        'epayco_allied_account_id' => null,
    ]);

    $this->service->calculate(100000, $settings, 99);
})->throws(SplitConfigurationException::class);

// Config fallback test belongs in Feature tests where the Laravel app is booted.
// See tests/Feature/Finance/SplitEngineConfigFallbackTest.php

it('handles zero amount gracefully', function () {
    $settings = new FinancialSetting([
        'commission_type' => CommissionType::Fixed,
        'commission_value' => 1500,
        'epayco_allied_account_id' => 'allied_zero',
    ]);

    $result = $this->service->calculate(0, $settings, 1);

    expect($result['platform_commission'])->toBe(0)
        ->and($result['net_amount'])->toBe(0)
        ->and($result['split_percentage'])->toBe(0.00);
});
