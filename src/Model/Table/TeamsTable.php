<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Teams Model
 *
 * @property \App\Model\Table\RegistrationsTable&\Cake\ORM\Association\HasMany $Registrations
 *
 * @method \App\Model\Entity\Team newEmptyEntity()
 * @method \App\Model\Entity\Team newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Team> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Team get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Team findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Team patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Team> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Team|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Team saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Team>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Team>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Team>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Team> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Team>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Team>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Team>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Team> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TeamsTable extends Table
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

        $this->setTable('teams');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Registrations', [
            'foreignKey' => 'team_id',
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
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->boolean('visible')
            ->notEmptyString('visible');

        $validator
            ->integer('pos')
            ->requirePresence('pos', 'create')
            ->notEmptyString('pos');

        return $validator;
    }
}
