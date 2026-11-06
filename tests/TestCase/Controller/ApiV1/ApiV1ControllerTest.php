<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\ApiV1;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Az /api-v1 prefix CRUD-ja és a CSRF kihagyása.
 */
class ApiV1ControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Competitions',
        'app.Teams',
        'app.Registrations',
    ];

    public function testGuestGetsJsonUnauthorized(): void
    {
        $this->get('/api-v1/competitions');
        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $body = $this->decode();
        $this->assertFalse($body['success']);
    }

    public function testApiPostSkipsCsrf(): void
    {
        $this->login();
        $this->post('/api-v1/competitions', [
            'name' => 'Tavaszi verseny',
            'comment' => 'Megjegyzés',
            'date_from' => '2026-11-01',
            'date_deadline' => '2026-11-10',
            'visible' => 1,
            'pos' => 2,
        ]);
        $this->assertResponseCode(201);
        $body = $this->decode();
        $this->assertTrue($body['success']);
        $this->assertSame('Tavaszi verseny', $body['competition']['name']);
    }

    public function testWebPostStillRequiresCsrf(): void
    {
        $this->post('/admin/competitions/add', [
            'name' => 'Nem mehet át',
        ]);
        $this->assertResponseCode(403);
    }

    public function testTeamsAndRegistrationsCrud(): void
    {
        $this->login();

        $this->post('/api-v1/teams', [
            'name' => 'Alfa',
            'visible' => 1,
            'pos' => 3,
        ]);
        $this->assertResponseCode(201);
        $teamId = $this->decode()['team']['id'];

        $this->get('/api-v1/teams/' . $teamId);
        $this->assertResponseOk();
        $this->assertSame('Alfa', $this->decode()['team']['name']);

        $this->patch('/api-v1/teams/' . $teamId, [
            'name' => 'Béta',
        ]);
        $this->assertResponseOk();
        $this->assertSame('Béta', $this->decode()['team']['name']);

        $this->post('/api-v1/registrations', [
            'user_id' => '63b5e6d6-a061-42fa-9860-377bb16ff19b',
            'team_id' => $teamId,
            'competition_id' => 1,
            'lunch_count' => 1,
            'extra_lunch_count' => 0,
            'competition_pipe_1' => 0,
            'competition_pipe_2' => 0,
            'competition_pipe_3' => 0,
            'extra_count_1' => 0,
            'extra_count_2' => 0,
            'extra_count_3' => 0,
            'visible' => 1,
            'pos' => 1,
        ]);
        $this->assertResponseCode(201);
        $registration = $this->decode()['registration'];
        $this->assertSame('Béta', $registration['team']['name']);
        $this->assertArrayNotHasKey('password', $registration['user']);
        $this->assertArrayNotHasKey('api_token', $registration['user']);

        $this->delete('/api-v1/registrations/' . $registration['id']);
        $this->assertResponseOk();

        $this->delete('/api-v1/teams/' . $teamId);
        $this->assertResponseOk();
    }

    public function testCompetitionWithRegistrationsCannotBeDeleted(): void
    {
        $this->login();
        $this->delete('/api-v1/competitions/1');
        $this->assertResponseCode(409);
    }

    public function testMemberRosterAndMineExposeNameAndTeamOnly(): void
    {
        $this->login(['role' => 'member']);

        $this->get('/api-v1/registrations/roster');
        $this->assertResponseOk();
        $roster = $this->decode();
        $this->assertTrue($roster['success']);
        $this->assertIsArray($roster['registrations']);
        foreach ($roster['registrations'] as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('team', $row);
            $this->assertArrayHasKey('competition', $row);
            $this->assertArrayNotHasKey('email', $row);
            $this->assertArrayNotHasKey('user', $row);
        }

        $this->get('/api-v1/registrations/mine');
        $this->assertResponseOk();
        $mine = $this->decode();
        $this->assertTrue($mine['success']);
        $this->assertIsArray($mine['registrations']);
    }

    public function testNewUserCanEditOwnNameButNotTheApi(): void
    {
        $this->login(['role' => 'new']);

        $this->get('/api-v1/competitions');
        $this->assertResponseCode(403);

        $this->patch('/api-v1/users/profile', [
            'last_name' => 'Kovács',
            'first_name' => 'Anna',
            'role' => 'admin',
        ]);
        $this->assertResponseOk();

        $fresh = $this->getTableLocator()->get('Users')->get('63b5e6d6-a061-42fa-9860-377bb16ff19b');
        $this->assertSame('Kovács', $fresh->last_name);
        $this->assertSame('Anna', $fresh->first_name);
        $this->assertSame('Lorem ipsum dolor sit amet', $fresh->role);
    }

    public function testProfileUpdatesNameOnly(): void
    {
        $users = $this->getTableLocator()->get('Users');
        $user = $users->get('63b5e6d6-a061-42fa-9860-377bb16ff19b');
        $user->email = 'anna@example.com';
        $password = $user->password;
        $role = $user->role;
        $users->saveOrFail($user, ['validate' => false, 'checkRules' => false]);

        $this->login();
        $this->patch('/api-v1/users/profile', [
            'first_name' => 'Anna',
            'last_name' => 'Kovács',
            'email' => 'hack@example.com',
            'password' => 'new-secret',
            'role' => 'admin',
        ]);
        $this->assertResponseOk();
        $body = $this->decode();
        $this->assertSame('Anna', $body['user']['first_name']);
        $this->assertSame('Kovács', $body['user']['last_name']);
        $this->assertSame('anna@example.com', $body['user']['email']);
        $this->assertArrayNotHasKey('password', $body['user']);
        $this->assertArrayNotHasKey('api_token', $body['user']);

        $fresh = $users->get('63b5e6d6-a061-42fa-9860-377bb16ff19b');
        $this->assertSame($password, $fresh->password);
        $this->assertSame($role, $fresh->role);
        $this->assertSame('anna@example.com', $fresh->email);
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function login(array $extra = []): void
    {
        $this->session([
            'Auth' => $extra + [
                'id' => '63b5e6d6-a061-42fa-9860-377bb16ff19b',
                'username' => 'anna',
                'email' => 'anna@example.com',
                'role' => 'admin',
                'active' => true,
                'is_superuser' => false,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(): array
    {
        $decoded = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($decoded);

        return $decoded;
    }
}
