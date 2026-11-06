<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMembershipRequestToUsers extends BaseMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('profile_saved', 'boolean', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('membership_request', 'string', [
                'limit' => 20,
                'null' => true,
                'default' => null,
            ])
            ->update();
    }
}
