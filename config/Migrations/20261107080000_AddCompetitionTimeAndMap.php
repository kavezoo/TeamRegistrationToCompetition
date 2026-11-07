<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCompetitionTimeAndMap extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('competitions');
        if (!$table->hasColumn('date_competition')) {
            $table->addColumn('date_competition', 'datetime', [
                'null' => true,
                'default' => null,
                'after' => 'comment',
            ]);
        }
        if (!$table->hasColumn('map_url')) {
            $table->addColumn('map_url', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 2000,
                'after' => 'date_deadline',
            ]);
        }
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('competitions');
        if ($table->hasColumn('date_competition')) {
            $table->removeColumn('date_competition');
        }
        if ($table->hasColumn('map_url')) {
            $table->removeColumn('map_url');
        }
        $table->update();
    }
}
