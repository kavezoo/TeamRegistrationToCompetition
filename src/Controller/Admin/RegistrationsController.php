<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class RegistrationsController extends AppController
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

        $query = $this->Registrations->find()
            ->contain(['Users', 'Teams', 'Competitions']);

        // Header kereső (?search=) — sessionből a JeffAdmin AppController állítja vissza.
        // Aktív: name (vagy title / első string). Többi mező / BelongsTo: uncomment.
        // Több szó: minden szóra AND; egy szón belül OR a mezőkön (bárhol, sorrend nélkül).
        $search = trim((string)$this->request->getQuery('search', ''));
        if ($search !== '') {
            $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            // $query->leftJoinWith('Users');
            // $query->leftJoinWith('Teams');
            // $query->leftJoinWith('Competitions');

            foreach ($words as $word) {
                $like = '%' . addcslashes($word, '%_\\') . '%';
                $query->where([
                    'OR' => [
                        'Registrations.user_id LIKE' => $like,
                        // 'Users.username LIKE' => $like,
                        // 'Teams.name LIKE' => $like,
                        // 'Competitions.name LIKE' => $like,
                    ],
                ]);
            }

            $query->distinct(['Registrations.id']);
        }

        $registrations = $this->paginate($query);

        $this->set(compact('registrations'));
    }

    /**
     * View method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->rememberLastRecord($id);

        $registration = $this->Registrations->get($id, contain: ['Users', 'Teams', 'Competitions']);
        //dd($id);
        //dd($registration->toArray());
        $users = \App\Model\Entity\User::nameList($this->Registrations->Users);
        $teams = $this->Registrations->Teams->find('list', limit: 200)->all();
        $competitions = $this->Registrations->Competitions->find('list', limit: 200)->all();
        $this->set(compact('registration', 'users', 'teams', 'competitions'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $registration = $this->Registrations->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            //dd($data);
            $registration = $this->Registrations->patchEntity($registration, $data);
            //dd($registration->toArray());
            //dd($registration->getErrors());
            if ($this->Registrations->save($registration)) {
                $this->Flash->success(__('The registration has been saved.'));
                $this->rememberLastRecord($registration->id);

                return $this->redirectAfterSave();
            }
            $this->Flash->error(__('The registration could not be saved. Please, try again.'));
        }
        $users = \App\Model\Entity\User::nameList($this->Registrations->Users);
        $teams = $this->Registrations->Teams->find('list', limit: 200)->all();
        $competitions = $this->Registrations->Competitions->find('list', limit: 200)->all();
        $this->set(compact('registration', 'users', 'teams', 'competitions'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->rememberLastRecord($id);

        $registration = $this->Registrations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            //dd($data);
            $registration = $this->Registrations->patchEntity($registration, $data);
            //dd($registration->toArray());
            //dd($registration->getErrors());
            if ($this->Registrations->save($registration)) {
                $this->Flash->success(__('The registration has been saved.'));

                return $this->redirectAfterSave();
            }
            $this->Flash->error(__('The registration could not be saved. Please, try again.'));
        }
        $users = \App\Model\Entity\User::nameList($this->Registrations->Users);
        $teams = $this->Registrations->Teams->find('list', limit: 200)->all();
        $competitions = $this->Registrations->Competitions->find('list', limit: 200)->all();
        $this->set(compact('registration', 'users', 'teams', 'competitions'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Registration id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $registration = $this->Registrations->get($id);
        foreach ($registration->toArray() as $field => $value) {
            if (is_string($field) && str_ends_with($field, '_count') && (int)$value > 0) {
                $this->Flash->error(__('Cannot delete: related records exist'));

                return $this->redirect(['action' => 'index']);
            }
        }
        if ($this->Registrations->delete($registration)) {
            $this->Flash->success(__('The registration has been deleted.'));
        } else {
            $this->Flash->error(__('The registration could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
