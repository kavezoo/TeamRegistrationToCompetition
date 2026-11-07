<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Aki jelentkezett a versenyre, levelet kap a saját adatlapról és a jelentkezettek listájáról.
 * A jelentkezés akkor is megmarad, ha a levél nem megy ki.
 */
class RegistrationSignupNotifier
{
    public function notify(int|string $registrationId): void
    {
        try {
            $registration = TableRegistry::getTableLocator()->get('Registrations')->get($registrationId, contain: [
                'Users',
                'Competitions',
            ]);
        } catch (\Throwable $exception) {
            Log::error('A jelentkezési értesítéshez a nevezés nem tölthető be: ' . $exception->getMessage());

            return;
        }

        $user = $registration->user ?? null;
        $to = trim((string)($user->email ?? ''));
        if ($user === null || $to === '') {
            return;
        }

        $role = (string)($user->get('role') ?? '');
        $name = trim((string)($registration->competition->name ?? ''));
        $details = "Jelentkeztél a versenyre.\n";
        if ($name !== '') {
            $details .= "\nVerseny: {$name}";
        }
        $subject = $name !== '' ? 'Jelentkeztél a versenyre: ' . $name : 'Jelentkeztél a versenyre';
        [$text, $html] = OutgoingMail::notice($user, $details, [
            [MailLinks::registrationSheet($role, $registration->id), 'A jelentkezésem megtekintése'],
            [MailLinks::registrantList($role), 'A jelentkezett tagok listája'],
        ]);

        try {
            OutgoingMail::send($to, $subject, $text, $html);
        } catch (\Throwable $exception) {
            Log::error('A jelentkezési értesítés nem ment ki: ' . $exception->getMessage());
        }
    }
}
