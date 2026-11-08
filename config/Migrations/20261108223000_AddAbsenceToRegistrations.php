<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddAbsenceToRegistrations extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('registrations');
        if (!$table->hasColumn('absent')) {
            $table->addColumn('absent', 'boolean', [
                'null' => false,
                'default' => false,
                'after' => 'comment',
            ]);
        }
        if (!$table->hasColumn('absent_reason')) {
            $table->addColumn('absent_reason', 'string', [
                'limit' => 1000,
                'null' => true,
                'default' => null,
                'after' => 'absent',
            ]);
        }
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('registrations');
        if ($table->hasColumn('absent_reason')) {
            $table->removeColumn('absent_reason');
        }
        if ($table->hasColumn('absent')) {
            $table->removeColumn('absent');
        }
        $table->update();
    }
}
