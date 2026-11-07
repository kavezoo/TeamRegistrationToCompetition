<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateRegistrationReminders extends BaseMigration
{
    public function up(): void
    {
        if ($this->hasTable('registration_reminders')) {
            return;
        }

        $this->table('registration_reminders')
            ->addColumn('user_id', 'char', [
                'limit' => 36,
                'null' => false,
            ])
            ->addColumn('competition_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('sender_id', 'char', [
                'limit' => 36,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('subject', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addIndex(['user_id'])
            ->addIndex(['competition_id'])
            ->create();
    }

    public function down(): void
    {
        if (!$this->hasTable('registration_reminders')) {
            return;
        }
        $this->table('registration_reminders')->drop()->save();
    }
}
