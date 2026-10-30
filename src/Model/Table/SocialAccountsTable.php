<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SocialAccounts Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\SocialAccount newEmptyEntity()
 * @method \App\Model\Entity\SocialAccount newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SocialAccount> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SocialAccount get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SocialAccount findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SocialAccount patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SocialAccount> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SocialAccount|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SocialAccount saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SocialAccount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SocialAccount> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SocialAccountsTable extends Table
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

        $this->setTable('social_accounts');
        $this->setDisplayField('provider');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('provider')
            ->maxLength('provider', 255)
            ->requirePresence('provider', 'create')
            ->notEmptyString('provider');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->allowEmptyString('username');

        $validator
            ->scalar('reference')
            ->maxLength('reference', 255)
            ->requirePresence('reference', 'create')
            ->notEmptyString('reference');

        $validator
            ->scalar('avatar')
            ->allowEmptyString('avatar');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('link')
            ->maxLength('link', 255)
            ->requirePresence('link', 'create')
            ->notEmptyString('link');

        $validator
            ->scalar('token')
            ->maxLength('token', 500)
            ->requirePresence('token', 'create')
            ->notEmptyString('token');

        $validator
            ->scalar('token_secret')
            ->maxLength('token_secret', 500)
            ->allowEmptyString('token_secret');

        $validator
            ->dateTime('token_expires')
            ->allowEmptyDateTime('token_expires');

        $validator
            ->boolean('active')
            ->notEmptyString('active');

        $validator
            ->scalar('data')
            ->requirePresence('data', 'create')
            ->notEmptyString('data');

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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
