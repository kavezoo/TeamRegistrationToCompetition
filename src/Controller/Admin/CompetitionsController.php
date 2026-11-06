<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

/**
 * Competitions Controller
 *
 * @property \App\Model\Table\CompetitionsTable $Competitions
 */
class CompetitionsController extends AppController
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

        $query = $this->Competitions->find();

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
                        'Competitions.name LIKE' => $like,
                        // 'Competitions.lunch_title LIKE' => $like,
                        // 'Competitions.special_lunch_title LIKE' => $like,
                        // 'Competitions.competition_pipe_title_1 LIKE' => $like,
                        // 'Competitions.competition_pipe_title_2 LIKE' => $like,
                        // 'Competitions.competition_pipe_title_3 LIKE' => $like,
                        // 'Competitions.extra_title_1 LIKE' => $like,
                        // 'Competitions.extra_title_2 LIKE' => $like,
                        // 'Competitions.extra_title_3 LIKE' => $like,
                        // 'Competitions.comment LIKE' => $like,
                    ],
                ]);
            }

            $query->distinct(['Competitions.id']);
        }

        $competitions = $this->paginate($query);

        $this->set(compact('competitions'));
    }

    /**
     * View method
     *
     * @param string|null $id Competition id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->rememberLastRecord($id);

        $competition = $this->Competitions->get($id, contain: ['Registrations' => ['Users']]);
        //dd($id);
        //dd($competition->toArray());
        $this->set(compact('competition'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $competition = $this->Competitions->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            //dd($data);
            $competition = $this->Competitions->patchEntity($competition, $data);
            //dd($competition->toArray());
            //dd($competition->getErrors());
            if ($this->Competitions->save($competition)) {
                $this->Flash->success(__('The competition has been saved.'));
                $this->rememberLastRecord($competition->id);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The competition could not be saved. Please, try again.'));
        }
        $this->set(compact('competition'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Competition id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->rememberLastRecord($id);

        $competition = $this->Competitions->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            //debug($data);
            $competition = $this->Competitions->patchEntity($competition, $data);
            //debug($competition->toArray());
            //dd($competition->getErrors());
            if ($this->Competitions->save($competition)) {
                $this->Flash->success(__('The competition has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The competition could not be saved. Please, try again.'));
        }
        $this->set(compact('competition'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Competition id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $competition = $this->Competitions->get($id);
        foreach ($competition->toArray() as $field => $value) {
            if (is_string($field) && str_ends_with($field, '_count') && (int)$value > 0) {
                $this->Flash->error(__('Cannot delete: related records exist'));

                return $this->redirect(['action' => 'index']);
            }
        }
        if ($this->Competitions->delete($competition)) {
            $this->Flash->success(__('The competition has been deleted.'));
        } else {
            $this->Flash->error(__('The competition could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
