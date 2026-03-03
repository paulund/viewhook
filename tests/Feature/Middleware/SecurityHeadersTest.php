<?php

declare(strict_types=1);

describe('SecurityHeaders middleware', function (): void {
    test('HSTS header is set in production environment', function (): void {
        $this->app['env'] = 'production';

        $response = $this->get('/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    });

    test('security headers are present on every response', function (): void {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    });
});
