<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use DateTimeInterface;

/**
 * Új verseny kiírásakor minden aktív tag e-mailt kap.
 * A verseny akkor is megmarad, ha a küldés nem sikerül.
 */
class CompetitionNotifier
{
    public function notifyMembers(Competition $competition): void
    {
        $name = trim((string)$competition->name);
        $url = MailLinks::competitionSheet(User::ROLE_MEMBER, (int)$competition->id);
        $body = "Van új verseny kiírva, lehet jelentkezni.\n\n";
        if ($name !== '') {
            $body .= "Verseny: {$name}\n";
        }
        $when = $this->formatWhen($competition->date_competition);
        if ($when !== '') {
            $body .= "Verseny ideje: {$when}\n";
        }
        $from = $this->formatDay($competition->date_from);
        if ($from !== '') {
            $body .= "Jelentkezés kezdete: {$from}\n";
        }
        $deadline = $this->formatDay($competition->date_deadline);
        if ($deadline !== '') {
            $body .= "Jelentkezési határidő: {$deadline}\n";
        }
        $body .= "\n" . MailLinks::loginLine($url);

        try {
            $members = TableRegistry::getTableLocator()->get('Users')->find()
                ->select(['id', 'email'])
                ->where([
                    'role' => User::ROLE_MEMBER,
                    'active' => true,
                    'enabled' => true,
                ])
                ->all();
        } catch (\Throwable $exception) {
            Log::error('Az új verseny értesítéséhez a tagok nem tölthetők be: ' . $exception->getMessage());

            return;
        }

        foreach ($members as $member) {
            $to = trim((string)($member->email ?? ''));
            if ($to === '') {
                continue;
            }
            try {
                $mailer = new Mailer('default');
                $mailer
                    ->setTo($to)
                    ->setSubject('Új verseny kiírva')
                    ->deliver($body);
            } catch (\Throwable $exception) {
                Log::error('Az új verseny értesítése nem ment ki: ' . $exception->getMessage());
            }
        }
    }

    private function formatWhen(mixed $value): string
    {
        if (!$value instanceof DateTimeInterface) {
            return '';
        }

        return $value->format('Y.m.d. H:i');
    }

    private function formatDay(mixed $value): string
    {
        if (!$value instanceof DateTimeInterface) {
            return '';
        }

        return $value->format('Y.m.d.');
    }
}
