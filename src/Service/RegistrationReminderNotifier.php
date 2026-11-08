<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Competition;
use App\Model\Entity\User;
use App\Model\Entity\RegistrationReminder;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use DateTimeInterface;

/**
 * Emlékeztető annak a tagnak, aki még nem jelentkezett a versenyre.
 * A levél szövege a naplóba is bekerül.
 */
class RegistrationReminderNotifier
{
    public function remind(object $member, Competition $competition, string $senderId): ?RegistrationReminder
    {
        $to = trim((string)($member->email ?? ''));
        if ($to === '' || !User::wantsMail($member, User::MAIL_REMINDER)) {
            return null;
        }
        [$subject, $message, $html] = $this->compose($competition, $member);
        try {
            OutgoingMail::send($to, $subject, $message, $html, $member);
        } catch (\Throwable $exception) {
            Log::error('A nevezési emlékeztető nem ment ki: ' . $exception->getMessage());

            return null;
        }
        FlashNotice::deliver(
            $member,
            $subject,
            'Még nem jelentkeztél a versenyre: ' . trim((string)$competition->name),
            'competition',
            $competition->id,
            User::FLASH_REMINDER,
        );

        $reminders = TableRegistry::getTableLocator()->get('RegistrationReminders');
        $row = $reminders->newEntity([
            'user_id' => (string)($member->id ?? ''),
            'competition_id' => (int)$competition->id,
            'sender_id' => $senderId !== '' ? $senderId : null,
            'subject' => $subject,
            'message' => $message,
        ]);
        if (!$reminders->save($row)) {
            Log::error('A nevezési emlékeztető kiment, a naplóba nem került be.');
            $row->set('created', DateTime::now());
        }

        return $row;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public function compose(Competition $competition, object $member): array
    {
        $name = trim((string)$competition->name);
        $subject = $name !== ''
            ? 'Még nem jelentkeztél a versenyre: ' . $name
            : 'Még nem jelentkeztél a versenyre';
        $role = (string)($member->get('role') ?? '');
        $url = MailLinks::competitionSheet($role, (int)$competition->id);
        $details = "Még nem jelentkeztél a versenyre.\n\n";
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
        $info = $competition->infoUrl();
        if ($info !== null) {
            $details .= "A verseny leírása: {$info}\n";
        }
        [$message, $html] = OutgoingMail::competitionRegistration(
            $member,
            $details,
            $url,
            null,
        );

        return [$subject, $message, $html];
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
