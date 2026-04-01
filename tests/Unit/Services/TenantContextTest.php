<?php

use App\Exceptions\TenantContextMissingException;
use App\Models\Community;
use App\Services\TenantContext;
use Tests\TestCase;

uses(TestCase::class);

it('has an empty initial state', function () {
    $context = new TenantContext;

    expect($context->get())->toBeNull();
});

it('can set and get the community', function () {
    $community = new Community(['id' => 1, 'name' => 'Test Community']);
    $context = new TenantContext;

    $context->set($community);

    expect($context->get())->toBe($community);
});

it('can require the community when set', function () {
    $community = new Community(['id' => 1, 'name' => 'Test Community']);
    $context = new TenantContext;

    $context->set($community);

    expect($context->require())->toBe($community);
});

it('throws an exception when requiring community without context', function () {
    $context = new TenantContext;

    $context->require();
})->throws(TenantContextMissingException::class, 'Tenant context is required but not set.');

it('is registered as a singleton in the container', function () {
    $context1 = app(TenantContext::class);
    $context2 = app(TenantContext::class);

    expect($context1)->toBe($context2);
});
