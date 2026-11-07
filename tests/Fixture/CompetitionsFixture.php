<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CompetitionsFixture
 */
class CompetitionsFixture extends TestFixture
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
                'lunch_title' => 'Lorem ipsum dolor sit amet',
                'special_lunch_title' => 'Lorem ipsum dolor sit amet',
                'competition_pipe_title_1' => 'Lorem ipsum dolor sit amet',
                'competition_pipe_title_2' => 'Lorem ipsum dolor sit amet',
                'competition_pipe_title_3' => 'Lorem ipsum dolor sit amet',
                'extra_title_1' => 'Lorem ipsum dolor sit amet',
                'extra_title_2' => 'Lorem ipsum dolor sit amet',
                'extra_title_3' => 'Lorem ipsum dolor sit amet',
                'comment' => 'Lorem ipsum dolor sit amet',
                'date_competition' => '2026-10-30 09:00:00',
                'date_from' => '2026-10-30',
                'date_deadline' => '2026-10-30',
                'map_url' => null,
                'description' => null,
                'registration_count' => 1,
                'visible' => 1,
                'pos' => 1,
                'created' => '2026-10-30 19:10:50',
                'modified' => '2026-10-30 19:10:50',
            ],
        ];
        parent::init();
    }
}
