<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

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
                        // 'Users.email LIKE' => $like,
                        // 'Users.first_name LIKE' => $like,
                        // 'Users.last_name LIKE' => $like,
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
        //dd($id);
        //dd($user->toArray());
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            //dd($data);
            $user = $this->Users->patchEntity($user, $data);
            //dd($user->toArray());
            //dd($user->getErrors());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                $this->rememberLastRecord($user->id);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
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
            //dd($data);
            $user = $this->Users->patchEntity($user, $data);
            //dd($user->toArray());
            //dd($user->getErrors());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
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
                'fields' => ['username', 'email', 'first_name', 'last_name'],
            ]);
            if ($this->Users->save($user)) {
                $this->Flash->success('A profil mentve.');

                return $this->redirect(['action' => 'profile']);
            }
            $this->Flash->error('A profilt nem sikerült menteni.');
        }

        $this->set(compact('user'));
    }
}
