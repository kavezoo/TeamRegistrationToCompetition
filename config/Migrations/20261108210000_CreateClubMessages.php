<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateClubMessages extends BaseMigration
{
    public function up(): void
    {
        if ($this->hasTable('club_messages')) {
            return;
        }

        $charset = 'utf8mb4';
        $collation = 'utf8mb4_unicode_ci';
        $row = $this->fetchRow(
            "SELECT CHARACTER_SET_NAME AS charset_name, COLLATION_NAME AS collation_name
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'id'"
        );
        if (is_array($row)) {
            $foundCharset = (string)($row['charset_name'] ?? '');
            $foundCollation = (string)($row['collation_name'] ?? '');
            if (preg_match('/^[A-Za-z0-9_]+$/', $foundCharset) === 1) {
                $charset = $foundCharset;
            }
            if (preg_match('/^[A-Za-z0-9_]+$/', $foundCollation) === 1) {
                $collation = $foundCollation;
            }
        }

        $this->execute(
            "CREATE TABLE `club_messages` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `sender_id` CHAR(36) CHARACTER SET {$charset} COLLATE {$collation} NOT NULL,
                `recipient_id` CHAR(36) CHARACTER SET {$charset} COLLATE {$collation} NOT NULL,
                `subject` VARCHAR(180) NOT NULL,
                `body` TEXT NOT NULL,
                `thread_id` INT UNSIGNED NOT NULL DEFAULT 0,
                `created` DATETIME NOT NULL,
                `modified` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `club_messages_sender` (`sender_id`, `created`),
                KEY `club_messages_recipient` (`recipient_id`, `created`),
                KEY `club_messages_thread` (`thread_id`, `id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collation}"
        );
    }

    public function down(): void
    {
        if (!$this->hasTable('club_messages')) {
            return;
        }
        $this->table('club_messages')->drop()->save();
    }
}
