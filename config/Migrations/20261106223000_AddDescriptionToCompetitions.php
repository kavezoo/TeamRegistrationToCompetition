<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Adapter\MysqlAdapter;

class AddDescriptionToCompetitions extends BaseMigration
{
    public function up(): void
    {
        $table = $this->table('competitions');
        if ($table->hasColumn('description')) {
            return;
        }
        $table
            ->addColumn('description', 'text', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::TEXT_LONG,
                'after' => 'date_deadline',
            ])
            ->update();
    }

    public function down(): void
    {
        $table = $this->table('competitions');
        if (!$table->hasColumn('description')) {
            return;
        }
        $table->removeColumn('description')->update();
    }
}
