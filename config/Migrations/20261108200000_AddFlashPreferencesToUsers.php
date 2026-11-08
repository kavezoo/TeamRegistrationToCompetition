<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFlashPreferencesToUsers extends BaseMigration
{
    /**
     * A telefonos üzenet fajtái. Az alapérték bekapcsolt, a régi tagok továbbra is megkapják.
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'flash_membership',
        'flash_signup',
        'flash_assignment',
        'flash_competition',
        'flash_reminder',
        'flash_applicants',
        'flash_president_entries',
        'flash_message',
    ];

    public function up(): void
    {
        $table = $this->table('users');
        $changed = false;
        foreach (self::COLUMNS as $column) {
            if ($table->hasColumn($column)) {
                continue;
            }
            $table->addColumn($column, 'boolean', [
                'default' => 1,
                'null' => false,
            ]);
            $changed = true;
        }
        if ($changed) {
            $table->update();
        }
    }

    public function down(): void
    {
        $table = $this->table('users');
        $changed = false;
        foreach (self::COLUMNS as $column) {
            if (!$table->hasColumn($column)) {
                continue;
            }
            $table->removeColumn($column);
            $changed = true;
        }
        if ($changed) {
            $table->update();
        }
    }
}
