<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
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
        if ($user !== null) {
            $role = (string)($user->get('role') ?? '');
            $name = trim((string)($registration->competition->name ?? ''));
            $details = "Jelentkeztél a versenyre.\n";
            if ($name !== '') {
                $details .= "\nVerseny: {$name}";
            }
            $subject = $name !== '' ? 'Jelentkeztél a versenyre: ' . $name : 'Jelentkeztél a versenyre';
            FlashNotice::deliver($user, $subject, $details, 'registration', $registration->id, User::FLASH_SIGNUP);
            $to = trim((string)($user->email ?? ''));
            if ($to !== '' && User::wantsMail($user, User::MAIL_SIGNUP)) {
                [$text, $html] = OutgoingMail::notice($user, $details, [
                    [MailLinks::registrationSheet($role, $registration->id), 'A jelentkezésem megtekintése'],
                    [MailLinks::registrantList($role), 'A jelentkezett tagok listája'],
                ]);

                try {
                    OutgoingMail::send($to, $subject, $text, $html, $user);
                } catch (\Throwable $exception) {
                    Log::error('A jelentkezési értesítés nem ment ki: ' . $exception->getMessage());
                }
            }
        }

        $this->notifyPresidentsSignedUp($registration);
    }

    /**
     * A lemondás után a klubelnökök látják, hogy valaki már nincs a nevezettek között.
     */
    public function notifyPresidentsWithdrawn(object $registration): void
    {
        $user = $registration->user ?? null;
        $name = User::displayName($user);
        $competition = trim((string)($registration->competition->name ?? ''));
        $details = "Valaki lemondta a versenyre jelentkezését.\n";
        if ($name !== '') {
            $details .= "\nNév: {$name}";
        }
        if ($competition !== '') {
            $details .= "\nVerseny: {$competition}";
        }
        $subject = $competition !== '' ? 'Lemondott versenyjelentkezés: ' . $competition : 'Lemondott versenyjelentkezés';
        PresidentNotice::send($subject, $details, [
            [MailLinks::registrantList(User::ROLE_PRESIDENT), 'A jelentkezett tagok listája'],
        ], $user !== null ? (string)($user->id ?? $registration->user_id ?? '') : (string)($registration->user_id ?? ''), User::MAIL_PRESIDENT_ENTRIES, 'roster');
    }

    /**
     * Határidő után a nevezés megmarad, de a klubelnök látja, hogy a tag nem tud jönni.
     */
    public function notifyPresidentsAbsent(object $registration): void
    {
        $user = $registration->user ?? null;
        $name = User::displayName($user);
        $competition = trim((string)($registration->competition->name ?? ''));
        $reason = trim((string)($registration->absent_reason ?? ''));
        $details = "Valaki jelezte, hogy nem tud eljönni a versenyre.\n";
        if ($name !== '') {
            $details .= "\nNév: {$name}";
        }
        if ($competition !== '') {
            $details .= "\nVerseny: {$competition}";
        }
        if ($reason !== '') {
            $details .= "\nIndok: {$reason}";
        }
        $subject = $competition !== '' ? 'Nem tud eljönni: ' . $competition : 'Nem tud eljönni a versenyre';
        PresidentNotice::send($subject, $details, [
            [MailLinks::registrantList(User::ROLE_PRESIDENT), 'A jelentkezett tagok listája'],
        ], $user !== null ? (string)($user->id ?? $registration->user_id ?? '') : (string)($registration->user_id ?? ''), User::MAIL_PRESIDENT_ENTRIES, 'roster');
    }

    private function notifyPresidentsSignedUp(object $registration): void
    {
        $user = $registration->user ?? null;
        $name = User::displayName($user);
        $email = trim((string)($user->email ?? ''));
        $competition = trim((string)($registration->competition->name ?? ''));
        $details = "Valaki jelentkezett a versenyre.\n";
        if ($name !== '') {
            $details .= "\nNév: {$name}";
        }
        if ($email !== '') {
            $details .= "\nE-mail: {$email}";
        }
        if ($competition !== '') {
            $details .= "\nVerseny: {$competition}";
        }
        $subject = $competition !== '' ? 'Új versenyjelentkezés: ' . $competition : 'Új versenyjelentkezés';
        PresidentNotice::send($subject, $details, [
            [MailLinks::registrantList(User::ROLE_PRESIDENT), 'A jelentkezett tagok listája'],
        ], $user !== null ? (string)($user->id ?? '') : null, User::MAIL_PRESIDENT_ENTRIES, 'roster');
    }
}
