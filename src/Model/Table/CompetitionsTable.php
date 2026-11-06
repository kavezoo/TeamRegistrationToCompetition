<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Competitions Model
 *
 * @property \App\Model\Table\RegistrationsTable&\Cake\ORM\Association\HasMany $Registrations
 *
 * @method \App\Model\Entity\Competition newEmptyEntity()
 * @method \App\Model\Entity\Competition newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Competition> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Competition get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Competition findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Competition patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Competition> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Competition|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Competition saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Competition>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Competition>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Competition>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Competition> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Competition>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Competition>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Competition>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Competition> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CompetitionsTable extends Table
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

        $this->setTable('competitions');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Registrations', [
            'foreignKey' => 'competition_id',
            // 'dependent' => true, // Kaszkád törlés: a kapcsolt rekordok is törlődnek (kapcsold be, ha kell)
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
            ->scalar('name')
            ->maxLength('name', 200)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('lunch_title')
            ->maxLength('lunch_title', 200)
            ->allowEmptyString('lunch_title');

        $validator
            ->scalar('special_lunch_title')
            ->maxLength('special_lunch_title', 200)
            ->allowEmptyString('special_lunch_title');

        $validator
            ->scalar('competition_pipe_title_1')
            ->maxLength('competition_pipe_title_1', 200)
            ->allowEmptyString('competition_pipe_title_1');

        $validator
            ->scalar('competition_pipe_title_2')
            ->maxLength('competition_pipe_title_2', 200)
            ->allowEmptyString('competition_pipe_title_2');

        $validator
            ->scalar('competition_pipe_title_3')
            ->maxLength('competition_pipe_title_3', 200)
            ->allowEmptyString('competition_pipe_title_3');

        $validator
            ->scalar('extra_title_1')
            ->maxLength('extra_title_1', 200)
            ->allowEmptyString('extra_title_1');

        $validator
            ->scalar('extra_title_2')
            ->maxLength('extra_title_2', 200)
            ->allowEmptyString('extra_title_2');

        $validator
            ->scalar('extra_title_3')
            ->maxLength('extra_title_3', 200)
            ->allowEmptyString('extra_title_3');

        $validator
            ->scalar('comment')
            ->maxLength('comment', 200)
            ->requirePresence('comment', 'create')
            ->notEmptyString('comment');

        $validator
            ->date('date_from')
            ->requirePresence('date_from', 'create')
            ->notEmptyDate('date_from');

        $validator
            ->date('date_deadline')
            ->requirePresence('date_deadline', 'create')
            ->notEmptyDate('date_deadline');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->nonNegativeInteger('registration_count')
            ->allowEmptyString('registration_count');

        $validator
            ->boolean('visible')
            ->notEmptyString('visible');

        $validator
            ->integer('pos')
            ->notEmptyString('pos');

        return $validator;
    }
}
