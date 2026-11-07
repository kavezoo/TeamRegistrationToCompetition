<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $device_name
 * @property string|null $platform
 * @property string $refresh_token_hash
 * @property string|null $previous_refresh_hash
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $last_used
 * @property \Cake\I18n\DateTime $expires
 * @property \Cake\I18n\DateTime|null $revoked
 */
class MobileSession extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'device_name' => true,
        'platform' => true,
        'refresh_token_hash' => true,
        'previous_refresh_hash' => true,
        'created' => true,
        'last_used' => true,
        'expires' => true,
        'revoked' => true,
    ];

    protected array $_hidden = [
        'refresh_token_hash',
        'previous_refresh_hash',
    ];
}
