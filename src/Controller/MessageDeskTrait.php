<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Conversation;
use Cake\Http\Response;
use Cake\Log\Log;

/**
 * A webes üzenetlista, az új üzenet és a válasz. Elnök, tag és admin ugyanazt használja.
 */
trait MessageDeskTrait
{
    public function index(): ?Response
    {
        $actorId = $this->actorId();
        if ($actorId === null) {
            $this->Flash->error('Ehhez nincs jogosultságod.');

            return $this->redirect('/');
        }
        $selected = trim((string)$this->request->getQuery('user'));
        $people = [];
        $threads = [];
        try {
            $people = Conversation::people($actorId);
            if ($this->request->is('post')) {
                $subject = (string)$this->request->getData('subject');
                $body = (string)$this->request->getData('body');
                $userId = trim((string)$this->request->getData('user_id'));
                $selected = $userId;
                $broadcast = in_array((string)$this->request->getParam('prefix'), ['President', 'Admin'], true);
                if ($userId === '' && !$broadcast) {
                    $this->Flash->error('Válassz címzettet.');
                    $result = null;
                } else {
                    $result = Conversation::send($actorId, $userId === '' ? [] : [$userId], $subject, $body);
                }
                if ($result !== null && $result['error'] !== null) {
                    $this->Flash->error($result['error']);
                } elseif ($result !== null) {
                    $this->Flash->success($this->sentText($result));

                    return $this->redirect(['action' => 'index']);
                }
            }
            $threads = Conversation::threads($actorId);
        } catch (\Throwable $exception) {
            Log::error('Üzenetek: ' . $exception->getMessage());
            $this->Flash->error(
                Conversation::missingTable($exception)
                    ? Conversation::UNAVAILABLE
                    : 'Az üzeneteket nem sikerült betölteni.'
            );
        }
        $this->set(compact('people', 'threads', 'selected'));

        return null;
    }

    public function view(string $id = ''): ?Response
    {
        $actorId = $this->actorId();
        $threadId = (int)$id;
        if ($actorId === null || $threadId <= 0) {
            $this->Flash->error('Az üzenet nem található.');

            return $this->redirect(['action' => 'index']);
        }
        try {
            if ($this->request->is('post')) {
                $result = Conversation::reply($actorId, $threadId, (string)$this->request->getData('body'));
                if ($result['error'] !== null) {
                    $this->Flash->error($result['error']);
                } else {
                    $this->Flash->success(
                        $result['flashed']
                            ? 'A válasz kiment a telefonra.'
                            : 'A válasz elment. A címzett kikapcsolta a telefonos üzenetet.'
                    );

                    return $this->redirect(['action' => 'view', $threadId]);
                }
            }
            $thread = Conversation::open($actorId, $threadId);
        } catch (\Throwable $exception) {
            Log::error('Üzenet: ' . $exception->getMessage());
            $this->Flash->error(
                Conversation::missingTable($exception)
                    ? Conversation::UNAVAILABLE
                    : 'Az üzenetet nem sikerült megnyitni.'
            );

            return $this->redirect(['action' => 'index']);
        }
        if ($thread === null) {
            $this->Flash->error('Az üzenet nem található.');

            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('thread'));

        return null;
    }

    /**
     * @param array{error: ?string, matched: int, saved: int, flashed: int} $result
     */
    private function sentText(array $result): string
    {
        if ($result['saved'] === 1) {
            $text = 'Az üzenet elment.';
        } else {
            $text = $result['saved'] . ' üzenet elment.';
        }
        if ($result['flashed'] === 0) {
            return $text . ' A címzett kikapcsolta a telefonos üzenetet.';
        }
        if ($result['flashed'] === 1 && $result['saved'] === 1) {
            return $text . ' Kiment a telefonra.';
        }
        $text .= ' ' . $result['flashed'] . ' telefonra kiment.';
        $skipped = $result['saved'] - $result['flashed'];
        if ($skipped > 0) {
            $text .= ' ' . $skipped . ' tag kikapcsolta az egyedi üzenetet.';
        }

        return $text;
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
