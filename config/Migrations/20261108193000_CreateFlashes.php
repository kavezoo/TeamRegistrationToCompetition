<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateFlashes extends BaseMigration
{
    public function up(): void
    {
        if ($this->hasTable('flashes')) {
            return;
        }

        $this->table('flashes')
            ->addColumn('user_id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('subject', 'string', [
                'limit' => 180,
                'null' => false,
            ])
            ->addColumn('body', 'text', [
                'null' => false,
            ])
            ->addColumn('target', 'string', [
                'limit' => 40,
                'null' => false,
                'default' => 'home',
            ])
            ->addColumn('target_id', 'string', [
                'limit' => 36,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('seen', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['user_id', 'seen'])
            ->create();
    }

    public function down(): void
    {
        if (!$this->hasTable('flashes')) {
            return;
        }
        $this->table('flashes')->drop()->save();
    }
}
