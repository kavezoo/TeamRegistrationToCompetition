<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\ORM\Query\SelectQuery;
use CakeDC\Users\Model\Behavior\AuthFinderBehavior;

/**
 * Belépéskor az aktív fiók mellett az enabled mező is kell.
 */
class LoginFinderBehavior extends AuthFinderBehavior
{
    public function findActive(SelectQuery $query)
    {
        $query = parent::findActive($query);
        if ($this->_table->getSchema()->hasColumn('enabled')) {
            $query->where([$this->_table->aliasField('enabled') => 1]);
        }

        return $query;
    }
}
