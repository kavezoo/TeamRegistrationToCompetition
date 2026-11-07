<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use Cake\Log\Log;
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
        $details = "Van új verseny kiírva, lehet jelentkezni.\n\n";
        if ($name !== '') {
            $details .= "Verseny: {$name}\n";
        }
        $when = $this->formatWhen($competition->date_competition);
        if ($when !== '') {
            $details .= "Verseny ideje: {$when}\n";
        }
        $from = $this->formatDay($competition->date_from);
        if ($from !== '') {
            $details .= "Jelentkezés kezdete: {$from}\n";
        }
        $deadline = $this->formatDay($competition->date_deadline);
        if ($deadline !== '') {
            $details .= "Jelentkezési határidő: {$deadline}\n";
        }

        try {
            $users = TableRegistry::getTableLocator()->get('Users');
            $select = ['id', 'email', 'first_name', 'last_name', 'username', 'role'];
            if ($users->getSchema()->hasColumn(User::MAIL_COMPETITION)) {
                $select[] = User::MAIL_COMPETITION;
            }
            $members = $users->find()
                ->select($select)
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
            if ($to === '' || !User::wantsMail($member, User::MAIL_COMPETITION)) {
                continue;
            }
            try {
                [$text, $html] = OutgoingMail::competitionRegistration(
                    $member,
                    $details,
                    $url,
                    $competition->descriptionHtml(),
                );
                OutgoingMail::send($to, 'Új verseny kiírva', $text, $html, $member);
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
