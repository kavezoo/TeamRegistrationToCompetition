<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\DateTime;

/**
 * A bejelentkezett felhasználó saját flash üzenetei.
 *
 * @property \App\Model\Table\FlashesTable $Flashes
 */
class FlashesController extends AppController
{
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $userId = $this->identityId();
        if ($userId === null) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        $flashes = $this->Flashes->find()
            ->select(['id', 'subject', 'body', 'target', 'target_id', 'seen', 'created'])
            ->where(['Flashes.user_id' => $userId])
            ->orderBy(['Flashes.created' => 'DESC', 'Flashes.id' => 'DESC'])
            ->limit(40)
            ->all();
        $this->set([
            'success' => true,
            'flashes' => $flashes,
        ]);
    }

    public function seen(string $id): void
    {
        $this->request->allowMethod(['post']);
        $userId = $this->identityId();
        if ($userId === null) {
            $this->respondForbidden('Ehhez nincs jogosultságod.');

            return;
        }
        try {
            $flash = $this->Flashes->get($id);
        } catch (RecordNotFoundException) {
            $this->respondNotFound('Az üzenet nem található.');

            return;
        }
        if ((string)$flash->user_id !== $userId) {
            $this->respondForbidden('Ehhez az üzenethez nincs jogosultságod.');

            return;
        }
        if ($flash->seen === null) {
            $flash->set('seen', DateTime::now());
            $this->Flashes->save($flash);
        }
        $this->set([
            'success' => true,
        ]);
    }
}
