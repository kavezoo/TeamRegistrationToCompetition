<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Routing\Router;

/**
 * Teljes cím a levélben. A címzett szerepe dönti el, melyik felület adatlapja nyílik meg.
 */
class MailLinks
{
    public static function competitionSheet(string $role, int $competitionId): string
    {
        return Router::url([
            'prefix' => $role === User::ROLE_PRESIDENT ? 'President' : 'Member',
            'controller' => 'Registrations',
            'action' => 'index',
            '?' => ['competition' => $competitionId],
        ], true);
    }

    public static function registrationSheet(string $role, int|string $registrationId): string
    {
        return Router::url([
            'prefix' => $role === User::ROLE_PRESIDENT ? 'President' : 'Member',
            'controller' => 'Registrations',
            'action' => 'edit',
            $registrationId,
        ], true);
    }

    public static function memberHome(): string
    {
        return Router::url([
            'prefix' => 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'index',
        ], true);
    }

    public static function applicants(): string
    {
        return Router::url([
            'prefix' => 'President',
            'plugin' => null,
            'controller' => 'Applicants',
            'action' => 'index',
        ], true);
    }

    public static function loginLine(string $url): string
    {
        return "Ha nincs belépve, a link először a belépésre visz, utána ez az adatlap nyílik meg:\n{$url}\n";
    }
}
