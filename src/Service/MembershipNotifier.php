<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

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
        $body = "Új jelentkező érkezett: {$name}\n";
        if ($email !== '') {
            $body .= "E-mail: {$email}\n";
        }
        $body .= "\nA jelentkezők listáján tagként fogadhatod el, vagy jelezheted, hogy nem tag, csak kipróbálta az oldalt.\n";
        $body .= MailLinks::loginLine(MailLinks::applicants());

        $presidents = TableRegistry::getTableLocator()->get('Users')->find()
            ->select(['id', 'email', 'first_name', 'last_name', 'username'])
            ->where([
                'role' => User::ROLE_PRESIDENT,
                'active' => true,
            ])
            ->all();

        foreach ($presidents as $president) {
            $to = trim((string)($president->email ?? ''));
            if ($to === '') {
                continue;
            }
            try {
                OutgoingMail::send($to, 'Új klubjelentkező', OutgoingMail::greeting($president) . "\n\n" . $body);
            } catch (\Throwable $exception) {
                Log::error('A klubelnök értesítése nem ment ki: ' . $exception->getMessage());
            }
        }
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

        $body = OutgoingMail::greeting($applicant) . "\n\n";
        $body .= "A klubelnök elfogadta a jelentkezésedet.\n\n";
        $body .= "Jelentkezz ki a Kilépés gombbal, majd jelentkezz be újra. A következő bejelentkezéskor minden elérhető lesz.\n\n";
        $body .= MailLinks::loginLine(MailLinks::memberHome());

        try {
            OutgoingMail::send($to, 'A jelentkezésedet elfogadtuk', $body);
        } catch (\Throwable $exception) {
            Log::error('Az elfogadás értesítése nem ment ki: ' . $exception->getMessage());
        }
    }
}
