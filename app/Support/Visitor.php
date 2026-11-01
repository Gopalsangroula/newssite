<?php

namespace App\Support;

use Illuminate\Http\Request;

class Visitor
{
    private const IPV6_BYTES = 16;
    private const IPV6_NETWORK_BYTES = 8;

    /**
     * What a rate limit counts by. A single home gets a whole block of IPv6 addresses,
     * so those count by the block, or moving to the next address would reset the limit.
     */
    public static function limitKey(Request $request): string
    {
        $ip = (string) $request->ip();
        $packed = @inet_pton($ip);

        return $packed !== false && strlen($packed) === self::IPV6_BYTES
            ? bin2hex(substr($packed, 0, self::IPV6_NETWORK_BYTES))
            : $ip;
    }
}
