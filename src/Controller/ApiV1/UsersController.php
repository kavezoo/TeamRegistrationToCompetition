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
     * Az új jelentkező törli a saját adatait. A webes munkamenetet is lezárja, ha van.
     */
    public function deleteAccount(): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $id = $this->identityId();
        if ($id === null) {
            throw new UnauthorizedException('Bejelentkezés szükséges.');
        }
        $user = $this->Users->get($id);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            $this->respondForbidden('Az adatok törlése csak új jelentkezőnek érhető el.');

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
            Log::error('Az új jelentkező adatait nem sikerült törölni: ' . $exception->getMessage());
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

        $reminders = $this->fetchTable('RegistrationReminders');
        if ($reminders->getSchema()->hasColumn('user_id')) {
            $reminders->deleteAll(['user_id' => $userId]);
        }
        if ($reminders->getSchema()->hasColumn('sender_id')) {
            $reminders->updateAll(['sender_id' => null], ['sender_id' => $userId]);
        }
        try {
            $sessions = $this->fetchTable('MobileSessions');
            if ($sessions->getSchema()->hasColumn('user_id')) {
                $sessions->deleteAll(['user_id' => $userId]);
            }
        } catch (\Throwable) {
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
