<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddKindToClubMessages extends BaseMigration
{
    public function up(): void
    {
        if (!$this->hasTable('club_messages')) {
            return;
        }
        $table = $this->table('club_messages');
        if ($table->hasColumn('kind')) {
            return;
        }
        $table
            ->addColumn('kind', 'string', [
                'limit' => 20,
                'null' => false,
                'default' => 'direct',
            ])
            ->update();
    }

    public function down(): void
    {
        if (!$this->hasTable('club_messages')) {
            return;
        }
        $table = $this->table('club_messages');
        if (!$table->hasColumn('kind')) {
            return;
        }
        $table->removeColumn('kind')->update();
    }
}
