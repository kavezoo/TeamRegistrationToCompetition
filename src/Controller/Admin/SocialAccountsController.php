<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

/**
 * SocialAccounts Controller
 *
 * @property \App\Model\Table\SocialAccountsTable $SocialAccounts
 */
class SocialAccountsController extends AppController
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

        $query = $this->SocialAccounts->find()
            ->contain(['Users']);

        // Header kereső (?search=) — sessionből a JeffAdmin AppController állítja vissza.
        // Aktív: name (vagy title / első string). Többi mező / BelongsTo: uncomment.
        // Több szó: minden szóra AND; egy szón belül OR a mezőkön (bárhol, sorrend nélkül).
        $search = trim((string)$this->request->getQuery('search', ''));
        if ($search !== '') {
            $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            // $query->leftJoinWith('Users');

            foreach ($words as $word) {
                $like = '%' . addcslashes($word, '%_\\') . '%';
                $query->where([
                    'OR' => [
                        'SocialAccounts.provider LIKE' => $like,
                        // 'SocialAccounts.username LIKE' => $like,
                        // 'SocialAccounts.reference LIKE' => $like,
                        // 'SocialAccounts.avatar LIKE' => $like,
                        // 'SocialAccounts.description LIKE' => $like,
                        // 'SocialAccounts.link LIKE' => $like,
                        // 'SocialAccounts.token LIKE' => $like,
                        // 'SocialAccounts.token_secret LIKE' => $like,
                        // 'SocialAccounts.data LIKE' => $like,
                        // 'Users.username LIKE' => $like,
                    ],
                ]);
            }

            $query->distinct(['SocialAccounts.id']);
        }

        $socialAccounts = $this->paginate($query);

        $this->set(compact('socialAccounts'));
    }

    /**
     * View method
     *
     * @param string|null $id Social Account id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->rememberLastRecord($id);

        $socialAccount = $this->SocialAccounts->get($id, contain: ['Users']);
        //dd($id);
        //dd($socialAccount->toArray());
        $users = \App\Model\Entity\User::nameList($this->SocialAccounts->Users);
        $this->set(compact('socialAccount', 'users'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $socialAccount = $this->SocialAccounts->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            //dd($data);
            $socialAccount = $this->SocialAccounts->patchEntity($socialAccount, $data);
            //dd($socialAccount->toArray());
            //dd($socialAccount->getErrors());
            if ($this->SocialAccounts->save($socialAccount)) {
                $this->Flash->success(__('The social account has been saved.'));
                $this->rememberLastRecord($socialAccount->id);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The social account could not be saved. Please, try again.'));
        }
        $users = \App\Model\Entity\User::nameList($this->SocialAccounts->Users);
        $this->set(compact('socialAccount', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Social Account id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->rememberLastRecord($id);

        $socialAccount = $this->SocialAccounts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            //dd($data);
            $socialAccount = $this->SocialAccounts->patchEntity($socialAccount, $data);
            //dd($socialAccount->toArray());
            //dd($socialAccount->getErrors());
            if ($this->SocialAccounts->save($socialAccount)) {
                $this->Flash->success(__('The social account has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The social account could not be saved. Please, try again.'));
        }
        $users = \App\Model\Entity\User::nameList($this->SocialAccounts->Users);
        $this->set(compact('socialAccount', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Social Account id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $socialAccount = $this->SocialAccounts->get($id);
        foreach ($socialAccount->toArray() as $field => $value) {
            if (is_string($field) && str_ends_with($field, '_count') && (int)$value > 0) {
                $this->Flash->error(__('Cannot delete: related records exist'));

                return $this->redirect(['action' => 'index']);
            }
        }
        if ($this->SocialAccounts->delete($socialAccount)) {
            $this->Flash->success(__('The social account has been deleted.'));
        } else {
            $this->Flash->error(__('The social account could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
