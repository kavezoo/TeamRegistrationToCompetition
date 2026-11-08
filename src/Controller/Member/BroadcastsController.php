<?php
declare(strict_types=1);

namespace App\Controller\Member;

use App\Controller\ExchangeDeskTrait;
use App\Service\Conversation;

class BroadcastsController extends AppController
{
    use ExchangeDeskTrait;

    protected function exchangeKind(): string
    {
        return Conversation::KIND_ALL;
    }
}
