<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\Table;

/**
 * User Entity
 *
 * @property string $id
 * @property string $username
 * @property string|null $email
 * @property string $password
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $token
 * @property \Cake\I18n\DateTime|null $token_expires
 * @property string|null $api_token
 * @property \Cake\I18n\DateTime|null $activation_date
 * @property string|null $secret
 * @property bool|null $secret_verified
 * @property \Cake\I18n\DateTime|null $tos_date
 * @property bool $active
 * @property bool $is_superuser
 * @property string|null $role
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property array|null $additional_data
 * @property \Cake\I18n\DateTime|null $last_login
 * @property \Cake\I18n\DateTime|null $lockout_time
 * @property string|null $login_token
 * @property \Cake\I18n\DateTime|null $login_token_date
 * @property bool $token_send_requested
 *
 * @property \CakeDC\Users\Model\Entity\SocialAccount[] $social_accounts
 */
class User extends Entity
{
    public const ROLE_NEW = 'new';

    public const ROLE_MEMBER = 'member';

    public const ROLE_PRESIDENT = 'president';

    public const ROLE_ADMIN = 'admin';

    /**
     * Szerepkörök az admin űrlaphoz.
     *
     * @var array<string, string>
     */
    public const ROLES = [
        self::ROLE_NEW => 'Új jelentkező',
        self::ROLE_MEMBER => 'Tag',
        self::ROLE_PRESIDENT => 'Klubelnök',
        self::ROLE_ADMIN => 'Admin',
    ];

    /**
     * A first_name a vezetéknév, a last_name a keresztnév, mert a Google így adja át.
     * Üres névnél e-mail, majd felhasználónév.
     *
     * @param object|array<string, mixed>|null $user
     */
    public static function displayName(object|array|null $user): string
    {
        if ($user === null) {
            return '';
        }

        $name = trim(self::readField($user, 'first_name') . ' ' . self::readField($user, 'last_name'));
        if ($name !== '') {
            return $name;
        }

        $email = self::readField($user, 'email');
        if ($email !== '') {
            return $email;
        }

        return self::readField($user, 'username');
    }

    public static function roleLabel(?string $role): string
    {
        if ($role !== null && isset(self::ROLES[$role])) {
            return self::ROLES[$role];
        }

        return (string)$role;
    }

    /**
     * Legördülő: vezetéknév, keresztnév szerint rendezve.
     *
     * @return array<string, string>
     */
    public static function nameList(Table $table): array
    {
        $alias = $table->getAlias();
        $rows = $table->find()
            ->select(['id', 'last_name', 'first_name', 'email', 'username'])
            ->orderBy([
                $alias . '.first_name' => 'ASC',
                $alias . '.last_name' => 'ASC',
            ])
            ->limit(200)
            ->all();

        $list = [];
        foreach ($rows as $row) {
            $list[(string)$row->id] = self::displayName($row);
        }

        return $list;
    }

    /**
     * @param object|array<string, mixed> $user
     */
    private static function readField(object|array $user, string $field): string
    {
        if (is_array($user)) {
            return trim((string)($user[$field] ?? ''));
        }

        return trim((string)($user->{$field} ?? ''));
    }

    protected function _getFullName(): string
    {
        return self::displayName($this);
    }

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
        'username' => true,
        'email' => true,
        'password' => true,
        'first_name' => true,
        'last_name' => true,
        'token' => true,
        'token_expires' => true,
        'api_token' => true,
        'activation_date' => true,
        'secret' => true,
        'secret_verified' => true,
        'tos_date' => true,
        'active' => true,
        'is_superuser' => true,
        'role' => true,
        'created' => true,
        'modified' => true,
        'additional_data' => true,
        'last_login' => true,
        'lockout_time' => true,
        'login_token' => true,
        'login_token_date' => true,
        'token_send_requested' => true,
        'social_accounts' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
        'token',
        'token_expires',
        'api_token',
        'secret',
        'secret_verified',
        'login_token',
        'login_token_date',
    ];
}
