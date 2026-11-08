<?php
declare(strict_types=1);

namespace App\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
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
     * A naptár magyar szövege (2026. 11. 07.) ne essen szét mentéskor.
     *
     * @param \Cake\Event\EventInterface $event
     * @param \ArrayObject<string, mixed> $data
     * @param \ArrayObject<string, mixed> $options
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['date_from', 'date_deadline', 'date_competition'] as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                continue;
            }
            $iso = $this->hungarianDate($data[$field]);
            if ($iso !== null) {
                $data[$field] = $iso;
            }
        }
    }

    /**
     * 2026. 11. 07. vagy 2026. 11. 15. 02:00 → 2026-11-07 / 2026-11-15 02:00:00.
     */
    private function hungarianDate(string $value): ?string
    {
        $value = trim($value);
        if (preg_match(
            '/\A(\d{4})\.\s*(\d{1,2})\.\s*(\d{1,2})\.?(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?\z/',
            $value,
            $match,
        ) !== 1) {
            return null;
        }
        $date = sprintf('%04d-%02d-%02d', (int)$match[1], (int)$match[2], (int)$match[3]);
        if (!isset($match[4]) || $match[4] === '') {
            return $date;
        }

        return $date . sprintf(' %02d:%02d:%02d', (int)$match[4], (int)$match[5], (int)($match[6] ?? 0));
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
            ->dateTime('date_competition')
            ->allowEmptyDateTime('date_competition');

        $validator
            ->date('date_from')
            ->requirePresence('date_from', 'create')
            ->notEmptyDate('date_from');

        $validator
            ->date('date_deadline')
            ->requirePresence('date_deadline', 'create')
            ->notEmptyDate('date_deadline');

        $validator
            ->scalar('map_url')
            ->maxLength('map_url', 2000)
            ->allowEmptyString('map_url')
            ->add('map_url', 'httpUrl', [
                'rule' => function ($value): bool {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return preg_match('#\Ahttps?://#i', (string)$value) === 1;
                },
                'message' => 'A térkép címe http vagy https legyen.',
            ]);

        $validator
            ->scalar('info_url')
            ->maxLength('info_url', 2000)
            ->allowEmptyString('info_url')
            ->add('info_url', 'httpUrl', [
                'rule' => function ($value): bool {
                    if ($value === null || $value === '') {
                        return true;
                    }

                    return preg_match('#\Ahttps?://#i', (string)$value) === 1;
                },
                'message' => 'A leírás linkje http vagy https legyen.',
            ]);

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
