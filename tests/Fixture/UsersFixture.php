<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'id' => '63b5e6d6-a061-42fa-9860-377bb16ff19b',
                'username' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'token' => 'Lorem ipsum dolor sit amet',
                'token_expires' => '2026-10-30 19:02:30',
                'api_token' => 'Lorem ipsum dolor sit amet',
                'activation_date' => '2026-10-30 19:02:30',
                'secret' => 'Lorem ipsum dolor sit amet',
                'secret_verified' => 1,
                'tos_date' => '2026-10-30 19:02:30',
                'active' => 1,
                'is_superuser' => 1,
                'role' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-10-30 19:02:30',
                'modified' => '2026-10-30 19:02:30',
                'additional_data' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'last_login' => '2026-10-30 19:02:30',
                'lockout_time' => '2026-10-30 19:02:30',
                'login_token' => 'Lorem ipsum dolor sit amet',
                'login_token_date' => '2026-10-30 19:02:30',
                'token_send_requested' => 1,
            ],
        ];
        parent::init();
    }
}
