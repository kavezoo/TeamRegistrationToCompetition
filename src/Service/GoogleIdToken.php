<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\Http\Client;
use function Cake\Core\env;

/**
 * A Google ID tokent a Google nyilvános kulcsával ellenőrzi.
 * Az audience a webes kliens, és ha kell, a mobil kliensek.
 */
class GoogleIdToken
{
    /**
     * @return array<string, mixed>
     */
    public function verify(string $idToken): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new \RuntimeException('A Google token formátuma hibás.');
        }
        $header = json_decode($this->decode($parts[0]), true);
        $payload = json_decode($this->decode($parts[1]), true);
        if (!is_array($header) || !is_array($payload)) {
            throw new \RuntimeException('A Google token nem olvasható.');
        }
        $kid = (string)($header['kid'] ?? '');
        if ($kid === '' || ($header['alg'] ?? '') !== 'RS256') {
            throw new \RuntimeException('A Google token aláírása nem támogatott.');
        }
        $pem = $this->certificate($kid);
        $signature = $this->decode($parts[2]);
        $verified = openssl_verify($parts[0] . '.' . $parts[1], $signature, $pem, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            throw new \RuntimeException('A Google token aláírása érvénytelen.');
        }
        $this->assertClaims($payload);

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertClaims(array $payload): void
    {
        $iss = (string)($payload['iss'] ?? '');
        if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new \RuntimeException('A Google token kibocsátója érvénytelen.');
        }
        $exp = (int)($payload['exp'] ?? 0);
        if ($exp < time()) {
            throw new \RuntimeException('A Google token lejárt.');
        }
        $aud = $payload['aud'] ?? '';
        $audiences = is_array($aud) ? $aud : [(string)$aud];
        $allowed = $this->clientIds();
        $match = false;
        foreach ($audiences as $audience) {
            if (in_array((string)$audience, $allowed, true)) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            throw new \RuntimeException('A Google token nem ehhez az alkalmazáshoz szól.');
        }
        $verified = $payload['email_verified'] ?? false;
        if ($verified !== true && $verified !== 'true') {
            throw new \RuntimeException('A Google e-mail nincs megerősítve.');
        }
        if (trim((string)($payload['sub'] ?? '')) === '' || trim((string)($payload['email'] ?? '')) === '') {
            throw new \RuntimeException('A Google tokenből hiányzik a fiók vagy az e-mail.');
        }
    }

    /**
     * @return list<string>
     */
    private function clientIds(): array
    {
        $ids = [];
        $web = trim((string)env('GOOGLE_CLIENT_ID'));
        if ($web !== '') {
            $ids[] = $web;
        }
        $extra = trim((string)env('GOOGLE_MOBILE_CLIENT_IDS'));
        if ($extra !== '') {
            foreach (explode(',', $extra) as $id) {
                $id = trim($id);
                if ($id !== '') {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }

    private function certificate(string $kid): string
    {
        $certs = Cache::remember('google_jwks', function (): array {
            $response = (new Client())->get('https://www.googleapis.com/oauth2/v3/certs');
            $body = $response->getJson();
            if (!is_array($body) || !isset($body['keys']) || !is_array($body['keys'])) {
                throw new \RuntimeException('A Google kulcsok nem tölthetők le.');
            }

            return $body['keys'];
        }, 'default');
        if (!is_array($certs)) {
            throw new \RuntimeException('A Google kulcsok nem tölthetők le.');
        }
        foreach ($certs as $jwk) {
            if (is_array($jwk) && (string)($jwk['kid'] ?? '') === $kid) {
                return $this->pem($jwk);
            }
        }
        Cache::delete('google_jwks');
        $fresh = Cache::remember('google_jwks', function (): array {
            $response = (new Client())->get('https://www.googleapis.com/oauth2/v3/certs');
            $body = $response->getJson();
            if (!is_array($body) || !isset($body['keys']) || !is_array($body['keys'])) {
                throw new \RuntimeException('A Google kulcsok nem tölthetők le.');
            }

            return $body['keys'];
        }, 'default');
        if (is_array($fresh)) {
            foreach ($fresh as $jwk) {
                if (is_array($jwk) && (string)($jwk['kid'] ?? '') === $kid) {
                    return $this->pem($jwk);
                }
            }
        }
        throw new \RuntimeException('A Google token kulcsa nem található.');
    }

    /**
     * @param array<string, mixed> $jwk
     */
    private function pem(array $jwk): string
    {
        $modulus = $this->decode((string)($jwk['n'] ?? ''));
        $exponent = $this->decode((string)($jwk['e'] ?? ''));
        $rsa = $this->asn(0x30, $this->integer($modulus) . $this->integer($exponent));
        $algorithm = hex2bin('300d06092a864886f70d0101010500');
        if ($algorithm === false) {
            throw new \RuntimeException('A Google kulcs nem állítható elő.');
        }
        $spki = $this->asn(0x30, $algorithm . $this->asn(0x03, "\x00" . $rsa));
        $body = chunk_split(base64_encode($spki), 64, "\n");

        return "-----BEGIN PUBLIC KEY-----\n" . $body . "-----END PUBLIC KEY-----\n";
    }

    private function integer(string $bytes): string
    {
        if ($bytes !== '' && (ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return $this->asn(0x02, $bytes);
    }

    private function asn(int $tag, string $value): string
    {
        $length = strlen($value);
        if ($length < 128) {
            return chr($tag) . chr($length) . $value;
        }
        $long = '';
        $rest = $length;
        while ($rest > 0) {
            $long = chr($rest & 0xff) . $long;
            $rest >>= 8;
        }

        return chr($tag) . chr(0x80 | strlen($long)) . $long . $value;
    }

    private function decode(string $part): string
    {
        $remainder = strlen($part) % 4;
        if ($remainder > 0) {
            $part .= str_repeat('=', 4 - $remainder);
        }
        $decoded = base64_decode(strtr($part, '-_', '+/'), true);
        if ($decoded === false) {
            throw new \RuntimeException('A Google token nem olvasható.');
        }

        return $decoded;
    }
}
