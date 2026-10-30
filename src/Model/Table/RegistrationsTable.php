<?php
declare(strict_types=1);

namespace App\Model\Table;

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

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Teams', [
            'foreignKey' => 'team_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Competitions', [
            'foreignKey' => 'competition_id',
            'joinType' => 'INNER',
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
            ->notEmptyString('team_id');

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
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['team_id'], 'Teams'), ['errorField' => 'team_id']);
        $rules->add($rules->existsIn(['competition_id'], 'Competitions'), ['errorField' => 'competition_id']);

        return $rules;
    }
}
