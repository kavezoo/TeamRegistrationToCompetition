<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $user_id
 * @property int $competition_id
 * @property string|null $sender_id
 * @property string $subject
 * @property string $message
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class RegistrationReminder extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'competition_id' => true,
        'sender_id' => true,
        'subject' => true,
        'message' => true,
        'created' => true,
        'modified' => true,
    ];
}
