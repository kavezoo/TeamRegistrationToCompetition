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
 * @property bool $enabled
 * @property bool $is_superuser
 * @property string|null $role
 * @property bool $profile_saved
 * @property string|null $membership_request
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

    /**
     * A Google-profilkép címe. Csak https, a Google saját kiszolgálójáról.
     *
     * @param object|array<string, mixed>|null $user
     */
    public static function avatarUrl(object|array|null $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $accounts = is_array($user) ? ($user['social_accounts'] ?? []) : ($user->social_accounts ?? []);
        if (!is_iterable($accounts)) {
            return null;
        }

        foreach ($accounts as $account) {
            $provider = is_array($account) ? ($account['provider'] ?? '') : ($account->provider ?? '');
            if ($provider !== '' && $provider !== 'google') {
                continue;
            }
            $raw = is_array($account) ? ($account['avatar'] ?? '') : ($account->avatar ?? '');
            $url = self::googlePictureUrl(trim((string)$raw));
            if ($url !== null) {
                return $url;
            }
        }

        return null;
    }

    /**
     * A közösségi fiókból csak a kép kell, a token és a nyers Google-adat nem.
     *
     * @return array<string, mixed>
     */
    public static function socialAvatarContain(): array
    {
        return [
            'SocialAccounts' => function (\Cake\ORM\Query\SelectQuery $accounts): \Cake\ORM\Query\SelectQuery {
                return $accounts->select(['id', 'user_id', 'provider', 'avatar']);
            },
        ];
    }

    /**
     * JSON-válaszhoz: a kép címe marad, a közösségi fiók rekordja nem.
     */
    public static function publishAvatar(object $user): void
    {
        $url = self::avatarUrl($user);
        $accounts = $user->social_accounts ?? null;
        $first = null;
        if (is_array($accounts) || $accounts instanceof \ArrayAccess) {
            $first = $accounts[0] ?? null;
        }
        if (is_object($first) && method_exists($first, 'set')) {
            $first->set('avatar', $url);
        }
        if (method_exists($user, 'set')) {
            $user->set('avatar', $url);
        }
        if (method_exists($user, 'setHidden') && method_exists($user, 'getHidden')) {
            $hidden = $user->getHidden();
            if (!in_array('social_accounts', $hidden, true)) {
                $hidden[] = 'social_accounts';
                $user->setHidden($hidden);
            }
        }
    }

    private static function googlePictureUrl(string $url): ?string
    {
        if (!preg_match('#\Ahttps://#i', $url)) {
            return null;
        }
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $googleHost = $host === 'googleusercontent.com'
            || str_ends_with($host, '.googleusercontent.com')
            || $host === 'google.com'
            || str_ends_with($host, '.google.com');
        if (!$googleHost) {
            return null;
        }
        if (preg_match('#=s\d+#', $url) === 1) {
            $sized = preg_replace('~=s\d+(-[a-z])?(?=$|[?&#])~', '=s128-c', $url);

            return is_string($sized) && $sized !== '' ? $sized : $url;
        }
        if (!str_contains($url, '?')) {
            return $url . '=s128-c';
        }

        return $url;
    }

    public static function roleLabel(?string $role): string
    {
        if ($role !== null && isset(self::ROLES[$role])) {
            return self::ROLES[$role];
        }

        return (string)$role;
    }

    /**
     * Hiányzó oszlopnál beléphet. A kitiltás az enabled = hamis érték.
     */
    public static function isEnabled(object $user): bool
    {
        if (!method_exists($user, 'get')) {
            return true;
        }
        $value = $user->get('enabled');
        if ($value === null) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
        'enabled' => true,
        'is_superuser' => true,
        'role' => true,
        'profile_saved' => true,
        'membership_request' => true,
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
