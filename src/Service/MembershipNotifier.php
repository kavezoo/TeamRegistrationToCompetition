<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;

/**
 * A klubelnökök e-mailben értesülnek az új felvételi kérelemről,
 * a jelentkező pedig az elfogadásról.
 * A levél csak kiegészítés: a döntés akkor is megmarad, ha a küldés nem sikerül.
 */
class MembershipNotifier
{
    public function notifyPresidents(object $applicant): void
    {
        $name = User::displayName($applicant);
        $email = trim((string)($applicant->email ?? ''));
        $details = "Új jelentkező érkezett: {$name}\n";
        if ($email !== '') {
            $details .= "E-mail: {$email}\n";
        }
        $details .= "\nA jelentkezők listáján tagként fogadhatod el, vagy jelezheted, hogy nem tag, csak kipróbálta az oldalt.";
        PresidentNotice::send('Új klubjelentkező', $details, [
            [MailLinks::applicants(), 'Az új jelentkezők megtekintése'],
        ]);
    }

    /**
     * A jelentkező megtudja, hogy tag lett. A szerepkör csak új belépés után érvényes.
     */
    public function notifyAccepted(object $applicant): void
    {
        $to = trim((string)($applicant->email ?? ''));
        if ($to === '') {
            return;
        }

        $details = "A klubelnök elfogadta a jelentkezésedet.\n\n";
        $details .= 'Jelentkezz ki a Kilépés gombbal, majd jelentkezz be újra. A következő bejelentkezéskor minden elérhető lesz.';
        [$text, $html] = OutgoingMail::notice($applicant, $details, [
            [MailLinks::memberHome(), 'A tagi felület megnyitása'],
        ]);

        try {
            OutgoingMail::send($to, 'A jelentkezésedet elfogadtuk', $text, $html);
        } catch (\Throwable $exception) {
            Log::error('Az elfogadás értesítése nem ment ki: ' . $exception->getMessage());
        }
    }

    /**
     * A kipróbáló megtudja, hogy nem tag. Belépve törölheti az adatait, vagy újra kérheti a tagságot.
     */
    public function notifyDenied(object $applicant): void
    {
        $to = trim((string)($applicant->email ?? ''));
        if ($to === '') {
            return;
        }

        $details = "A klubelnök jelezte, hogy nem vagy tag, csak kipróbáltad az oldalt.\n\n";
        $details .= 'Az adataidat törölheted. Ha mégis tag szeretnél lenni, a profil mentése újra a klubelnök elé viszi a kérelmet.';
        [$text, $html] = OutgoingMail::notice($applicant, $details, [
            [MailLinks::accountHome(), 'A saját oldalam megnyitása'],
        ]);

        try {
            OutgoingMail::send($to, 'Nem tag, csak kipróbáltad az oldalt', $text, $html);
        } catch (\Throwable $exception) {
            Log::error('Az elutasítás értesítése nem ment ki: ' . $exception->getMessage());
        }
    }
}
