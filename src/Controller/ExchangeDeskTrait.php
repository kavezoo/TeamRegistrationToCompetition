<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Conversation;
use App\Service\FlashNotice;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Response;
use Cake\Log\Log;

/**
 * Bake-jellegű üzenetváltás lista. A kind mondja meg, mindenkinek vagy csak az elnököknek szól.
 */
trait ExchangeDeskTrait
{
    abstract protected function exchangeKind(): string;

    public function index(): ?Response
    {
        $this->viewBuilder()->setTemplatePath('Exchange')->setTemplate('index');
        $kind = $this->exchangeKind();
        $actorId = $this->actorId();
        if ($actorId === null || !Conversation::canSee($actorId, $kind)) {
            $this->Flash->error('Ehhez nincs jogosultságod.');

            return $this->redirect('/');
        }
        $unavailable = false;
        try {
            $table = $this->fetchTable('ClubMessages');
            $query = $table->find()
                ->contain(['Senders'])
                ->where(function (QueryExpression $exp) {
                    return $exp->equalFields('ClubMessages.id', 'ClubMessages.thread_id');
                })
                ->orderBy(['ClubMessages.created' => 'DESC', 'ClubMessages.id' => 'DESC']);
            if ($table->getSchema()->hasColumn('kind')) {
                $query->where(['ClubMessages.kind' => $kind]);
            } else {
                $unavailable = true;
                $query->where(['ClubMessages.id' => 0]);
                $this->Flash->error(Conversation::UNAVAILABLE);
            }
            $search = trim((string)$this->request->getQuery('search', ''));
            if ($search !== '' && !$unavailable) {
                $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                foreach ($words as $word) {
                    $like = '%' . addcslashes($word, '%_\\') . '%';
                    $query->where([
                        'OR' => [
                            'ClubMessages.subject LIKE' => $like,
                            'ClubMessages.body LIKE' => $like,
                        ],
                    ]);
                }
            }
            $exchanges = $this->paginate($query);
        } catch (\Throwable $exception) {
            Log::error('Üzenetváltások: ' . $exception->getMessage());
            $this->Flash->error(
                Conversation::missingTable($exception)
                    ? Conversation::UNAVAILABLE
                    : 'Az üzenetváltásokat nem sikerült betölteni.'
            );
            $unavailable = true;
            $exchanges = [];
        }
        $pageTitle = $kind === Conversation::KIND_PRESIDENTS
            ? 'Elnöki üzenetváltások'
            : 'Mindenkinek szóló üzenetváltások';
        $pageSubtitle = $kind === Conversation::KIND_PRESIDENTS
            ? 'Csak a klubelnökök látják, és nekik megy ki flashként'
            : 'Minden tagnak és elnöknek kiküldött üzenetváltás';
        $this->set(compact('exchanges', 'unavailable', 'kind', 'pageTitle', 'pageSubtitle'));

        return null;
    }

    public function view(string $id = ''): ?Response
    {
        $this->viewBuilder()->setTemplatePath('Exchange')->setTemplate('view');
        $kind = $this->exchangeKind();
        $actorId = $this->actorId();
        $threadId = (int)$id;
        if ($actorId === null || !Conversation::canSee($actorId, $kind) || $threadId <= 0) {
            $this->Flash->error('Az üzenetváltás nem található.');

            return $this->redirect(['action' => 'index']);
        }
        $this->rememberLastRecord($threadId);
        try {
            if ($this->request->is('post')) {
                $result = Conversation::reply($actorId, $threadId, (string)$this->request->getData('body'));
                if ($result['error'] !== null) {
                    $this->Flash->error($result['error']);
                } else {
                    $this->Flash->success(
                        $result['flashed']
                            ? 'A válasz kiment a telefonra.'
                            : 'A válasz elment. ' . FlashNotice::missedNotice()
                    );

                    return $this->redirect(['action' => 'view', $threadId]);
                }
            }
            $thread = Conversation::open($actorId, $threadId);
        } catch (\Throwable $exception) {
            Log::error('Üzenetváltás: ' . $exception->getMessage());
            $this->Flash->error(
                Conversation::missingTable($exception)
                    ? Conversation::UNAVAILABLE
                    : 'Az üzenetváltást nem sikerült megnyitni.'
            );

            return $this->redirect(['action' => 'index']);
        }
        if ($thread === null || (string)($thread['kind'] ?? '') !== $kind) {
            $this->Flash->error('Az üzenetváltás nem található.');

            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('thread', 'kind'));

        return null;
    }

    public function add(): ?Response
    {
        $this->viewBuilder()->setTemplatePath('Exchange')->setTemplate('add');
        $kind = $this->exchangeKind();
        $actorId = $this->actorId();
        if ($actorId === null || !Conversation::canSee($actorId, $kind)) {
            $this->Flash->error('Ehhez nincs jogosultságod.');

            return $this->redirect(['action' => 'index']);
        }
        if ($this->request->is('post')) {
            try {
                $result = Conversation::sendChannel(
                    $actorId,
                    $kind,
                    (string)$this->request->getData('subject'),
                    (string)$this->request->getData('body'),
                );
            } catch (\Throwable $exception) {
                Log::error('Üzenetváltás küldése: ' . $exception->getMessage());
                $this->Flash->error(
                    Conversation::missingTable($exception)
                        ? Conversation::UNAVAILABLE
                        : 'Az üzenet nem menthető.'
                );

                return null;
            }
            if ($result['error'] !== null) {
                $this->Flash->error($result['error']);
            } else {
                $text = 'Az üzenet elment.';
                if ($result['flashed'] === 0) {
                    $text .= ' ' . FlashNotice::missedNotice();
                } elseif ($result['flashed'] === 1) {
                    $text .= ' Kiment a telefonra.';
                } else {
                    $text .= ' ' . $result['flashed'] . ' telefonra kiment.';
                }
                $this->Flash->success($text);
                if ($result['thread_id'] > 0) {
                    return $this->redirect(['action' => 'view', $result['thread_id']]);
                }

                return $this->redirect(['action' => 'index']);
            }
        }
        $pageTitle = $kind === Conversation::KIND_PRESIDENTS
            ? 'Flash üzenet az elnököknek'
            : 'Flash üzenet mindenkinek';
        $this->set(compact('kind', 'pageTitle'));

        return null;
    }

    private function actorId(): ?string
    {
        $identity = $this->request->getAttribute('identity');
        if ($identity === null) {
            return null;
        }
        $id = $identity['id'] ?? null;
        if ($id === null || $id === '') {
            return null;
        }

        return (string)$id;
    }
}
