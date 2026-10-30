<?php
/**
 * Lista — megjelenítési kapcsolók ($show, plugin config + helyi felülírás)
 * rowCheckbox, rowId, name, visible, pos, created, modified, counts,
 * viewButton, editButton, deleteButton, rowDblClick
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 * @var string|null $lastRecordId
 * @var int|null $lastRecordPage
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$show = Configure::read('JeffAdmin');
$showLocal = [];

$showLocal['index'] = [
//    'rowCheckbox'     => true,
//    'rowId'           => true,
//    'name'            => false,
//    'visible'         => false,
//    'pos'             => false,
//    'created'         => false,
//    'modified'        => false,
//    'counts'          => false,
//    'viewButton'      => false,
//    'editButton'      => false,
//    'deleteButton'    => false,
//    'relatedTables'   => false,
//    'rowDblClick'     => 'view',   // 'edit' | 'view' | 'none'
];

$show = array_merge($show['index'] ?? [], $showLocal['index']);

$rowDblClick = $show['rowDblClick'] ?? 'edit';
if (!in_array($rowDblClick, ['edit', 'view'], true)) {
    $rowDblClick = 'none';
}

$listPage = max(1, (int)$this->request->getQuery('page', 1));

$countColumns = [];
if (!empty($show['counts'])) {
    foreach ($users as $user) {
        foreach (array_keys($user->toArray()) as $field) {
            if (str_ends_with($field, '_count')) {
                $countColumns[$field] = Inflector::humanize(substr($field, 0, -6)) . ' count';
            }
        }
        break;
    }
}

$tableAttrs = '';
if (!empty($show['rowCheckbox'])) {
    $tableAttrs .= ' data-table-select';
}
if ($rowDblClick !== 'none') {
    $tableAttrs .= ' data-row-dblclick="' . h($rowDblClick) . '"';
}
?>
              <div class="row row-tight" style="margin-top: 16px;">
                <div class="col-md-12">
                  <div class="card shadow" aria-labelledby="users-title">
                    <div class="card-header index-card-header">
                      <div class="index-card-header__title">
                        <strong id="users-title"><?= __('Users') ?></strong>
                        <small class="d-block"><?= __('All users') ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs index-card-header__tabs" id="indexTabs" role="tablist">
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List FailedPasswordAttempts'), ['controller' => 'FailedPasswordAttempts', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Registrations'), ['controller' => 'Registrations', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List SocialAccounts'), ['controller' => 'SocialAccounts', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>

                      </ul>
                      <div class="index-card-header__actions">
                        <?= $this->Html->link(
                            $this->Icon->outline('plus') . ' ' . __('Add new user'),
                            ['action' => 'add'],
                            ['class' => 'btn btn-success', 'escape' => false]
                        ) ?>
                      </div>
                    </div>

                    <div class="card-body p-0">
                      <div class="table-responsive text-nowrap">
                        <table class="table table-data2 table-border table-hover table-striped table-custom-hover mb-0"<?= $tableAttrs ?>>
                          <thead>
                            <tr>
<?php if (!empty($show['rowCheckbox'])) : ?>
                              <th class="select-col">
                                <label class="au-checkbox">
                                  <input type="checkbox" data-select-all aria-label="<?= h(__('Select all')) ?>">
                                  <span class="au-checkmark"></span>
                                </label>
                              </th>
<?php endif; ?>
<?php if (!empty($show['rowId'])) : ?>
                              <th class="integer id-col"><?= $this->Paginator->sort('id') ?></th>
<?php endif; ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('username') ?></th>
*/ ?>
<?php /*
                              <th class="email"><?= $this->Paginator->sort('email') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('first_name') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('last_name') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('token_expires') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('api_token') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('activation_date') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('secret') ?></th>
*/ ?>
<?php /*
                              <th class="boolean"><?= $this->Paginator->sort('secret_verified') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('tos_date') ?></th>
*/ ?>
<?php /*
                              <th class="boolean"><?= $this->Paginator->sort('active') ?></th>
*/ ?>
<?php /*
                              <th class="boolean"><?= $this->Paginator->sort('is_superuser') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('role') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('last_login') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('lockout_time') ?></th>
*/ ?>
<?php /*
                              <th class="string"><?= $this->Paginator->sort('login_token') ?></th>
*/ ?>
<?php /*
                              <th class="datetime"><?= $this->Paginator->sort('login_token_date') ?></th>
*/ ?>
<?php /*
                              <th class="boolean"><?= $this->Paginator->sort('token_send_requested') ?></th>
*/ ?>
<?php if (!empty($show['counts'])) : ?>
<?php     foreach ($countColumns as $countKey => $countLabel) : ?>
                              <th class="count"><?= $this->Paginator->sort($countKey, $countLabel) ?></th>
<?php     endforeach; ?>
<?php endif; ?>
<?php if (!empty($show['created']) || !empty($show['modified'])) : ?>
                              <th class="datetime-meta">
<?php     if (!empty($show['created'])) : ?>
                                <?= $this->Paginator->sort('created') ?>
