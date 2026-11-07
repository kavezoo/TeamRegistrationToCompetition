<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Entity\User;
use App\Service\MembershipNotifier;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\ORM\Table;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/5/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path || in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        if (($path[0] ?? null) === 'home' && !isset($path[1])) {
            return $this->home();
        }

        return $this->redirect('/');
    }

    /**
     * Az új jelentkező saját adata. Mentés után a főoldalra lép.
     */
    public function profile(): ?Response
    {
        $users = $this->fetchTable('Users');
        $user = $this->newUser($users);
        if ($user === null) {
            return $this->redirect('/');
        }
        $this->viewBuilder()->setLayout('JeffAdmin.default');
        if ($this->request->is(['post', 'put', 'patch']) && $this->storeProfile($users, $user)) {
            return $this->redirect('/');
        }
        $users->loadInto($user, User::socialAvatarContain());
        $this->set('profileSaved', $this->profileSaved($user));
        $this->set(compact('user'));

        return $this->render('new_profile');
    }

    public function saveProfile(): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        return $this->profile() ?? $this->redirect('/');
    }

    public function requestMembership(): Response
    {
        $this->request->allowMethod(['post']);
        $users = $this->fetchTable('Users');
        $user = $users->get($this->request->getAttribute('identity')['id']);
        if ((string)$user->get('role') !== User::ROLE_NEW || !$this->profileSaved($user)) {
            $this->Flash->error('Előbb mentsd a profilodat.');

            return $this->redirect('/');
        }
        if ((string)$user->get('membership_request') === 'pending') {
            $this->Flash->success('A felvételi kérelem már a klubelnöknél van.');

            return $this->redirect('/');
        }
        $user->set('membership_request', 'pending', ['guard' => false]);
        if (!$users->save($user)) {
            $this->Flash->error('A kérelmet nem sikerült elküldeni.');

            return $this->redirect('/');
        }
        (new MembershipNotifier())->notifyPresidents($user);
        $this->Flash->success('A felvételi kérelem elküldve a klubelnöknek.');

        return $this->redirect('/');
    }

    /**
     * Az új jelentkező törli a saját adatait. Tag és elnök ezt nem teheti meg itt.
     */
    public function deleteAccount(): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $users = $this->fetchTable('Users');
        $user = $this->newUser($users);
        if ($user === null) {
            $this->Flash->error('Az adatok törlése csak új jelentkezőnek érhető el.');

            return $this->redirect('/');
        }

        $userId = (string)$user->get('id');
        $connection = $users->getConnection();
        $connection->begin();
        try {
            $this->deleteOwnedRows($users, $userId);
            if (!$users->delete($user)) {
                throw new \RuntimeException('A felhasználó törlése nem sikerült.');
            }
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollback();
            Log::error('Az új jelentkező adatait nem sikerült törölni: ' . $exception->getMessage());
            $this->Flash->error('Az adataidat nem sikerült törölni.');

            return $this->redirect('/');
        }

        $service = $this->request->getAttribute('authentication');
        if (is_object($service) && method_exists($service, 'clearIdentity')) {
            $cleared = $service->clearIdentity($this->request, $this->response);
            $this->setRequest($cleared['request']);
            $this->setResponse($cleared['response']);
        }
        $this->request->getSession()->delete('Auth');
        $this->Flash->success('Az adataid törölve lettek, és kijelentkeztettünk.');

        return $this->redirect('/login');
    }

    private function home(): Response
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            return $this->redirect('/login');
        }

        $user = $this->fetchTable('Users')->get($identity['id']);
        $role = (string)$user->get('role');
        if (!empty($user->get('is_superuser'))) {
            return $this->redirect('/admin');
        }
        $target = match ($role) {
            User::ROLE_ADMIN => '/admin',
            User::ROLE_PRESIDENT => '/president',
            User::ROLE_MEMBER => '/member',
            default => null,
        };
        if ($role === User::ROLE_NEW) {
            if (!$this->profileSaved($user)) {
                return $this->redirect(['controller' => 'Pages', 'action' => 'profile']);
            }
            $this->viewBuilder()->setLayout('JeffAdmin.default');
            $this->set(compact('user'));

            return $this->render('new_home');
        }
        if ($target !== null) {
            return $this->redirect($target);
        }

        return $this->redirect('/login');
    }

    private function newUser(Table $users): ?EntityInterface
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            return null;
        }
        $user = $users->get($identity['id']);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            return null;
        }

        return $user;
    }

    /**
     * A név mentése egyben a klubfelvételi kérelem is, ha még nincs a klubelnöknél.
     */
    private function storeProfile(Table $users, EntityInterface $user): bool
    {
        $user = $users->patchEntity($user, $this->request->getData(), [
            'fields' => ['first_name', 'last_name'],
        ]);
        $hasName = trim((string)$user->get('first_name')) !== '' && trim((string)$user->get('last_name')) !== '';
        if (!$hasName) {
            $this->Flash->error('A vezetéknév és a keresztnév is kell.');

            return false;
        }
        $user->set('profile_saved', true, ['guard' => false]);
        $user->setDirty('profile_saved', true);
        $notify = (string)$user->get('membership_request') !== 'pending';
        if ($notify) {
            $user->set('membership_request', 'pending', ['guard' => false]);
            $user->setDirty('membership_request', true);
        }
        if (!$users->save($user)) {
            $this->Flash->error('A profilt nem sikerült menteni.');

            return false;
        }
        if ($notify) {
            (new MembershipNotifier())->notifyPresidents($user);
        }
        $this->Flash->success('A profil mentve. A kérelem a klubelnöknél van. Ha elfogadja, e-mailt küldünk róla. Utána ki kell jelentkezned innen. A következő bejelentkezéskor minden elérhető lesz az új tagoknak.');

        return true;
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
    }

    private function profileSaved(object $user): bool
    {
        $hasName = trim((string)$user->get('first_name')) !== '' && trim((string)$user->get('last_name')) !== '';

        return $hasName && filter_var($user->get('profile_saved'), FILTER_VALIDATE_BOOLEAN);
    }
}
