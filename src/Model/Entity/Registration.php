<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Registration Entity
 *
 * @property int $id
 * @property string $user_id
 * @property int $team_id
 * @property int $competition_id
 * @property int $lunch_count
 * @property int $extra_lunch_count
 * @property int $competition_pipe_1
 * @property int $competition_pipe_2
 * @property int $competition_pipe_3
 * @property int $extra_count_1
 * @property int $extra_count_2
 * @property int $extra_count_3
 * @property bool $visible
 * @property int $pos
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \CakeDC\Users\Model\Entity\User $user
 * @property \App\Model\Entity\Team $team
 * @property \App\Model\Entity\Competition $competition
 */
class Registration extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'team_id' => true,
        'competition_id' => true,
        'lunch_count' => true,
        'extra_lunch_count' => true,
        'competition_pipe_1' => true,
        'competition_pipe_2' => true,
        'competition_pipe_3' => true,
        'extra_count_1' => true,
        'extra_count_2' => true,
        'extra_count_3' => true,
        'visible' => true,
        'pos' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'team' => true,
        'competition' => true,
    ];
}