<?php     endif; ?>
<?php     if (!empty($show['created']) && !empty($show['modified'])) : ?>
                                <br>
<?php     endif; ?>
<?php     if (!empty($show['modified'])) : ?>
                                <?= $this->Paginator->sort('modified') ?>
<?php     endif; ?>
                              </th>
<?php endif; ?>
<?php if (!empty($show['viewButton']) || !empty($show['editButton']) || !empty($show['deleteButton'])) : ?>
                              <th class="action"><?= __('Actions') ?></th>
<?php endif; ?>
                            </tr>
                          </thead>
                          <tbody class="table-group-divider">
<?php foreach ($users as $user) :
    $dblClickUrl = $rowDblClick === 'none'
        ? null
        : $this->Url->build([
            'action' => $rowDblClick,
            $user->id,
            '?' => ['listPage' => $listPage],
        ]);
    $rowClass = (isset($lastRecordId) && (string)$lastRecordId === (string)$user->id)
        ? ' is-last-touched'
        : '';
    $rowAttrs = ' data-id="' . h((string)$user->id) . '"'
        . ' id="row-' . h((string)$user->id) . '"';
    if ($dblClickUrl) {
        $rowAttrs .= ' data-dblclick-url="' . h($dblClickUrl) . '"';
    }
    if ($rowClass !== '') {
        $rowAttrs .= ' class="' . trim($rowClass) . '"';
    }
?>
                            <tr<?= $rowAttrs ?>>
<?php if (!empty($show['rowCheckbox'])) : ?>
                              <td class="select-col">
                                <label class="au-checkbox">
                                  <input type="checkbox" data-select-row value="<?= h((string)$user->id) ?>" aria-label="<?= h(__('Select row')) ?>">
                                  <span class="au-checkmark"></span>
                                </label>
                              </td>
<?php endif; ?>
<?php if (!empty($show['rowId'])) : ?>
                              <td class="integer id-col"><?= h((string)$user->id) ?></td>
<?php endif; ?>
<?php /*
                              <td class="string"><?= h($user->username) ?></td>
*/ ?>
<?php /*
                              <td class="email"><?= $user->email === null || $user->email === '' ? '' : $this->Html->link(h($user->email), 'mailto:' . $user->email, ['class' => 'text-decoration-none']) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->first_name) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->last_name) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->token_expires) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->api_token) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->activation_date) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->secret) ?></td>
*/ ?>
<?php /*
                              <td class="boolean"><?= $this->Icon->boolean($user->secret_verified) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->tos_date) ?></td>
*/ ?>
<?php /*
                              <td class="boolean"><?= $this->Icon->boolean($user->active) ?></td>
*/ ?>
<?php /*
                              <td class="boolean"><?= $this->Icon->boolean($user->is_superuser) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->role) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->last_login) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->lockout_time) ?></td>
*/ ?>
<?php /*
                              <td class="string"><?= h($user->login_token) ?></td>
*/ ?>
<?php /*
                              <td class="datetime"><?= $this->Input->formatDatetime($user->login_token_date) ?></td>
*/ ?>
<?php /*
                              <td class="boolean"><?= $this->Icon->boolean($user->token_send_requested) ?></td>
*/ ?>
<?php if (!empty($show['counts'])) : ?>
<?php     foreach ($countColumns as $countKey => $countLabel) :
        $countVal = $user->get($countKey) ?? 0;
        $countIsZero = ((float)$countVal) == 0.0;
?>
                              <td class="count<?= $countIsZero ? ' count--zero' : '' ?>"><?= h((string)$countVal) ?></td>
<?php     endforeach; ?>
<?php endif; ?>
<?php if (!empty($show['created']) || !empty($show['modified'])) : ?>
                              <td class="datetime created-modified">
<?php     if (!empty($show['created'])) : ?>
                                <span class="created"><?= $this->Input->formatDatetime($user->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($show['created']) && !empty($show['modified'])) : ?>
                                <br>
<?php     endif; ?>
<?php     if (!empty($show['modified'])) : ?>
                                <span class="modified"><?= $this->Input->formatDatetime($user->modified) ?></span>
<?php     endif; ?>
                              </td>
<?php endif; ?>
<?php if (!empty($show['viewButton']) || !empty($show['editButton']) || !empty($show['deleteButton'])) : ?>
                              <td class="action">
                                <div class="table-data-feature">
<?php     if (!empty($show['viewButton'])) : ?>
                                  <?= $this->Action->view($user->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['editButton'])) : ?>
                                  <?= $this->Action->edit($user->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['deleteButton'])) : ?>
                                  <?= $this->Action->delete($user->id, [], ['entity' => $user]) ?>
<?php     endif; ?>
                                </div>
                              </td>
<?php endif; ?>
                            </tr>
<?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <div class="card-footer border-top d-flex align-items-center justify-content-between">
                      <?= $this->element('JeffAdmin.pagination') ?>
                    </div>
                  </div>
                </div>
              </div>
