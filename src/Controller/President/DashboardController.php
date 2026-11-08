<?php
declare(strict_types=1);

namespace App\Controller\President;

use App\Controller\OwnProfileTrait;
use App\Model\Entity\User;
use Cake\Http\Response;

class DashboardController extends AppController
{
    use OwnProfileTrait;

    /**
     * Jelentkező nélkül a nevezők a kezdőlap. Elbírálnivaló esetén a jelentkezők.
     */
    public function home(): Response
    {
        $users = $this->fetchTable('Users');
        $pending = $users->find()
            ->where(User::openApplicantConditions($users))
            ->where([$users->aliasField('profile_saved') => true])
            ->count();
        if ($pending > 0) {
            return $this->redirect(['controller' => 'Applicants', 'action' => 'index']);
        }

        return $this->redirect(['controller' => 'Groups', 'action' => 'index']);
    }
}
