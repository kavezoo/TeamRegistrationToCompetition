<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Competition;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use DateTimeInterface;

/**
 * Emlékeztető annak a tagnak, aki még nem jelentkezett a versenyre.
 * A levél szövege a naplóba is bekerül.
 */
class RegistrationReminderNotifier
{
    public function remind(object $member, Competition $competition, string $senderId): bool
    {
        $to = trim((string)($member->email ?? ''));
        if ($to === '') {
            return false;
        }

        [$subject, $message] = $this->compose($competition);
        try {
            $mailer = new Mailer('default');
            $mailer
                ->setTo($to)
                ->setSubject($subject)
                ->deliver($message);
        } catch (\Throwable $exception) {
            Log::error('A nevezési emlékeztető nem ment ki: ' . $exception->getMessage());

            return false;
        }

        $row = TableRegistry::getTableLocator()->get('RegistrationReminders')->newEntity([
            'user_id' => (string)($member->id ?? ''),
            'competition_id' => (int)$competition->id,
            'sender_id' => $senderId !== '' ? $senderId : null,
            'subject' => $subject,
            'message' => $message,
        ]);
        if (!TableRegistry::getTableLocator()->get('RegistrationReminders')->save($row)) {
            Log::error('A nevezési emlékeztető kiment, a naplóba nem került be.');
        }

        return true;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function compose(Competition $competition): array
    {
        $name = trim((string)$competition->name);
        $subject = $name !== ''
            ? 'Még nem jelentkeztél a versenyre: ' . $name
            : 'Még nem jelentkeztél a versenyre';
        $url = Router::url([
            'prefix' => 'Member',
            'controller' => 'Registrations',
            'action' => 'index',
        ], true);
        $message = "Még nem jelentkeztél a versenyre.\n\n";
        if ($name !== '') {
            $message .= "Verseny: {$name}\n";
        }
        $when = $this->formatWhen($competition->date_competition);
        if ($when !== '') {
            $message .= "Verseny ideje: {$when}\n";
        }
        $from = $this->formatDay($competition->date_from);
        if ($from !== '') {
            $message .= "Jelentkezés kezdete: {$from}\n";
        }
        $deadline = $this->formatDay($competition->date_deadline);
        if ($deadline !== '') {
            $message .= "Jelentkezési határidő: {$deadline}\n";
        }
        $message .= "\nItt lehet jelentkezni:\n{$url}\n";

        return [$subject, $message];
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
