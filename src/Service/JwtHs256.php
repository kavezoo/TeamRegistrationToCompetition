<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Rövid HS256 JWT. A titok a környezeti változó, különben a biztonsági só.
 */
class JwtHs256
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function encode(array $payload, string $secret): string
    {
        $header = self::json([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]);
        $body = self::json($payload);
        $signature = self::sign($header . '.' . $body, $secret);

        return $header . '.' . $body . '.' . $signature;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function decode(string $jwt, string $secret): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        $expected = self::sign($parts[0] . '.' . $parts[1], $secret);
        if (!hash_equals($expected, $parts[2])) {
            return null;
        }
        $payload = json_decode(self::decodePart($parts[1]), true);
        if (!is_array($payload)) {
            return null;
        }
        $exp = $payload['exp'] ?? null;
        if (!is_int($exp) && !is_numeric($exp)) {
            return null;
        }
        if ((int)$exp < time()) {
            return null;
        }

        return $payload;
    }

    private static function sign(string $data, string $secret): string
    {
        return self::base64(hash_hmac('sha256', $data, $secret, true));
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function json(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return '';
        }

        return self::base64($json);
    }

    private static function base64(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function decodePart(string $part): string
    {
        $remainder = strlen($part) % 4;
        if ($remainder > 0) {
            $part .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($part, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }
}
