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
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'index',
            '?' => ['competition' => $competitionId],
        ], true);
    }

    public static function registrationSheet(string $role, int|string $registrationId): string
    {
        return Router::url([
            'prefix' => $role === User::ROLE_PRESIDENT ? 'President' : 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'edit',
            $registrationId,
        ], true);
    }

    /**
     * A jelentkezett tagok listája: tagnál a saját főoldal, klubelnöknél a nevezők.
     */
    public static function registrantList(string $role): string
    {
        if ($role === User::ROLE_PRESIDENT) {
            return Router::url([
                'prefix' => 'President',
                'plugin' => null,
                'controller' => 'Groups',
                'action' => 'index',
            ], true);
        }

        return Router::url([
            'prefix' => 'Member',
            'plugin' => null,
            'controller' => 'Registrations',
            'action' => 'index',
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

    public static function accountHome(): string
    {
        return Router::url('/', true);
    }
}
