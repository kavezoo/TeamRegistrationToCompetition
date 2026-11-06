<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class RegistrationCommentAndNullableTeam extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('registrations');
        if (!$table->hasColumn('comment')) {
            $table->addColumn('comment', 'string', [
                'limit' => 1000,
                'null' => true,
                'default' => null,
                'after' => 'extra_count_3',
            ]);
        }
        $table
            ->changeColumn('team_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => true,
            ])
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('registrations');
        if ($table->hasColumn('comment')) {
            $table->removeColumn('comment');
        }
        $table
            ->changeColumn('team_id', 'integer', [
                'null' => false,
                'signed' => true,
            ])
            ->update();
    }
}
