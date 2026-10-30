<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

/**
 * FailedPasswordAttempts Controller
 *
 * @property \App\Model\Table\FailedPasswordAttemptsTable $FailedPasswordAttempts
 */
class FailedPasswordAttemptsController extends AppController
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

        $query = $this->FailedPasswordAttempts->find()
            ->contain(['Users']);

        // Header kereső (?search=) — sessionből a JeffAdmin AppController állítja vissza.
        // Aktív: name (vagy title / első string). Többi mező / BelongsTo: uncomment.
        // Több szó: minden szóra AND; egy szón belül OR a mezőkön (bárhol, sorrend nélkül).
        $search = trim((string)$this->request->getQuery('search', ''));

        $failedPasswordAttempts = $this->paginate($query);

        $this->set(compact('failedPasswordAttempts'));
    }

    /**
     * View method
     *
     * @param string|null $id Failed Password Attempt id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->rememberLastRecord($id);

        $failedPasswordAttempt = $this->FailedPasswordAttempts->get($id, contain: ['Users']);
        //dd($id);
        //dd($failedPasswordAttempt->toArray());
        $users = $this->FailedPasswordAttempts->Users->find('list', limit: 200)->all();
        $this->set(compact('failedPasswordAttempt', 'users'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $failedPasswordAttempt = $this->FailedPasswordAttempts->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            //dd($data);
            $failedPasswordAttempt = $this->FailedPasswordAttempts->patchEntity($failedPasswordAttempt, $data);
            //dd($failedPasswordAttempt->toArray());
            //dd($failedPasswordAttempt->getErrors());
            if ($this->FailedPasswordAttempts->save($failedPasswordAttempt)) {
                $this->Flash->success(__('The failed password attempt has been saved.'));
                $this->rememberLastRecord($failedPasswordAttempt->id);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The failed password attempt could not be saved. Please, try again.'));
        }
        $users = $this->FailedPasswordAttempts->Users->find('list', limit: 200)->all();
        $this->set(compact('failedPasswordAttempt', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Failed Password Attempt id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->rememberLastRecord($id);

        $failedPasswordAttempt = $this->FailedPasswordAttempts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            //dd($data);
            $failedPasswordAttempt = $this->FailedPasswordAttempts->patchEntity($failedPasswordAttempt, $data);
            //dd($failedPasswordAttempt->toArray());
            //dd($failedPasswordAttempt->getErrors());
            if ($this->FailedPasswordAttempts->save($failedPasswordAttempt)) {
                $this->Flash->success(__('The failed password attempt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The failed password attempt could not be saved. Please, try again.'));
        }
        $users = $this->FailedPasswordAttempts->Users->find('list', limit: 200)->all();
        $this->set(compact('failedPasswordAttempt', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Failed Password Attempt id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $failedPasswordAttempt = $this->FailedPasswordAttempts->get($id);
        foreach ($failedPasswordAttempt->toArray() as $field => $value) {
            if (is_string($field) && str_ends_with($field, '_count') && (int)$value > 0) {
                $this->Flash->error(__('Cannot delete: related records exist'));

                return $this->redirect(['action' => 'index']);
            }
        }
        if ($this->FailedPasswordAttempts->delete($failedPasswordAttempt)) {
            $this->Flash->success(__('The failed password attempt has been deleted.'));
        } else {
            $this->Flash->error(__('The failed password attempt could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
