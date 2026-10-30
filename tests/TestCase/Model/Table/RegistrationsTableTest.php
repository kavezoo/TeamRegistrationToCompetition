<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RegistrationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RegistrationsTable Test Case
 */
class RegistrationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RegistrationsTable
     */
    protected $Registrations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Registrations',
        'app.Users',
        'app.Teams',
        'app.Competitions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Registrations') ? [] : ['className' => RegistrationsTable::class];
        $this->Registrations = $this->getTableLocator()->get('Registrations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Registrations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RegistrationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RegistrationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
