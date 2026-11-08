<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FlashesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('flashes');
        $this->setDisplayField('subject');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 36)
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 180)
            ->requirePresence('subject', 'create')
            ->notEmptyString('subject');

        $validator
            ->scalar('body')
            ->requirePresence('body', 'create')
            ->notEmptyString('body');

        $validator
            ->scalar('target')
            ->maxLength('target', 40)
            ->notEmptyString('target');

        $validator
            ->scalar('target_id')
            ->maxLength('target_id', 36)
            ->allowEmptyString('target_id');

        $validator
            ->dateTime('seen')
            ->allowEmptyDateTime('seen');

        return $validator;
    }
}
