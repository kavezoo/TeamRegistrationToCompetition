<?php
/**
 * Lista — megjelenítési kapcsolók ($show, plugin config + helyi felülírás)
 * rowCheckbox, rowId, name, visible, pos, created, modified, counts,
 * viewButton, editButton, deleteButton, rowDblClick
 *
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Team> $teams
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
    foreach ($teams as $team) {
        foreach (array_keys($team->toArray()) as $field) {
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
                  <div class="card shadow" aria-labelledby="teams-title">
                    <div class="card-header index-card-header">
                      <div class="index-card-header__title">
                        <strong id="teams-title"><?= __('Teams') ?></strong>
                        <small class="d-block"><?= __('All teams') ?></small>
                      </div>
                      <ul class="nav nav-tabs card-header-tabs index-card-header__tabs" id="indexTabs" role="tablist">
<?php if (!empty($show['relatedTables'])) : ?>
                        <li class="nav-item dropdown" role="presentation">
                          <a class="nav-link dropdown-toggle" href="#" id="tab-related-btn" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Related tables') ?></a>
                          <ul class="dropdown-menu" aria-labelledby="tab-related-btn">
                            <li><?= $this->Html->link(__('List Registrations'), ['controller' => 'Registrations', 'action' => 'index'], ['class' => 'dropdown-item']) ?></li>
                          </ul>
                        </li>
<?php endif; ?>

                      </ul>
                      <div class="index-card-header__actions">
                        <?= $this->Html->link(
                            $this->Icon->outline('plus') . ' ' . __('Add new team'),
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
<?php if (!empty($show['name'])) : ?>
                              <th class="string"><?= $this->Paginator->sort('name') ?></th>
<?php endif; ?>
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
<?php foreach ($teams as $team) :
    $dblClickUrl = $rowDblClick === 'none'
        ? null
        : $this->Url->build([
            'action' => $rowDblClick,
            $team->id,
            '?' => ['listPage' => $listPage],
        ]);
    $rowClass = (isset($lastRecordId) && (string)$lastRecordId === (string)$team->id)
        ? ' is-last-touched'
        : '';
    $rowAttrs = ' data-id="' . h((string)$team->id) . '"'
        . ' id="row-' . h((string)$team->id) . '"';
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
                                  <input type="checkbox" data-select-row value="<?= h((string)$team->id) ?>" aria-label="<?= h(__('Select row')) ?>">
                                  <span class="au-checkmark"></span>
                                </label>
                              </td>
<?php endif; ?>
<?php if (!empty($show['rowId'])) : ?>
                              <td class="integer id-col"><?= h((string)$team->id) ?></td>
<?php endif; ?>
<?php if (!empty($show['name'])) : ?>
                              <td class="string"><?= h($team->name) ?></td>
<?php endif; ?>
<?php if (!empty($show['counts'])) : ?>
<?php     foreach ($countColumns as $countKey => $countLabel) :
        $countVal = $team->get($countKey) ?? 0;
        $countIsZero = ((float)$countVal) == 0.0;
?>
                              <td class="count<?= $countIsZero ? ' count--zero' : '' ?>"><?= h((string)$countVal) ?></td>
<?php     endforeach; ?>
<?php endif; ?>
<?php if (!empty($show['visible'])) : ?>
                              <td class="boolean visible"><?= $this->Icon->boolean($team->visible) ?></td>
<?php endif; ?>
<?php if (!empty($show['pos'])) : ?>
                              <td class="integer pos"><?= $team->pos === null ? '' : h((string)$team->pos) ?></td>
<?php endif; ?>
<?php if (!empty($show['created']) || !empty($show['modified'])) : ?>
                              <td class="datetime created-modified">
<?php     if (!empty($show['created'])) : ?>
                                <span class="created"><?= $this->Input->formatDatetime($team->created) ?></span>
<?php     endif; ?>
<?php     if (!empty($show['created']) && !empty($show['modified'])) : ?>
                                <br>
<?php     endif; ?>
<?php     if (!empty($show['modified'])) : ?>
                                <span class="modified"><?= $this->Input->formatDatetime($team->modified) ?></span>
<?php     endif; ?>
                              </td>
<?php endif; ?>
<?php if (!empty($show['viewButton']) || !empty($show['editButton']) || !empty($show['deleteButton'])) : ?>
                              <td class="action">
                                <div class="table-data-feature">
<?php     if (!empty($show['viewButton'])) : ?>
                                  <?= $this->Action->view($team->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['editButton'])) : ?>
                                  <?= $this->Action->edit($team->id) ?>
<?php     endif; ?>
<?php     if (!empty($show['deleteButton'])) : ?>
                                  <?= $this->Action->delete($team->id, [], ['entity' => $team]) ?>
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
