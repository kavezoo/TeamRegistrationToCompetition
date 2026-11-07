<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;

/**
 * A jelentkező e-mailt kap, ha a klubelnök alcsoportba sorolja vagy másikba teszi.
 * A besorolás akkor is megmarad, ha a levél nem megy ki.
 */
class AssignmentNotifier
{
    public function notify(int|string $registrationId): void
    {
        try {
            $registration = TableRegistry::getTableLocator()->get('Registrations')->get($registrationId, contain: [
                'Teams',
                'Competitions',
                'Users',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Az alcsoport-értesítéshez a jelentkezés nem tölthető be: ' . $exception->getMessage());

            return;
        }

        $team = trim((string)($registration->team->name ?? ''));
        $to = trim((string)($registration->user->email ?? ''));
        if ($team === '' || $to === '') {
            return;
        }

        $competition = trim((string)($registration->competition->name ?? ''));
        $body = "A klubelnök besorolt egy alcsoportba.\n\n";
        if ($competition !== '') {
            $body .= "Verseny: {$competition}\n";
        }
        $body .= "Alcsoport: {$team}\n\n";
        $body .= MailLinks::loginLine(MailLinks::registrationSheet(
            (string)($registration->user->get('role') ?? ''),
            $registration->id,
        ));

        try {
            $mailer = new Mailer('default');
            $mailer
                ->setTo($to)
                ->setSubject('Besoroltak egy alcsoportba')
                ->deliver($body);
        } catch (\Throwable $exception) {
            Log::error('Az alcsoport-értesítés nem ment ki: ' . $exception->getMessage());
        }
    }
}
