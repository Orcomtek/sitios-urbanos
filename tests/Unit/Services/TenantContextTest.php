<?php

use App\Exceptions\TenantContextMissingException;
use App\Models\Community;
use App\Services\TenantContext;

it('sets and gets a community', function () {
    $context = new TenantContext;
    $community = new Community;

    $context->set($community);

    expect($context->get())->toBe($community);
});

it('returns null when getting an unset community', function () {
    $context = new TenantContext;

    expect($context->get())->toBeNull();
});

it('requires a community and returns it when set', function () {
    $context = new TenantContext;
    $community = new Community;

    $context->set($community);

    expect($context->require())->toBe($community);
});

it('throws TenantContextMissingException when requiring an unset community', function () {
    $context = new TenantContext;

    expect(fn () => $context->require())
        ->toThrow(
            TenantContextMissingException::class,
            'Tenant context is required but not set.'
        );
});
