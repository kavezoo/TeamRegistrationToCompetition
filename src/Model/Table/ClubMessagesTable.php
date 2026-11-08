<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ClubMessagesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('club_messages');
        $this->setDisplayField('subject');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
        $this->belongsTo('Senders', [
            'className' => 'Users',
            'foreignKey' => 'sender_id',
            'propertyName' => 'sender',
        ]);
        $this->belongsTo('Recipients', [
            'className' => 'Users',
            'foreignKey' => 'recipient_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('sender_id')
            ->maxLength('sender_id', 36)
            ->requirePresence('sender_id', 'create')
            ->notEmptyString('sender_id');

        $validator
            ->scalar('recipient_id')
            ->maxLength('recipient_id', 36)
            ->requirePresence('recipient_id', 'create')
            ->notEmptyString('recipient_id');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 180)
            ->requirePresence('subject', 'create')
            ->notEmptyString('subject');

        $validator
            ->scalar('body')
            ->maxLength('body', 2000)
            ->requirePresence('body', 'create')
            ->notEmptyString('body');

        $validator
            ->integer('thread_id')
            ->requirePresence('thread_id', 'create');

        $validator
            ->scalar('kind')
            ->maxLength('kind', 20)
            ->inList('kind', ['direct', 'all', 'presidents'])
            ->allowEmptyString('kind');

        return $validator;
    }
}
