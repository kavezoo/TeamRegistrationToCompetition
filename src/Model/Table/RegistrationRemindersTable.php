<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class RegistrationRemindersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('registration_reminders');
        $this->setPrimaryKey('id');
        $this->setEntityClass('App\Model\Entity\RegistrationReminder');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 36)
            ->notEmptyString('user_id');

        $validator
            ->integer('competition_id')
            ->notEmptyString('competition_id');

        $validator
            ->scalar('sender_id')
            ->maxLength('sender_id', 36)
            ->allowEmptyString('sender_id');

        $validator
            ->scalar('subject')
            ->maxLength('subject', 255)
            ->notEmptyString('subject');

        $validator
            ->scalar('message')
            ->notEmptyString('message');

        return $validator;
    }
}
