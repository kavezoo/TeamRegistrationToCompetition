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
                $this->sendNew($userId);

                return;
            }
            $threads = [];
            $notice = null;
            try {
                $threads = Conversation::threads($userId);
            } catch (\Throwable $exception) {
                Log::error('Mobil beszélgetések: ' . $exception->getMessage());
                $notice = Conversation::missingTable($exception)
                    ? Conversation::UNAVAILABLE
                    : 'A korábbi üzeneteket nem sikerült betölteni.';
            }
            $this->set([
                'success' => true,
                'threads' => $threads,
                'recipients' => Conversation::recipientPayload($userId),
                'notice' => $notice,
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

    /**
     * Mindenkinek egy közös üzenet, vagy a kijelölt tagoknak külön.
     * A régi alkalmazás egyetlen recipient_id mezőt küld.
     */
    private function sendNew(string $userId): void
    {
        $subject = (string)$this->request->getData('subject');
        $body = (string)$this->request->getData('body');
        $audience = trim((string)$this->request->getData('audience'));
        if ($audience === Conversation::KIND_ALL) {
            $result = Conversation::sendChannel($userId, Conversation::KIND_ALL, $subject, $body);
            if ($result['error'] === Conversation::UNAVAILABLE) {
                $result = Conversation::send($userId, [], $subject, $body);
            }
        } else {
            $recipientIds = [];
            $posted = $this->request->getData('recipient_ids');
            if (is_array($posted)) {
                foreach ($posted as $id) {
                    $id = trim((string)$id);
                    if ($id !== '') {
                        $recipientIds[$id] = $id;
                    }
                }
            }
            $single = trim((string)$this->request->getData('recipient_id'));
            if ($single !== '') {
                $recipientIds[$single] = $single;
            }
            if ($recipientIds === []) {
                $this->response = $this->response->withStatus(422);
                $this->set([
                    'success' => false,
                    'message' => 'Válassz legalább egy tagot, vagy küldd mindenkinek.',
                ]);

                return;
            }
            $result = Conversation::send($userId, array_values($recipientIds), $subject, $body);
        }
        if ($result['error'] !== null && $result['saved'] === 0) {
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
            'message' => $result['error'],
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
