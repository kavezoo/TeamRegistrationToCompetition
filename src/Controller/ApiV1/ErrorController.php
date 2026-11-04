<?php
declare(strict_types=1);

namespace App\Controller\ApiV1;

use App\Controller\ErrorController as BaseErrorController;
use Cake\Event\EventInterface;

/**
 * Az API hibái JSON-ben mennek ki.
 */
class ErrorController extends BaseErrorController
{
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setClassName('Json');
    }
}
