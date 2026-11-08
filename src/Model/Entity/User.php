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
 * @property bool $mail_membership
 * @property bool $mail_signup
 * @property bool $mail_assignment
 * @property bool $mail_competition
 * @property bool $mail_reminder
 * @property bool $mail_applicants
 * @property bool $mail_president_entries
 * @property bool $flash_membership
 * @property bool $flash_signup
 * @property bool $flash_assignment
 * @property bool $flash_competition
 * @property bool $flash_reminder
 * @property bool $flash_applicants
 * @property bool $flash_president_entries
 * @property bool $flash_message
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

    public const DENIED_NOTICE = 'A klubelnök elutasította a jelentkezésedet. A profilodat nem módosíthatod. Az adataidat törölheted. Ha mégis jelentkezni szeretnél, töröld az adataidat, és egy új bejelentkezés után jelentkezz újra.';

    public const MAIL_MEMBERSHIP = 'mail_membership';

    public const MAIL_SIGNUP = 'mail_signup';

    public const MAIL_ASSIGNMENT = 'mail_assignment';

    public const MAIL_COMPETITION = 'mail_competition';

    public const MAIL_REMINDER = 'mail_reminder';

    public const MAIL_APPLICANTS = 'mail_applicants';

    public const MAIL_PRESIDENT_ENTRIES = 'mail_president_entries';

    public const FLASH_MEMBERSHIP = 'flash_membership';

    public const FLASH_SIGNUP = 'flash_signup';

    public const FLASH_ASSIGNMENT = 'flash_assignment';

    public const FLASH_COMPETITION = 'flash_competition';

    public const FLASH_REMINDER = 'flash_reminder';

    public const FLASH_APPLICANTS = 'flash_applicants';

    public const FLASH_PRESIDENT_ENTRIES = 'flash_president_entries';

    public const FLASH_MESSAGE = 'flash_message';

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

    /**
     * Új szerep, amíg a klubelnök el nem utasította.
     * A Google-belépés után a kérelem mező még üres is lehet.
     *
     * @return array<string, mixed>
     */
    public static function openApplicantConditions(Table $users): array
    {
        $request = $users->aliasField('membership_request');

        return [
            $users->aliasField('role') => self::ROLE_NEW,
            'OR' => [
                [$request . ' IS' => null],
                [$request => ''],
                [$request => 'pending'],
            ],
        ];
    }

    /**
     * A saját adatlapján ezek közül választja ki, melyik levelet kéri.
     * Az oszlop alapból be van kapcsolva.
     *
     * @return array<string, string>
     */
    public static function mailChoices(string $role): array
    {
        $membership = 'E-mail a klubtagság elfogadásáról és elutasításáról';
        $signup = 'E-mail, ha jelentkezem egy versenyre';
        $assignment = 'E-mail, ha alcsoportba sorolnak';
        $competition = 'E-mail, ha új versenyt írnak ki';
        $reminder = 'Emlékeztető, ha még nem jelentkeztem egy versenyre';
        $applicants = 'E-mail az új és a törölt klubjelentkezőkről';
        $entries = 'E-mail, ha valaki jelentkezik egy versenyre vagy lemondja';

        return match ($role) {
            self::ROLE_NEW => [
                self::MAIL_MEMBERSHIP => $membership,
            ],
            self::ROLE_MEMBER => [
                self::MAIL_SIGNUP => $signup,
                self::MAIL_ASSIGNMENT => $assignment,
                self::MAIL_COMPETITION => $competition,
                self::MAIL_REMINDER => $reminder,
            ],
            self::ROLE_PRESIDENT => [
                self::MAIL_APPLICANTS => $applicants,
                self::MAIL_PRESIDENT_ENTRIES => $entries,
                self::MAIL_SIGNUP => $signup,
                self::MAIL_ASSIGNMENT => $assignment,
            ],
            self::ROLE_ADMIN => [
                self::MAIL_MEMBERSHIP => $membership,
                self::MAIL_SIGNUP => $signup,
                self::MAIL_ASSIGNMENT => $assignment,
                self::MAIL_COMPETITION => $competition,
                self::MAIL_REMINDER => $reminder,
                self::MAIL_APPLICANTS => $applicants,
                self::MAIL_PRESIDENT_ENTRIES => $entries,
            ],
            default => [],
        };
    }

    /**
     * A telefonos üzenet fajtái, szerepenként. Az egyedi üzenetet tag és elnök kapja.
     *
     * @return array<string, string>
     */
    public static function flashChoices(string $role): array
    {
        $membership = 'Telefonos üzenet a klubtagság elfogadásáról és elutasításáról';
        $signup = 'Telefonos üzenet, ha jelentkezem egy versenyre';
        $assignment = 'Telefonos üzenet, ha alcsoportba sorolnak';
        $competition = 'Telefonos üzenet, ha új versenyt írnak ki';
        $reminder = 'Telefonos emlékeztető, ha még nem jelentkeztem egy versenyre';
        $applicants = 'Telefonos üzenet az új és a törölt klubjelentkezőkről';
        $entries = 'Telefonos üzenet, ha valaki jelentkezik egy versenyre vagy lemondja';
        // A klubüzenet (flash_message) mindig kimegy; nincs kapcsoló hozzá.

        return match ($role) {
            self::ROLE_NEW => [
                self::FLASH_MEMBERSHIP => $membership,
            ],
            self::ROLE_MEMBER => [
                self::FLASH_SIGNUP => $signup,
                self::FLASH_ASSIGNMENT => $assignment,
                self::FLASH_COMPETITION => $competition,
                self::FLASH_REMINDER => $reminder,
            ],
            self::ROLE_PRESIDENT => [
                self::FLASH_APPLICANTS => $applicants,
                self::FLASH_PRESIDENT_ENTRIES => $entries,
                self::FLASH_SIGNUP => $signup,
                self::FLASH_ASSIGNMENT => $assignment,
            ],
            self::ROLE_ADMIN => [
                self::FLASH_MEMBERSHIP => $membership,
                self::FLASH_SIGNUP => $signup,
                self::FLASH_ASSIGNMENT => $assignment,
                self::FLASH_COMPETITION => $competition,
                self::FLASH_REMINDER => $reminder,
                self::FLASH_APPLICANTS => $applicants,
                self::FLASH_PRESIDENT_ENTRIES => $entries,
            ],
            default => [],
        };
    }

    /**
     * A levélfajta párja a telefonos üzeneteknél. Ismeretlen mezőnél nincs szűrés.
     */
    public static function flashKind(?string $mailField): ?string
    {
        return match ($mailField) {
            self::MAIL_MEMBERSHIP => self::FLASH_MEMBERSHIP,
            self::MAIL_SIGNUP => self::FLASH_SIGNUP,
            self::MAIL_ASSIGNMENT => self::FLASH_ASSIGNMENT,
            self::MAIL_COMPETITION => self::FLASH_COMPETITION,
            self::MAIL_REMINDER => self::FLASH_REMINDER,
            self::MAIL_APPLICANTS => self::FLASH_APPLICANTS,
            self::MAIL_PRESIDENT_ENTRIES => self::FLASH_PRESIDENT_ENTRIES,
            default => null,
        };
    }

    /**
     * Üres vagy hiányzó jelölés bekapcsolt levelet jelent, amíg az oszlop nincs betöltve.
     */
    public static function wantsMail(object|array|null $user, string $field): bool
    {
        if ($user === null) {
            return false;
        }
        $value = is_array($user) ? ($user[$field] ?? null) : $user->get($field);
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Üres vagy hiányzó jelölés bekapcsolt telefonos üzenetet jelent.
     */
    public static function wantsFlash(object|array|null $user, string $field): bool
    {
        return self::wantsMail($user, $field);
    }

    /**
     * A profil mentése a szerephez tartozó levélkapcsolókat is eltárolja.
     * A még nem létező oszlopot kihagyja, hogy a mentés a tábla bővítése előtt is sikerüljön.
     * A telefonos kapcsolót csak akkor írja, ha a kérésben szerepel, hogy a régi alkalmazás ne kapcsolja ki.
     */
    public static function applyMailRequest(object $user, array $data, object $schema): void
    {
        if (!method_exists($user, 'set') || !method_exists($schema, 'hasColumn')) {
            return;
        }
        $role = (string)$user->get('role');
        foreach (array_keys(self::mailChoices($role)) as $field) {
            if (!$schema->hasColumn($field)) {
                continue;
            }
            $user->set($field, filter_var($data[$field] ?? false, FILTER_VALIDATE_BOOLEAN), ['guard' => false]);
            $user->setDirty($field, true);
        }
        foreach (array_keys(self::flashChoices($role)) as $field) {
            if (!$schema->hasColumn($field) || !array_key_exists($field, $data)) {
                continue;
            }
            $user->set($field, filter_var($data[$field] ?? false, FILTER_VALIDATE_BOOLEAN), ['guard' => false]);
            $user->setDirty($field, true);
        }
    }

    /**
     * A mobilalkalmazásnak visszaadott profil. A first_name a vezetéknév.
     *
     * @return array<string, mixed>
     */
    public static function apiProfile(object $user): array
    {
        $role = (string)$user->get('role');
        $mail = [];
        foreach (array_keys(self::mailChoices($role)) as $field) {
            $mail[$field] = self::wantsMail($user, $field);
        }
        $flash = [];
        foreach (array_keys(self::flashChoices($role)) as $field) {
            $flash[$field] = self::wantsFlash($user, $field);
        }

        return [
            'id' => (string)$user->get('id'),
            'username' => (string)$user->get('username'),
            'email' => (string)$user->get('email'),
            'first_name' => (string)$user->get('first_name'),
            'last_name' => (string)$user->get('last_name'),
            'name' => self::displayName($user),
            'role' => $role,
            'membership_request' => $user->get('membership_request'),
            'profile_saved' => filter_var($user->get('profile_saved'), FILTER_VALIDATE_BOOLEAN),
            'enabled' => self::isEnabled($user),
            'tos_date' => $user->get('tos_date'),
            'mail' => $mail,
            'mail_choices' => self::mailChoices($role),
            'flash' => $flash,
            'flash_choices' => self::flashChoices($role),
        ];
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
        'mail_membership' => true,
        'mail_signup' => true,
        'mail_assignment' => true,
        'mail_competition' => true,
        'mail_reminder' => true,
        'mail_applicants' => true,
        'mail_president_entries' => true,
        'flash_membership' => true,
        'flash_signup' => true,
        'flash_assignment' => true,
        'flash_competition' => true,
        'flash_reminder' => true,
        'flash_applicants' => true,
        'flash_president_entries' => true,
        'flash_message' => true,
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
