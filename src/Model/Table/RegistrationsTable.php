<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\Log\Log;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Registrations Model
 *
 * @property \CakeDC\Users\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TeamsTable&\Cake\ORM\Association\BelongsTo $Teams
 * @property \App\Model\Table\CompetitionsTable&\Cake\ORM\Association\BelongsTo $Competitions
 *
 * @method \App\Model\Entity\Registration newEmptyEntity()
 * @method \App\Model\Entity\Registration newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Registration> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Registration get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Registration findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Registration patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Registration> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Registration|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Registration saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Registration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Registration>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Registration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Registration> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Registration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Registration>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Registration>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Registration> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\CounterCacheBehavior
 */
class RegistrationsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('registrations');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // A CakeDC Users a Users aliast a plugin táblájára köti, ezért itt is az kell.
        $this->belongsTo('Users', [
            'className' => 'CakeDC/Users.Users',
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);
        // A select stratégia nem szűri ki a team_id nélküli nevezést.
        $this->belongsTo('Teams', [
            'foreignKey' => 'team_id',
            'joinType' => 'LEFT',
            'strategy' => 'select',
        ]);
        $this->belongsTo('Competitions', [
            'foreignKey' => 'competition_id',
            'joinType' => 'INNER',
        ]);

        $this->addBehavior('CounterCache', [
            'Competitions' => ['registration_count'],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 36)
            ->notEmptyString('user_id');

        $validator
            ->nonNegativeInteger('team_id')
            ->allowEmptyString('team_id');

        $validator
            ->nonNegativeInteger('competition_id')
            ->notEmptyString('competition_id');

        $validator
            ->nonNegativeInteger('lunch_count')
            ->notEmptyString('lunch_count');

        $validator
            ->nonNegativeInteger('extra_lunch_count')
            ->notEmptyString('extra_lunch_count');

        $validator
            ->nonNegativeInteger('competition_pipe_1')
            ->notEmptyString('competition_pipe_1');

        $validator
            ->nonNegativeInteger('competition_pipe_2')
            ->notEmptyString('competition_pipe_2');

        $validator
            ->nonNegativeInteger('competition_pipe_3')
            ->notEmptyString('competition_pipe_3');

        $validator
            ->nonNegativeInteger('extra_count_1')
            ->notEmptyString('extra_count_1');

        $validator
            ->nonNegativeInteger('extra_count_2')
            ->notEmptyString('extra_count_2');

        $validator
            ->nonNegativeInteger('extra_count_3')
            ->notEmptyString('extra_count_3');

        foreach ([
            'lunch_count',
            'extra_lunch_count',
            'competition_pipe_1',
            'competition_pipe_2',
            'competition_pipe_3',
            'extra_count_1',
            'extra_count_2',
            'extra_count_3',
        ] as $countField) {
            $validator->add($countField, 'maxCount', [
                'rule' => function ($value): bool {
                    return (int)$value <= 1000;
                },
                'message' => 'Legfeljebb 1000 lehet.',
            ]);
        }

        $validator
            ->scalar('comment')
            ->maxLength('comment', 1000)
            ->allowEmptyString('comment');

        $validator
            ->boolean('visible')
            ->notEmptyString('visible');

        $validator
            ->integer('pos')
            ->requirePresence('pos', 'create')
            ->notEmptyString('pos');

        return $validator;
    }

    /**
     * Nevezések névvel, alcsapattal és versennyel. A tagi lista és az API ezt használja.
     */
    public function findRoster(SelectQuery $query): SelectQuery
    {
        return $query
            ->contain(['Teams', 'Competitions'])
            ->orderBy(['Registrations.id' => 'ASC'])
            ->formatResults(fn ($results) => $this->attachRosterUsers($results));
    }

    /**
     * A nevet külön lekérdezés adja. A beágyazott felhasználó-csatolás
     * csak az aktuális klubelnök nevét hagyta meg.
     *
     * @param iterable<mixed> $results
     */
    private function attachRosterUsers(iterable $results): \Cake\Collection\CollectionInterface
    {
        $rows = [];
        $ids = [];
        foreach ($results as $row) {
            $rows[] = $row;
            $id = (string)($row->user_id ?? '');
            if ($id !== '') {
                $ids[$id] = $id;
            }
        }

        $users = $ids === [] ? [] : $this->rosterUsers($ids);
        foreach ($rows as $row) {
            if (!is_object($row) || !method_exists($row, 'set')) {
                continue;
            }
            $row->set('user', $users[(string)$row->user_id] ?? null);
            $row->setDirty('user', false);
        }

        return new \Cake\Collection\Collection($rows);
    }

    /**
     * @param array<string, string> $ids
     * @return array<string, \Cake\Datasource\EntityInterface>
     */
    private function rosterUsers(array $ids): array
    {
        $usersTable = $this->getAssociation('Users')->getTarget();
        $query = $usersTable->find()
            ->selectAllExcept($usersTable, ['password', 'token', 'api_token', 'secret', 'additional_data'])
            ->where([$usersTable->aliasField('id') . ' IN' => array_values($ids)]);
        try {
            $loaded = $query->contain(User::socialAvatarContain())->all();
        } catch (\Throwable $exception) {
            Log::error('A jelentkezők képe nem tölthető: ' . $exception->getMessage());
            $loaded = $usersTable->find()
                ->selectAllExcept($usersTable, ['password', 'token', 'api_token', 'secret', 'additional_data'])
                ->where([$usersTable->aliasField('id') . ' IN' => array_values($ids)])
                ->all();
        }

        $users = [];
        foreach ($loaded as $user) {
            User::publishAvatar($user);
            $users[(string)$user->get('id')] = $user;
        }

        return $users;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add(
            function ($entity, $options): bool {
                $teamId = $entity->get('team_id');
                if ($teamId === null || $teamId === '') {
                    return true;
                }

                return $options['repository']->getAssociation('Teams')->getTarget()->exists([
                    'Teams.id' => $teamId,
                ]);
            },
            'teamExists',
            [
                'errorField' => 'team_id',
                'message' => 'Az alcsapat nem található.',
            ],
        );
        $rules->add($rules->existsIn(['competition_id'], 'Competitions'), ['errorField' => 'competition_id']);

        return $rules;
    }
}
