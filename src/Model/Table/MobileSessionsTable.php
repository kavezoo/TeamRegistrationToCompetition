<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class MobileSessionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('mobile_sessions');
        $this->setPrimaryKey('id');
        $this->setDisplayField('id');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('user_id')
            ->maxLength('user_id', 36)
            ->notEmptyString('user_id');
        $validator
            ->scalar('refresh_token_hash')
            ->lengthBetween('refresh_token_hash', [64, 64])
            ->notEmptyString('refresh_token_hash');

        return $validator;
    }
}
