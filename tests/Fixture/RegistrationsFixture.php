<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RegistrationsFixture
 */
class RegistrationsFixture extends TestFixture
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
                'user_id' => 'Lorem ipsum dolor sit amet',
                'team_id' => 1,
                'competition_id' => 1,
                'lunch_count' => 1,
                'extra_lunch_count' => 1,
                'competition_pipe_1' => 1,
                'competition_pipe_2' => 1,
                'competition_pipe_3' => 1,
                'extra_count_1' => 1,
                'extra_count_2' => 1,
                'extra_count_3' => 1,
                'visible' => 1,
                'pos' => 1,
                'created' => '2026-10-30 19:02:24',
                'modified' => '2026-10-30 19:02:24',
            ],
        ];
        parent::init();
    }
}
