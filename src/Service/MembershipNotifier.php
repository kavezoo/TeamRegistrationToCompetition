<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

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
        $url = Router::url([
            'prefix' => 'President',
            'controller' => 'Applicants',
            'action' => 'index',
        ], true);
        $body = "Új klubjelentkező: {$name}\n";
        if ($email !== '') {
            $body .= "E-mail: {$email}\n";
        }
        $body .= "\nA kérelmet itt lehet elfogadni vagy elutasítani:\n{$url}\n";

        $presidents = TableRegistry::getTableLocator()->get('Users')->find()
            ->select(['id', 'email'])
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
                $mailer = new Mailer('default');
                $mailer
                    ->setTo($to)
                    ->setSubject('Új klubjelentkező')
                    ->deliver($body);
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

        $body = "A klubelnök elfogadta a jelentkezésedet. Most már beléphetsz.\n\n";
        $body .= "Ha jelenleg be vagy jelentkezve, jelentkezz ki a Kilépés gombbal, majd lépj be újra.\n";

        try {
            $mailer = new Mailer('default');
            $mailer
                ->setTo($to)
                ->setSubject('A jelentkezésedet elfogadtuk')
                ->deliver($body);
        } catch (\Throwable $exception) {
            Log::error('Az elfogadás értesítése nem ment ki: ' . $exception->getMessage());
        }
    }
}
