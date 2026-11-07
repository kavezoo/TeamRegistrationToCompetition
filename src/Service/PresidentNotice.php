<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * A klubelnökök közös levele. A művelet akkor is megmarad, ha a küldés nem sikerül.
 */
class PresidentNotice
{
    /**
     * @param list<array{0: string, 1: string}> $buttons cím, felirat
     */
    public static function send(
        string $subject,
        string $details,
        array $buttons,
        ?string $exceptUserId = null,
        ?string $mailField = null,
    ): void {
        try {
            $users = TableRegistry::getTableLocator()->get('Users');
            $select = ['id', 'email', 'first_name', 'last_name', 'username'];
            if ($mailField !== null && $users->getSchema()->hasColumn($mailField)) {
                $select[] = $mailField;
            }
            $presidents = $users->find()
                ->select($select)
                ->where([
                    'role' => User::ROLE_PRESIDENT,
                    'active' => true,
                ])
                ->all();
        } catch (\Throwable $exception) {
            Log::error('A klubelnökök értesítéséhez a címzettek nem tölthetők be: ' . $exception->getMessage());

            return;
        }

        foreach ($presidents as $president) {
            if ($exceptUserId !== null && (string)$president->id === $exceptUserId) {
                continue;
            }
            if ($mailField !== null && !User::wantsMail($president, $mailField)) {
                continue;
            }
            $to = trim((string)($president->email ?? ''));
            if ($to === '') {
                continue;
            }
            try {
                [$text, $html] = OutgoingMail::notice($president, $details, $buttons);
                OutgoingMail::send($to, $subject, $text, $html);
            } catch (\Throwable $exception) {
                Log::error('A klubelnök értesítése nem ment ki: ' . $exception->getMessage());
            }
        }
    }
}
