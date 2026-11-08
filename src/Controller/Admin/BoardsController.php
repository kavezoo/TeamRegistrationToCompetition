<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\ExchangeDeskTrait;
use App\Service\Conversation;

class BoardsController extends AppController
{
    use ExchangeDeskTrait;

    protected function exchangeKind(): string
    {
        return Conversation::KIND_PRESIDENTS;
    }
}
