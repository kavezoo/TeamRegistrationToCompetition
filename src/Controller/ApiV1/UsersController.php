<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Model\Entity\User;
use App\Service\MembershipNotifier;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\UnauthorizedException;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Table;

/**
 * Saját profil, levélkapcsolók, adatkezelési nyilatkozat és a saját fiók törlése.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * @var array<string>
     */
    private const WRITE_FIELDS = [
        'first_name',
        'last_name',
    ];

    public function profile(): void
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }

        $user = $this->Users->get($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $this->storeProfile($user);

            return;
        }

        $this->set([
            'success' => true,
            'user' => User::apiProfile($user),
        ]);
    }

    /**
     * A bejelentkezett felhasználó törli a saját fiókját és minden hozzá tartozó adatot.
     */
    public function deleteAccount(): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }
        $user = $this->Users->get($id);
        $deletedName = User::displayName($user);
        $deletedEmail = trim((string)$user->get('email'));
        $connection = $this->Users->getConnection();
        $connection->begin();
        try {
            $this->deleteOwnedRows($this->Users, $id);
            if (!$this->Users->delete($user)) {
                throw new \RuntimeException('A felhasználó törlése nem sikerült.');
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollback();
            Log::error('A felhasználó adatait nem sikerült törölni: ' . $exception->getMessage());
            $this->response = $this->response->withStatus(500);
            $this->set([
                'success' => false,
                'message' => 'Az adataidat nem sikerült törölni.',
            ]);

            return;
        }

        (new MembershipNotifier())->notifyDeleted($deletedName, $deletedEmail);
        $service = $this->request->getAttribute('authentication');
        if (is_object($service) && method_exists($service, 'clearIdentity')) {
            $cleared = $service->clearIdentity($this->request, $this->response);
            $this->setRequest($cleared['request']);
            $this->setResponse($cleared['response']);
        }
        $this->request->getSession()->delete('Auth');
        $this->set([
            'success' => true,
            'message' => 'Az adataid törölve lettek.',
        ]);
    }

    /**
     * Admin törli egy tag vagy klubelnök fiókját és minden hozzá tartozó adatot.
     */
    public function remove(string $id): void
    {
        $this->request->allowMethod(['delete']);
        if ($this->role() !== User::ROLE_ADMIN) {
            $this->respondForbidden('Tagot csak admin törölhet.');

            return;
        }
        if ($id === $this->identityId()) {
            $this->respondForbidden('A saját fiókodat a Saját adatok lapon törölheted.');

            return;
        }
        try {
            $user = $this->Users->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException) {
            $this->respondNotFound('Nincs ilyen felhasználó.');

            return;
        }
        $role = (string)$user->get('role');
        if ($role === User::ROLE_ADMIN || !empty($user->get('is_superuser'))) {
            $this->respondForbidden('Adminisztrátort nem lehet itt törölni.');

            return;
        }
        $deletedName = User::displayName($user);
        $deletedEmail = trim((string)$user->get('email'));
        $connection = $this->Users->getConnection();
        $connection->begin();
        try {
            $this->deleteOwnedRows($this->Users, $id);
            if (!$this->Users->delete($user)) {
                throw new \RuntimeException('A felhasználó törlése nem sikerült.');
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollback();
            Log::error('A tag törlése nem sikerült: ' . $exception->getMessage());
            $this->response = $this->response->withStatus(500);
            $this->set([
                'success' => false,
                'message' => 'A tagot nem sikerült törölni.',
            ]);

            return;
        }
        (new MembershipNotifier())->notifyDeleted($deletedName, $deletedEmail);
        $this->set([
            'success' => true,
            'message' => 'A tag és minden adata törölve lett.',
        ]);
    }

    /**
     * A klubelnök tagot elnökké nevez, vagy az elnököt visszaállítja taggá.
     */
    public function role(string $id): void
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        if (!$this->isManager()) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $role = (string)$this->request->getData('role');
        if (!in_array($role, [User::ROLE_MEMBER, User::ROLE_PRESIDENT], true)) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'A szerep tag vagy klubelnök lehet.',
            ]);

            return;
        }
        if ($id === $this->identityId()) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'A saját szereped nem változtatható itt.',
            ]);

            return;
        }
        try {
            $user = $this->Users->get($id);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException) {
            $this->respondNotFound('Nincs ilyen felhasználó.');

            return;
        }
        $current = (string)$user->get('role');
        if (
            !empty($user->get('is_superuser'))
            || !in_array($current, [User::ROLE_MEMBER, User::ROLE_PRESIDENT], true)
        ) {
            $this->respondForbidden('Ennek a felhasználónak a szerepe nem módosítható.');

            return;
        }
        $user->set('role', $role, ['guard' => false]);
        $user->setDirty('role', true);
        if (!$this->Users->save($user)) {
            $this->respondValidationError($user);

            return;
        }
        $this->set([
            'success' => true,
            'user' => [
                'id' => $user->get('id'),
                'name' => User::displayName($user),
                'role' => $user->get('role'),
            ],
        ]);
    }

    /**
     * Klubelnök vagy admin: kitiltás és visszavétel az enabled mezőn.
     */
    public function enabled(string $id): void
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $enabled = $this->readEnabled($this->request->getData('enabled'));
        if ($enabled === null) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'Az enabled mező true vagy false legyen.',
            ]);

            return;
        }

        $user = $this->Users->get($id);
        $user->set('enabled', $enabled, ['guard' => false]);
        if (!$this->Users->save($user)) {
            $this->respondValidationError($user);

            return;
        }

        $this->set([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => User::displayName($user),
                'enabled' => User::isEnabled($user),
            ],
        ]);
    }

    private function storeProfile(EntityInterface $user): void
    {
        $role = (string)$user->get('role');
        if (!in_array($role, [User::ROLE_MEMBER, User::ROLE_PRESIDENT, User::ROLE_ADMIN, User::ROLE_NEW], true)) {
            $role = User::ROLE_NEW;
            $user->set('role', User::ROLE_NEW, ['guard' => false]);
            $user->setDirty('role', true);
        }
        if ($role === User::ROLE_NEW && (string)$user->get('membership_request') === 'denied') {
            $this->respondForbidden(User::DENIED_NOTICE);

            return;
        }
        $user = $this->patchFromRequest($this->Users, $user, self::WRITE_FIELDS);
        $needsPrivacy = $role === User::ROLE_NEW || $role === User::ROLE_MEMBER;
        $alreadyAccepted = $user->get('tos_date') !== null;
        $acceptsPrivacy = filter_var($this->request->getData('accept_privacy'), FILTER_VALIDATE_BOOLEAN);
        if ($needsPrivacy && !$alreadyAccepted && !$acceptsPrivacy) {
            $this->response = $this->response->withStatus(422);
            $this->set([
                'success' => false,
                'message' => 'A mentéshez el kell fogadni az adatkezelési nyilatkozatot.',
            ]);

            return;
        }
        if ($role === User::ROLE_NEW) {
            $hasName = trim((string)$user->get('first_name')) !== '' && trim((string)$user->get('last_name')) !== '';
            if (!$hasName) {
                $this->response = $this->response->withStatus(422);
                $this->set([
                    'success' => false,
                    'message' => 'A vezetéknév és a keresztnév is kell.',
                ]);

                return;
            }
            $user->set('profile_saved', true, ['guard' => false]);
            $user->setDirty('profile_saved', true);
        }
        if ($needsPrivacy && $acceptsPrivacy) {
            $user->set('tos_date', DateTime::now(), ['guard' => false]);
            $user->setDirty('tos_date', true);
        }
        User::applyMailRequest($user, $this->request->getData(), $this->Users->getSchema());
        $notify = $role === User::ROLE_NEW && (string)$user->get('membership_request') !== 'pending';
        if ($notify) {
            $user->set('membership_request', 'pending', ['guard' => false]);
            $user->setDirty('membership_request', true);
        }
        if (!$this->Users->save($user)) {
            $this->respondValidationError($user);

            return;
        }
        if ($notify) {
            (new MembershipNotifier())->notifyPresidents($user);
        }
        $this->set([
            'success' => true,
            'user' => User::apiProfile($user),
        ]);
    }

    private function deleteOwnedRows(Table $users, string $userId): void
    {
        $reminders = $this->fetchTable('RegistrationReminders');
        if ($reminders->getSchema()->hasColumn('user_id')) {
            $reminders->deleteAll(['user_id' => $userId]);
        }
        if ($reminders->getSchema()->hasColumn('sender_id')) {
            $reminders->deleteAll(['sender_id' => $userId]);
        }
        try {
            $sessions = $this->fetchTable('MobileSessions');
            if ($sessions->getSchema()->hasColumn('user_id')) {
                $sessions->deleteAll(['user_id' => $userId]);
            }
        } catch (\Throwable) {
        }
        try {
            $flashes = $this->fetchTable('Flashes');
            if ($flashes->getSchema()->hasColumn('user_id')) {
                $flashes->deleteAll(['user_id' => $userId]);
            }
        } catch (\Throwable) {
        }
        foreach (['SocialAccounts', 'FailedPasswordAttempts', 'Registrations'] as $name) {
            if (!$users->hasAssociation($name)) {
                continue;
            }
            $table = $users->getAssociation($name)->getTarget();
            if (!$table->getSchema()->hasColumn('user_id')) {
                continue;
            }
            $table->deleteAll(['user_id' => $userId]);
        }
    }

    private function readEnabled(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }
        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        return null;
    }
}
