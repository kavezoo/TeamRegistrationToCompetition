<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition|null $competition
 * @var array<int, \App\Model\Entity\User> $members
 * @var \Cake\Datasource\Paging\PaginatedInterface $membersPage
 * @var array<string, string|null> $entries
 * @var array<string, string> $recentSent
 * @var int $missing
 * @var string|null $lastRecordId
 */
use App\Model\Entity\User;

$this->assign('title', 'Tagok');
$this->Html->script('member-remind', ['block' => true]);

$canRemind = $competition !== null && $competition->visible && !$competition->registrationClosed();
$page = (int)$this->request->getQuery('page');
$pageQuery = $page > 1 ? ['page' => $page] : [];
$csrf = (string)$this->request->getAttribute('csrfToken');
?>
<div id="member-remind" data-csrf="<?= h($csrf) ?>" hidden></div>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="d-block">Aktív tagok</strong>
                <div class="mt-2">
                    <?= $this->Html->link(
                        'Üzenet minden tagnak',
                        ['controller' => 'Messages', 'action' => 'index'],
                        ['class' => 'btn btn-sm btn-primary'],
                    ) ?>
                </div>
                <?php if ($canRemind && $missing > 0) : ?>
                    <div class="mt-2">
                        <?= $this->Form->postLink(
                            'Emlékeztető mindenkinek, aki még nem jelentkezett (' . $missing . ')',
                            ['action' => 'remindMissing', '?' => $pageQuery],
                            [
                                'class' => 'btn btn-sm btn-primary text-wrap',
                                'data' => ['competition_id' => $competition->id],
                                'confirm' => 'Elküldöd az emlékeztetőt mindenkinek, aki még nem jelentkezett?',
                                'data-swal-title' => 'E-mail küldése',
                                'data-swal-text' => 'Elküldöd az emlékeztetőt mindenkinek, aki még nem jelentkezett?',
                                'data-swal-confirm' => __('Yes'),
                                'data-swal-cancel' => __('No'),
                            ]
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($canRemind) : ?>
                    <p>A jelentkezés és az emlékeztető ehhez a versenyhez tartozik: <strong><?= h($competition->name) ?></strong></p>
                <?php elseif ($competition === null) : ?>
                    <p>Nincs nyitott verseny, ezért emlékeztetőt nem lehet küldeni.</p>
                <?php endif; ?>

                <?php if ($members === []) : ?>
                    <p class="mb-0">Nincs aktív tag.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-data2 table-border table-hover table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Vegyes+</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                <?php foreach ($members as $member) : ?>
                                    <?php
                                    $memberId = (string)$member->id;
                                    $joined = array_key_exists($memberId, $entries);
                                    $teamName = $joined ? $entries[$memberId] : null;
                                    $sentAt = $recentSent[$memberId] ?? null;
                                    $isLast = isset($lastRecordId) && (string)$lastRecordId === $memberId;
                                    ?>
                                    <tr<?= $isLast ? ' class="is-last-touched"' : '' ?> id="row-<?= h($memberId) ?>" data-id="<?= h($memberId) ?>">
                                        <td>
                                            <?= $this->element('user_name', [
                                                'user' => $member,
                                                'bold' => true,
                                                'line' => (string)$member->email,
                                                'mark' => (string)$member->get('role') === User::ROLE_PRESIDENT ? 'Elnök' : '',
                                            ]) ?>
                                        </td>
                                        <td class="js-remind-action text-break">
                                            <?php if ($joined) : ?>
                                                <?= h($teamName === null || $teamName === '' ? 'Jelentkezett a versenyre és besorolásra vár' : $teamName) ?>
                                            <?php elseif ($sentAt !== null) : ?>
                                                <?= h($sentAt) ?>
                                            <?php elseif ($canRemind && trim((string)$member->email) !== '') : ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary js-member-remind"
                                                    data-url="<?= h($this->Url->build(['action' => 'remind', $member->id])) ?>"
                                                    <?php if ($competition !== null) : ?>
                                                        data-competition="<?= (int)$competition->id ?>"
                                                    <?php endif; ?>
                                                >Emlékeztető email küldése</button>
                                            <?php elseif ($canRemind) : ?>
                                                Nincs e-mail címe
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    'Üzenet a telefonra',
                                                    ['controller' => 'Messages', 'action' => 'index', '?' => ['user' => $memberId]],
                                                    ['class' => 'btn btn-sm btn-outline-secondary'],
                                                ) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($members !== []) : ?>
                <div class="card-footer border-top d-flex align-items-center justify-content-between">
                    <?php $this->Paginator->setPaginated($membersPage); ?>
                    <?= $this->element('JeffAdmin.pagination') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
