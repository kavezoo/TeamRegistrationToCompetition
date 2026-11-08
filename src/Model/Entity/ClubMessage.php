<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Két tag közötti üzenet. A thread_id a beszélgetés első üzenetének azonosítója.
 *
 * @property int $id
 * @property string $sender_id
 * @property string $recipient_id
 * @property string $subject
 * @property string $body
 * @property int $thread_id
 * @property string $kind
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class ClubMessage extends Entity
{
    protected array $_accessible = [
        'sender_id' => true,
        'recipient_id' => true,
        'subject' => true,
        'body' => true,
        'thread_id' => true,
        'kind' => true,
        'created' => true,
        'modified' => true,
    ];
}
