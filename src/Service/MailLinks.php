<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Teljes cím a levélben. A domain a küldő kérésből jön, nem a programba írt címből.
 * A címzett szerepe dönti el, melyik felület adatlapja nyílik meg.
 */
class MailLinks
{
    /**
     * A levél linkjének eleje: a kérés sémája és hostja. Parancssorból a beállított cím marad.
     */
    public static function originFromRequest(ServerRequestInterface $request): ?string
    {
        $host = trim($request->getHeaderLine('Host'));
        if (!self::validHost($host)) {
            $host = trim($request->getUri()->getHost());
        }
        $host = strtolower($host);
        if (!self::validHost($host)) {
            return null;
        }

        $scheme = strtolower($request->getUri()->getScheme() ?: '');
        $forwarded = strtolower(trim(explode(',', $request->getHeaderLine('X-Forwarded-Proto'))[0] ?? ''));
        if ($scheme !== 'https' && $forwarded === 'https') {
            $scheme = 'https';
        }
        if ($scheme !== 'http' && $scheme !== 'https') {
            $scheme = 'https';
        }

        return $scheme . '://' . $host;
    }

    public static function competitionSheet(string $role, int $competitionId): string
    {
        return self::url([
            'prefix' => $role === User::ROLE_PRESIDENT ? 'President' : 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'index',
            '?' => ['competition' => $competitionId],
        ]);
    }

    public static function registrationSheet(string $role, int|string $registrationId): string
    {
        return self::url([
            'prefix' => $role === User::ROLE_PRESIDENT ? 'President' : 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'edit',
            $registrationId,
        ]);
    }

    /**
     * A jelentkezett tagok listája: tagnál a saját főoldal, klubelnöknél a nevezők.
     */
    public static function registrantList(string $role): string
    {
        if ($role === User::ROLE_PRESIDENT) {
            return self::url([
                'prefix' => 'President',
                'plugin' => null,
                'controller' => 'Groups',
                'action' => 'index',
            ]);
        }

        return self::url([
            'prefix' => 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'index',
        ]);
    }

    /**
     * Kijelentkezés. A művelet utána a belépő oldalra visz, ott lehet újra belépni.
     */
    public static function logout(): string
    {
        return self::url('/logout');
    }

    public static function applicants(): string
    {
        return self::url([
            'prefix' => 'President',
            'plugin' => null,
            'controller' => 'Applicants',
            'action' => 'index',
        ]);
    }

    public static function accountHome(): string
    {
        return self::url('/');
    }

    /**
     * @param array<string, mixed>|string $url
     */
    private static function url(array|string $url): string
    {
        $request = Router::getRequest();
        $origin = $request !== null ? self::originFromRequest($request) : null;
        if ($origin === null) {
            return Router::url($url, true);
        }

        $previous = Router::fullBaseUrl();
        Router::fullBaseUrl($origin);
        try {
            return Router::url($url, true);
        } finally {
            if ($previous !== '') {
                Router::fullBaseUrl($previous);
            }
        }
    }

    private static function validHost(string $host): bool
    {
        return preg_match(
            '/\A([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*(\:[0-9]{1,5})?\z/i',
            $host,
        ) === 1;
    }
}
