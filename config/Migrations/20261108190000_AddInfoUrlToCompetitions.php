<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddInfoUrlToCompetitions extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('competitions');
        if (!$table->hasColumn('info_url')) {
            $table->addColumn('info_url', 'string', [
                'null' => true,
                'default' => null,
                'limit' => 2000,
                'after' => 'map_url',
            ]);
        }
        $table->update();
    }

    public function down(): void
    {
        $table = $this->table('competitions');
        if ($table->hasColumn('info_url')) {
            $table->removeColumn('info_url');
        }
        $table->update();
    }
}
