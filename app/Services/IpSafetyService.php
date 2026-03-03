<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\SsrfProtectionException;

final readonly class IpSafetyService
{
    private const array BLOCKED_HOSTNAMES = [
        'localhost',
        'metadata.google.internal',
        'metadata.internal',
    ];

    /**
     * @param  (\Closure(string): list<string>)|null  $dnsResolver  Custom DNS resolver for testing
     */
    public function __construct(
        private ?\Closure $dnsResolver = null,
    ) {}

    /**
     * Assert that the given URL is safe to request (not an SSRF target).
     *
     * @throws SsrfProtectionException
     */
    public function assertSafe(string $url): void
    {
        $error = $this->check($url);

        if ($error !== null) {
            throw new SsrfProtectionException($error);
        }
    }

    /**
     * Check if the URL is safe. Returns null if safe, or an error message if not.
     */
    public function check(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $parsed = parse_url($url);

        if ($parsed === false || ! isset($parsed['host'])) {
            return 'The URL must be a valid URL.';
        }

        $host = strtolower($parsed['host']);

        // Strip brackets from IPv6 literals (e.g. [::1] → ::1)
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $host = substr($host, 1, -1);
        }

        // If the host is already an IP address, validate it directly
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->checkIp($host);
        }

        if (in_array($host, self::BLOCKED_HOSTNAMES, true)) {
            return 'The URL cannot target internal or private hosts.';
        }

        // Resolve all A (IPv4) and AAAA (IPv6) records
        $records = $this->dnsResolver instanceof \Closure
            ? ($this->dnsResolver)($host)
            : $this->resolveAllIps($host);

        if ($records === []) {
            return 'The URL host could not be resolved.';
        }

        foreach ($records as $ip) {
            $error = $this->checkIp($ip);
            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    /**
     * Check if a single IP is safe (not private or reserved).
     */
    private function checkIp(string $ip): ?string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return 'The URL cannot target private or internal IP addresses.';
        }

        return null;
    }

    /**
     * Resolve all A and AAAA records for a hostname.
     *
     * @return list<string>
     */
    private function resolveAllIps(string $host): array
    {
        $ips = [];

        $aRecords = @dns_get_record($host, DNS_A);
        if (is_array($aRecords)) {
            foreach ($aRecords as $record) {
                if (isset($record['ip']) && is_string($record['ip'])) {
                    $ips[] = $record['ip'];
                }
            }
        }

        $aaaaRecords = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaaRecords)) {
            foreach ($aaaaRecords as $record) {
                if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return $ips;
    }
}
