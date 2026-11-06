<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use App\Model\Entity\User;
use Cake\Datasource\EntityInterface;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Lapozás — globális: JeffAdmin.paginate.limit / maxLimit (config/paginate.php).
        // Helyi felülírás:
        // $this->paginate['limit'] = 10;
        // $this->paginate['maxLimit'] = 100;

        $query = $this->Users->find();

        // Header kereső (?search=) — sessionből a JeffAdmin AppController állítja vissza.
        // Aktív: name (vagy title / első string). Többi mező / BelongsTo: uncomment.
        // Több szó: minden szóra AND; egy szón belül OR a mezőkön (bárhol, sorrend nélkül).
        $search = trim((string)$this->request->getQuery('search', ''));
        if ($search !== '') {
            $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];


            foreach ($words as $word) {
                $like = '%' . addcslashes($word, '%_\\') . '%';
                $query->where([
                    'OR' => [
                        'Users.username LIKE' => $like,
                        'Users.email LIKE' => $like,
                        'Users.first_name LIKE' => $like,
                        'Users.last_name LIKE' => $like,
                        // 'Users.token LIKE' => $like,
                        // 'Users.api_token LIKE' => $like,
                        // 'Users.secret LIKE' => $like,
                        // 'Users.role LIKE' => $like,
                        // 'Users.additional_data LIKE' => $like,
                        // 'Users.login_token LIKE' => $like,
                    ],
                ]);
            }

            $query->distinct(['Users.id']);
        }

        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->rememberLastRecord($id);

        $user = $this->Users->get($id, contain: ['FailedPasswordAttempts', 'Registrations' => ['Teams'], 'SocialAccounts']);
        $roles = $this->roleOptions($user);
        $this->set(compact('user', 'roles'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        $user->set('role', User::ROLE_NEW, ['guard' => false]);
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $this->Users->patchEntity($user, $data);
            if ($this->roleIsAllowed($user, $data)) {
                $user->set('role', (string)$data['role'], ['guard' => false]);
            }
            if (!$user->getErrors() && $this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                $this->rememberLastRecord($user->id);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $roles = $this->roleOptions($user);
        $this->set(compact('user', 'roles'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->rememberLastRecord($id);

        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['enabled'] = !empty($data['enabled']);
            if (!$this->roleIsAllowed($user, $data)) {
                $this->Flash->error('Válassz érvényes szerepet.');
            } else {
                $user = $this->Users->patchEntity($user, $data, [
                    'fields' => ['username', 'email', 'first_name', 'last_name', 'role', 'enabled'],
                    'accessibleFields' => [
                        'username' => true,
                        'email' => true,
                        'first_name' => true,
                        'last_name' => true,
                        'role' => true,
                        'enabled' => true,
                    ],
                ]);
                if ($this->Users->save($user)) {
                    $this->Flash->success(__('The user has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        if ($user->get('enabled') === null) {
            $user->set('enabled', true, ['guard' => false]);
        }
        $roles = $this->roleOptions($user);
        $this->set(compact('user', 'roles'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        foreach ($user->toArray() as $field => $value) {
            if (is_string($field) && str_ends_with($field, '_count') && (int)$value > 0) {
                $this->Flash->error(__('Cannot delete: related records exist'));

                return $this->redirect(['action' => 'index']);
            }
        }
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * A bejelentkezett admin saját profilja.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function profile()
    {
        $identity = $this->request->getAttribute('identity');
        $id = $identity ? ($identity['id'] ?? null) : null;
        if ($id === null || $id === '') {
            return $this->redirect('/login');
        }

        $user = $this->Users->get($id, contain: ['SocialAccounts']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['first_name', 'last_name'],
            ]);
            if ($this->Users->save($user)) {
                $this->Flash->success('A profil mentve.');

                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('A profilt nem sikerült menteni.');
        }

        $this->set(compact('user'));
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(EntityInterface $user): array
    {
        $roles = User::ROLES;
        $current = (string)$user->role;
        if ($current !== '' && !isset($roles[$current])) {
            $roles[$current] = $current;
        }

        return $roles;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function roleIsAllowed(EntityInterface $user, array $data): bool
    {
        $role = (string)($data['role'] ?? '');
        $allowed = User::ROLES;
        $current = $user->getOriginal('role');
        if (is_string($current) && $current !== '' && !isset($allowed[$current])) {
            $allowed[$current] = $current;
        }
        if (isset($allowed[$role])) {
            return true;
        }

        $user->setError('role', 'Válassz érvényes szerepet.');

        return false;
    }
}
