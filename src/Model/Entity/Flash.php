<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $user_id
 * @property string $subject
 * @property string $body
 * @property string $target
 * @property string|null $target_id
 * @property \Cake\I18n\DateTime|null $seen
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class Flash extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'subject' => true,
        'body' => true,
        'target' => true,
        'target_id' => true,
        'seen' => true,
        'created' => true,
        'modified' => true,
    ];
}
