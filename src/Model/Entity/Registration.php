<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Registration Entity
 *
 * @property int $id
 * @property string $user_id
 * @property int|null $team_id
 * @property int $competition_id
 * @property int $lunch_count
 * @property int $extra_lunch_count
 * @property int $competition_pipe_1
 * @property int $competition_pipe_2
 * @property int $competition_pipe_3
 * @property int $extra_count_1
 * @property int $extra_count_2
 * @property int $extra_count_3
 * @property string|null $comment
 * @property bool $absent
 * @property string|null $absent_reason
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
     * A verseny címmezője és a nevezés hozzá tartozó darabszáma.
     *
     * @var array<string, string>
     */
    public const TITLED_COUNTS = [
        'lunch_title' => 'lunch_count',
        'special_lunch_title' => 'extra_lunch_count',
        'competition_pipe_title_1' => 'competition_pipe_1',
        'competition_pipe_title_2' => 'competition_pipe_2',
        'competition_pipe_title_3' => 'competition_pipe_3',
        'extra_title_1' => 'extra_count_1',
        'extra_title_2' => 'extra_count_2',
        'extra_title_3' => 'extra_count_3',
    ];

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
        'comment' => true,
        'absent' => true,
        'absent_reason' => true,
        'visible' => true,
        'pos' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'team' => true,
        'competition' => true,
    ];

    public function isAbsent(): bool
    {
        return filter_var($this->get('absent'), FILTER_VALIDATE_BOOLEAN);
    }

    public function absenceReason(): string
    {
        return trim((string)$this->get('absent_reason'));
    }

    /**
     * Besoroltak a csoport sorrendjében, azon belül név szerint.
     * A besorolatlanok a lista végére kerülnek.
     */
    public static function compareByAssignment(object $left, object $right): int
    {
        $leftTeam = $left->team ?? null;
        $rightTeam = $right->team ?? null;
        $leftTeamName = trim((string)($leftTeam?->name ?? ''));
        $rightTeamName = trim((string)($rightTeam?->name ?? ''));
        $leftAssigned = $leftTeamName !== '';
        $rightAssigned = $rightTeamName !== '';
        if ($leftAssigned !== $rightAssigned) {
            return $leftAssigned ? -1 : 1;
        }
        $leftPos = $leftAssigned ? (int)($leftTeam?->pos ?? 0) : PHP_INT_MAX;
        $rightPos = $rightAssigned ? (int)($rightTeam?->pos ?? 0) : PHP_INT_MAX;
        if ($leftPos !== $rightPos) {
            return $leftPos <=> $rightPos;
        }
        $byTeam = strnatcasecmp($leftTeamName, $rightTeamName);
        if ($byTeam !== 0) {
            return $byTeam;
        }

        return strnatcasecmp(
            User::displayName($left->user ?? null),
            User::displayName($right->user ?? null),
        );
    }
}
