<?php

use App\Rules\NotPrivateUrl;

describe('NotPrivateUrl Rule', function (): void {
    function validate(string $value): ?string
    {
        $error = null;
        (new NotPrivateUrl)->validate('forward_to_url', $value, function (string $msg) use (&$error): void {
            $error = $msg;
        });

        return $error;
    }

    test('passes for a public HTTPS IP address', function (): void {
        expect(validate('https://8.8.8.8/webhook'))->toBeNull();
    });

    test('fails when scheme is http', function (): void {
        expect(validate('http://example.com/webhook'))->toContain('must use HTTPS');
    });

    test('fails for a private IPv4 address (10.x.x.x)', function (): void {
        expect(validate('https://10.0.0.1/webhook'))->toContain('private or internal');
    });

    test('fails for a loopback IPv4 address (127.x.x.x)', function (): void {
        expect(validate('https://127.0.0.1/webhook'))->toContain('private or internal');
    });

    test('fails for a link-local address (169.254.x.x)', function (): void {
        expect(validate('https://169.254.169.254/metadata'))->toContain('private or internal');
    });

    test('fails for a private IPv4 address (192.168.x.x)', function (): void {
        expect(validate('https://192.168.1.1/webhook'))->toContain('private or internal');
    });

    test('fails for the localhost hostname', function (): void {
        expect(validate('https://localhost/admin'))->toContain('internal or private hosts');
    });

    test('fails for the GCP metadata hostname', function (): void {
        expect(validate('https://metadata.google.internal/computeMetadata/v1/'))->toContain('internal or private hosts');
    });

    test('ignores empty string without failing', function (): void {
        expect(validate(''))->toBeNull();
    });

    test('fails for a URL with no host component', function (): void {
        expect(validate('https:///no-host'))->toContain('valid URL');
    });

    test('passes for a valid public domain that resolves to a public IP', function (): void {
        // Covers the gethostbyname() code path (DNS resolution)
        expect(validate('https://example.com/webhook'))->toBeNull();
    });

    test('fails for a hostname that cannot be resolved', function (): void {
        // .invalid TLD is reserved and never resolves — triggers the DNS-failure branch
        expect(validate('https://nonexistent-host-xyz12345.invalid/webhook'))->toContain('could not be resolved');
    });
});
