<?php

declare(strict_types=1);

use App\Exceptions\SsrfProtectionException;
use App\Services\IpSafetyService;

describe('IpSafetyService', function (): void {
    test('allows public URLs', function (): void {
        $service = new IpSafetyService;

        expect($service->check('https://example.com'))->toBeNull();
    });

    test('allows empty URL', function (): void {
        $service = new IpSafetyService;

        expect($service->check(''))->toBeNull();
    });

    test('rejects invalid URLs', function (): void {
        $service = new IpSafetyService;

        expect($service->check('not-a-url'))->toBe('The URL must be a valid URL.');
    });

    test('rejects private IPv4 addresses', function (string $url): void {
        $service = new IpSafetyService;

        expect($service->check($url))->toBe('The URL cannot target private or internal IP addresses.');
    })->with([
        'loopback' => 'http://127.0.0.1/path',
        'class A private' => 'http://10.0.0.1/path',
        'class B private' => 'http://172.16.0.1/path',
        'class C private' => 'http://192.168.1.1/path',
    ]);

    test('rejects IPv6 loopback', function (): void {
        $service = new IpSafetyService;

        expect($service->check('http://[::1]/path'))->toBe('The URL cannot target private or internal IP addresses.');
    });

    test('rejects blocked hostnames', function (string $url): void {
        $service = new IpSafetyService;

        expect($service->check($url))->toBe('The URL cannot target internal or private hosts.');
    })->with([
        'localhost' => 'http://localhost/path',
        'metadata.google.internal' => 'http://metadata.google.internal/path',
        'metadata.internal' => 'http://metadata.internal/path',
    ]);

    test('rejects 0.0.0.0', function (): void {
        $service = new IpSafetyService;

        expect($service->check('http://0.0.0.0/path'))->toBe('The URL cannot target private or internal IP addresses.');
    });

    test('assertSafe throws SsrfProtectionException for private IPs', function (): void {
        $service = new IpSafetyService;

        $service->assertSafe('http://127.0.0.1/path');
    })->throws(SsrfProtectionException::class);

    test('assertSafe does not throw for public URLs', function (): void {
        $service = new IpSafetyService;

        $service->assertSafe('https://example.com');

        expect(true)->toBeTrue();
    });

    test('assertSafe does not throw for empty URL', function (): void {
        $service = new IpSafetyService;

        $service->assertSafe('');

        expect(true)->toBeTrue();
    });

    test('rejects URL with unresolvable hostname', function (): void {
        $service = new IpSafetyService(dnsResolver: fn (string $host): array => []);

        expect($service->check('https://unresolvable.example.com/path'))
            ->toBe('The URL host could not be resolved.');
    });

    test('rejects hostname that resolves to a private IP', function (): void {
        $service = new IpSafetyService(dnsResolver: fn (string $host): array => ['10.0.0.1']);

        expect($service->check('https://evil.example.com/path'))
            ->toBe('The URL cannot target private or internal IP addresses.');
    });

    test('rejects hostname where any resolved IP is private', function (): void {
        $service = new IpSafetyService(dnsResolver: fn (string $host): array => ['8.8.8.8', '192.168.1.1']);

        expect($service->check('https://mixed.example.com/path'))
            ->toBe('The URL cannot target private or internal IP addresses.');
    });

    test('allows hostname that resolves to all public IPs', function (): void {
        $service = new IpSafetyService(dnsResolver: fn (string $host): array => ['8.8.8.8', '8.8.4.4']);

        expect($service->check('https://safe.example.com/path'))->toBeNull();
    });
});
