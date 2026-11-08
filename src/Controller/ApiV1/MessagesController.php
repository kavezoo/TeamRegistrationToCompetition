<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Service\Conversation;
use Cake\Log\Log;

/**
 * Beszélgetések a telefonnak: lista, megnyitás, új üzenet, válasz.
 */
class MessagesController extends AppController
{
    public function index(): void
    {
        $this->request->allowMethod(['get', 'post']);
        $userId = $this->identityId();
        if ($userId === null) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        try {
            if ($this->request->is('post')) {
                $recipientId = trim((string)$this->request->getData('recipient_id'));
                if ($recipientId === '') {
                    $this->response = $this->response->withStatus(422);
                    $this->set([
                        'success' => false,
                        'message' => 'A címzett kell.',
                    ]);

                    return;
                }
                $result = Conversation::send(
                    $userId,
                    $recipientId === '' ? [] : [$recipientId],
                    (string)$this->request->getData('subject'),
                    (string)$this->request->getData('body'),
                );
                if ($result['error'] !== null) {
                    $this->response = $this->response->withStatus(422);
                    $this->set([
                        'success' => false,
                        'message' => $result['error'],
                    ]);

                    return;
                }
                $this->set([
                    'success' => true,
                    'saved' => $result['saved'],
                    'flashed' => $result['flashed'],
                ]);

                return;
            }
            $this->set([
                'success' => true,
                'threads' => Conversation::threads($userId),
                'recipients' => Conversation::recipientPayload($userId),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Mobil üzenetek: ' . $exception->getMessage());
            $this->fail($exception);
        }
    }

    public function view(string $id): void
    {
        $this->request->allowMethod(['get']);
        $userId = $this->identityId();
        if ($userId === null) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        try {
            $thread = Conversation::open($userId, (int)$id);
        } catch (\Throwable $exception) {
            Log::error('Mobil üzenet: ' . $exception->getMessage());
            $this->fail($exception);

            return;
        }
        if ($thread === null) {
            $this->respondNotFound('Az üzenet nem található.');

            return;
        }
        $this->set([
            'success' => true,
            'thread' => $thread,
        ]);
    }

    public function reply(string $id): void
    {
        $this->request->allowMethod(['post']);
        $userId = $this->identityId();
        if ($userId === null) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        try {
            $result = Conversation::reply($userId, (int)$id, (string)$this->request->getData('body'));
        } catch (\Throwable $exception) {
            Log::error('Mobil válasz: ' . $exception->getMessage());
            $this->fail($exception);

            return;
        }
        if ($result['error'] !== null) {
            $status = $result['error'] === 'Az üzenet nem található.' ? 404 : 422;
            $this->response = $this->response->withStatus($status);
            $this->set([
                'success' => false,
                'message' => $result['error'],
            ]);

            return;
        }
        $this->set([
            'success' => true,
            'thread_id' => $result['thread_id'],
            'flashed' => $result['flashed'],
        ]);
    }

    private function fail(\Throwable $exception): void
    {
        $missing = Conversation::missingTable($exception);
        $this->response = $this->response->withStatus($missing ? 503 : 500);
        $this->set([
            'success' => false,
            'message' => $missing ? Conversation::UNAVAILABLE : 'Az üzeneteket nem sikerült betölteni.',
        ]);
    }
}
