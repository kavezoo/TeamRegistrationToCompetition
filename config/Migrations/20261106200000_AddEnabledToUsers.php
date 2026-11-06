<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddEnabledToUsers extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('users');
        if ($table->hasColumn('enabled')) {
            return;
        }

        $table
            ->addColumn('enabled', 'boolean', [
                'default' => 1,
                'null' => false,
                'after' => 'active',
            ])
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('users');
        if (!$table->hasColumn('enabled')) {
            return;
        }

        $table->removeColumn('enabled')->update();
    }
}
