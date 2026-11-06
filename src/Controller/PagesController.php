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
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

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
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        if ($page === 'home' && $subpage === null) {
            return $this->home();
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    public function saveProfile(): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $users = $this->fetchTable('Users');
        $user = $users->get($this->request->getAttribute('identity')['id']);
        if ((string)$user->get('role') !== User::ROLE_NEW) {
            return $this->redirect('/');
        }
        $user = $users->patchEntity($user, $this->request->getData(), [
            'fields' => ['first_name', 'last_name'],
        ]);
        $hasName = trim((string)$user->get('first_name')) !== '' && trim((string)$user->get('last_name')) !== '';
        $user->set('profile_saved', $hasName, ['guard' => false]);
        if ($users->save($user)) {
            $this->Flash->success('A profil mentve.');
        } else {
            $this->Flash->error('A profilt nem sikerült menteni.');
        }

        return $this->redirect('/');
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

    private function home(): Response
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            $page = 'home';
            $subpage = null;
            $this->set(compact('page', 'subpage'));

            return $this->render('home');
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
            $this->viewBuilder()->setLayout('login');
            $this->set('authWide', true);
            $this->set('authHeading', User::displayName($user));
            $this->set(
                'authSubtitle',
                'A jelentkezésed még nincs elfogadva. Addig a főoldalon a saját adataid szerkeszthetők.'
            );
            $this->set('profileSaved', $this->profileSaved($user));
            $this->set(compact('user'));

            return $this->render('new_profile');
        }
        if ($target !== null) {
            return $this->redirect($target);
        }

        $page = 'home';
        $subpage = null;
        $this->set(compact('page', 'subpage'));

        return $this->render('home');
    }

    private function profileSaved(object $user): bool
    {
        $hasName = trim((string)$user->get('first_name')) !== '' && trim((string)$user->get('last_name')) !== '';

        return $hasName && filter_var($user->get('profile_saved'), FILTER_VALIDATE_BOOLEAN);
    }
}
