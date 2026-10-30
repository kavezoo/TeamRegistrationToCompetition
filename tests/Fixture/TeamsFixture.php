<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamsFixture
 */
class TeamsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'visible' => 1,
                'pos' => 1,
                'created' => '2026-10-30 19:04:09',
                'modified' => '2026-10-30 19:04:09',
            ],
        ];
        parent::init();
    }
}
