<?php
/**
 * Lista — megjelenítési kapcsolók ($show, plugin config + helyi felülírás)
 * rowCheckbox, rowId, name, visible, pos, created, modified, counts,
 * viewButton, editButton, deleteButton, rowDblClick
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Registration> $registrations
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
    foreach ($registrations as $registration) {
        foreach (array_keys($registration->toArray()) as $field) {
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
                  <div class="card shadow" aria-labelledby="registrations-title">
                    <div class="card-header index-card-header">
                      <div class="index-card-header__title">
                        <strong id="registrations-title"><?= __('Registrations') ?></strong>
                        <small class="d-block"><?= __('All registrations') ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs index-card-header__tabs" id="indexTabs" role="tablist">
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Teams'), ['controller' => 'Teams', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                            <li><?= $this->Html->link(__('List Competitions'), ['controller' => 'Competitions', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>

                      </ul>
                      <div class="index-card-header__actions">
                        <?= $this->Html->link(
                            $this->Icon->outline('plus') . ' ' . __('Add new registration'),
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
                              <th class="string"><?= $this->Paginator->sort('competition_id', __('Competition')) ?></th>
                              <th class="string"><?= $this->Paginator->sort('user_id', __('User')) ?></th>
                              <th class="string"><?= $this->Paginator->sort('team_id', __('Team')) ?></th>

                              <th class="integer"><?= $this->Paginator->sort('competition_pipe_1') ?></th>
                              <th class="integer"><?= $this->Paginator->sort('competition_pipe_2') ?></th>
                              <th class="integer"><?= $this->Paginator->sort('competition_pipe_3') ?></th>
                              <th class="integer"><?= $this->Paginator->sort('extra_count_1') ?></th>
                              <th class="integer"><?= $this->Paginator->sort('extra_count_2') ?></th>
                              <th class="integer"><?= $this->Paginator->sort('extra_count_3') ?></th>
<?php if (!empty($show['counts'])) : ?>
<?php     foreach ($countColumns as $countKey => $countLabel) : ?>
                              <th class="count"><?= $this->Paginator->sort($countKey, $countLabel) ?></th>
<?php     endforeach; ?>
<?php endif; ?>
<?php if (!empty($show['visible'])) : ?>
                              <th class="boolean"><?= $this->Paginator->sort('visible') ?></th>
<?php endif; ?>
<?php if (!empty($show['pos'])) : ?>
                              <th class="integer pos"><?= $this->Paginator->sort('pos') ?></th>
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
<?php foreach ($registrations as $registration) :
    $dblClickUrl = $rowDblClick === 'none'
        ? null
        : $this->Url->build([
            'action' => $rowDblClick,
            $registration->id,
            '?' => ['listPage' => $listPage],
        ]);
    $rowClass = (isset($lastRecordId) && (string)$lastRecordId === (string)$registration->id)
        ? ' is-last-touched'
        : '';
    $rowAttrs = ' data-id="' . h((string)$registration->id) . '"'
        . ' id="row-' . h((string)$registration->id) . '"';
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
                                  <input type="checkbox" data-select-row value="<?= h((string)$registration->id) ?>" aria-label="<?= h(__('Select row')) ?>">
                                  <span class="au-checkmark"></span>
                                </label>
                              </td>
<?php endif; ?>
<?php if (!empty($show['rowId'])) : ?>
                              <td class="integer id-col"><?= h((string)$registration->id) ?></td>
<?php endif; ?>
                              <td class="string">
                                <?= $registration->hasValue('competition')
                                    ? $this->Html->link(
                                        h((string)$registration->competition->name)
                                            . $this->Icon->outline('link-chain', 'record-link__icon'),
                                        ['controller' => 'Competitions', 'action' => 'view', $registration->competition->id],
                                        [
                                            'class' => 'text-decoration-none text-dark fw-bold record-link',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'data-bs-title' => __('View {0}: {1}', __('Competition'), (string)$registration->competition->name),
                                        ]
                                    )
                                    : '' ?>
                              </td>

                              <td class="string">
                                <?= $registration->hasValue('user')
                                    ? $this->Html->link(
                                        h(\App\Model\Entity\User::displayName($registration->user))
                                            . $this->Icon->outline('link-chain', 'record-link__icon'),
                                        ['controller' => 'Users', 'action' => 'view', $registration->user->id],
                                        [
                                            'class' => 'text-decoration-none text-dark fw-bold record-link',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'data-bs-title' => __('View {0}: {1}', __('User'), \App\Model\Entity\User::displayName($registration->user)),
                                        ]
                                    )
                                    : '' ?>
                              </td>

                              <td class="string">
                                <?= $registration->hasValue('team')
                                    ? $this->Html->link(
                                        h((string)$registration->team->name)
                                            . $this->Icon->outline('link-chain', 'record-link__icon'),
                                        ['controller' => 'Teams', 'action' => 'view', $registration->team->id],
                                        [
                                            'class' => 'text-decoration-none text-dark fw-bold record-link',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'data-bs-title' => __('View {0}: {1}', __('Team'), (string)$registration->team->name),
                                        ]
                                    )
                                    : '' ?>
                              </td>

                              <td class="integer"><?= $registration->competition_pipe_1 === null ? '' : $this->Number->format($registration->competition_pipe_1) ?></td>
                              <td class="integer"><?= $registration->competition_pipe_2 === null ? '' : $this->Number->format($registration->competition_pipe_2) ?></td>
                              <td class="integer"><?= $registration->competition_pipe_3 === null ? '' : $this->Number->format($registration->competition_pipe_3) ?></td>
                              <td class="integer"><?= $registration->extra_count_1 === null ? '' : $this->Number->format($registration->extra_count_1) ?></td>
                              <td class="integer"><?= $registration->extra_count_2 === null ? '' : $this->Number->format($registration->extra_count_2) ?></td>
                              <td class="integer"><?= $registration->extra_count_3 === null ? '' : $this->Number->format($registration->extra_count_3) ?></td>
<?php if (!empty($show['counts'])) : ?>
<?php     foreach ($countColumns as $countKey => $countLabel) :
        $countVal = $registration->get($countKey) ?? 0;
        $countIsZero = ((float)$countVal) == 0.0;
?>
                              <td class="count<?= $countIsZero ? ' count--zero' : '' ?>"><?= h((string)$countVal) ?></td>
<?php     endforeach; ?>
<?php endif; ?>
<?php if (!empty($show['visible'])) : ?>
                              <td class="boolean visible"><?= $this->Icon->boolean($registration->visible) ?></td>
<?php endif; ?>
<?php if (!empty($show['pos'])) : ?>
                              <td class="integer pos"><?= $registration->pos === null ? '' : h((string)$registration->pos) ?></td>
<?php endif; ?>
<?php if (!empty($show['created']) || !empty($show['modified'])) : ?>
                              <td class="datetime created-modified">
<?php     if (!empty($show['created'])) : ?>
                                <span class="created"><?= $this->Input->formatDatetime($registration->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($show['created']) && !empty($show['modified'])) : ?>
                                <br>
<?php     endif; ?>
<?php     if (!empty($show['modified'])) : ?>
                                <span class="modified"><?= $this->Input->formatDatetime($registration->modified) ?></span>
<?php     endif; ?>
                              </td>
<?php endif; ?>
<?php if (!empty($show['viewButton']) || !empty($show['editButton']) || !empty($show['deleteButton'])) : ?>
                              <td class="action">
                                <div class="table-data-feature">
<?php     if (!empty($show['viewButton'])) : ?>
                                  <?= $this->Action->view($registration->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['editButton'])) : ?>
                                  <?= $this->Action->edit($registration->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['deleteButton'])) : ?>
                                  <?= $this->Action->delete($registration->id, [], ['entity' => $registration]) ?>
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
