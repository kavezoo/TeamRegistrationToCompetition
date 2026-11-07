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
        if (!filter_var($applicant->profile_saved ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }
        $name = User::displayName($applicant);
        $email = trim((string)($applicant->email ?? ''));
        $details = "Új jelentkező érkezett: {$name}\n";
        if ($email !== '') {
            $details .= "E-mail: {$email}\n";
        }
        $details .= "\nA profilját elmentette. A jelentkezők listáján tagként fogadhatod el, vagy jelezheted, hogy nem tag, csak kipróbálta az oldalt.";
        PresidentNotice::send('Új klubjelentkező', $details, [
            [MailLinks::applicants(), 'Az új jelentkezők megtekintése'],
        ]);
    }

    /**
     * A jelentkező törölte a saját adatait. A lista ettől fogva nem tartalmazza.
     */
    public function notifyDeleted(string $name, string $email): void
    {
        $name = trim($name);
        if ($name === '') {
            $name = $email !== '' ? $email : 'Egy jelentkező';
        }
        $details = "{$name} törölte az adatait.\n";
        if ($email !== '' && $email !== $name) {
            $details .= "E-mail: {$email}\n";
        }
        $details .= "\nVéletlenül járt az oldalon, és a saját adatait törölte. A jelentkezők listáján már nem szerepel.";
        PresidentNotice::send('Törölt klubjelentkezés', $details, [
            [MailLinks::applicants(), 'A jelentkezők listája'],
        ]);
    }

    /**
     * A jelentkező megtudja, hogy tag lett, és mit tegyen: kijelentkezés, majd új belépés.
     */
    public function notifyAccepted(object $applicant): void
    {
        $to = trim((string)($applicant->email ?? ''));
        if ($to === '') {
            return;
        }

        $details = "A klubelnök elfogadta a jelentkezésedet.\n\n";
        $details .= "Ezt tedd meg, ebben a sorrendben:\n";
        $details .= "1. Kattints a lenti gombra. A gomb kijelentkeztet.\n";
        $details .= "2. Utána a belépő oldal nyílik meg. Jelentkezz be újra ugyanazzal a Google-fiókkal.\n";
        $details .= '3. A következő bejelentkezéskor minden elérhető lesz az új tagoknak, és a tagi felület megnyílik.';
        [$text, $html] = OutgoingMail::notice($applicant, $details, [
            [MailLinks::logout(), 'Kijelentkezés, utána belépés'],
        ], '');

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

        $details = User::DENIED_NOTICE;
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
