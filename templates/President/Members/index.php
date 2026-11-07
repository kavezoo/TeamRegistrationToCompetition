<?php
/**
 * @var \App\View\AppView $this
 * @var array<int, \App\Model\Entity\Competition> $competitions
 * @var \App\Model\Entity\Competition|null $competition
 * @var array<int, \App\Model\Entity\User> $members
 * @var array<string, true> $registered
 * @var array<string, array<int, \App\Model\Entity\RegistrationReminder>> $history
 * @var string|null $lastRecordId
 */
use App\Model\Entity\User;

$this->assign('title', 'Tagok');

$options = [];
foreach ($competitions as $row) {
    $options[$row->id] = $row->name;
}
$canRemind = $competition !== null && $competition->visible && !$competition->registrationClosed();
$missing = 0;
if ($canRemind) {
    foreach ($members as $member) {
        if (!isset($registered[(string)$member->id]) && trim((string)$member->email) !== '') {
            $missing++;
        }
    }
}
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>Aktív tagok</strong>
                <?php if ($canRemind && $missing > 0) : ?>
                    <?= $this->Form->create(null, ['url' => ['action' => 'remindMissing'], 'class' => 'mb-0']) ?>
                        <?= $this->Form->hidden('competition_id', ['value' => $competition->id]) ?>
                        <?= $this->Form->button('Emlékeztető mindenkinek, aki még nem jelentkezett (' . $missing . ')', [
                            'class' => 'btn btn-sm btn-primary',
                            'onclick' => 'return confirm(' . json_encode('Elküldöd az emlékeztetőt mindenkinek, aki még nem jelentkezett?') . ');',
                        ]) ?>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($competitions === []) : ?>
                    <p>Még nincs kiírt verseny.</p>
                <?php else : ?>
                    <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end mb-3']) ?>
                        <div class="col-12 col-md-6">
                            <?= $this->Form->control('competition_id', [
                                'type' => 'select',
                                'label' => 'Verseny',
                                'options' => $options,
                                'value' => $competition?->id,
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-12 col-md-auto">
                            <?= $this->Form->button('Mutat', ['class' => 'btn btn-outline-secondary']) ?>
                        </div>
                    <?= $this->Form->end() ?>
                    <?php if ($competition !== null && !$competition->visible) : ?>
                        <p>Ez a verseny a tagoknak nem látható, ezért emlékeztetőt nem lehet küldeni.</p>
                    <?php elseif ($competition !== null && $competition->registrationClosed()) : ?>
                        <p>A nevezési határidő lejárt, emlékeztetőt nem lehet küldeni.</p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($members === []) : ?>
                    <p class="mb-0">Nincs aktív tag.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-data2 table-border table-hover table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>E-mail</th>
                                    <th>Jelentkezés</th>
                                    <th></th>
                                    <th>Elküldött üzenetek</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                <?php foreach ($members as $member) : ?>
                                    <?php
                                    $joined = isset($registered[(string)$member->id]);
                                    $messages = $history[(string)$member->id] ?? [];
                                    $isLast = isset($lastRecordId) && (string)$lastRecordId === (string)$member->id;
                                    ?>
                                    <tr<?= $isLast ? ' class="is-last-touched"' : '' ?> id="row-<?= h((string)$member->id) ?>" data-id="<?= h((string)$member->id) ?>">
                                        <td><?= $this->element('user_name', ['user' => $member]) ?></td>
                                        <td><?= h($member->email) ?></td>
                                        <td><?= $competition === null ? '' : ($joined ? 'Jelentkezett' : 'Még nem jelentkezett') ?></td>
                                        <td>
                                            <?php if ($canRemind && !$joined && trim((string)$member->email) !== '') : ?>
                                                <?= $this->Form->create(null, ['url' => ['action' => 'remind', $member->id], 'class' => 'mb-0']) ?>
                                                    <?= $this->Form->hidden('competition_id', ['value' => $competition->id]) ?>
                                                    <?= $this->Form->button('E-mail küldése', [
                                                        'class' => 'btn btn-sm btn-outline-primary',
                                                        'onclick' => 'return confirm(' . json_encode('Elküldöd az emlékeztetőt, hogy még nem jelentkezett a versenyre?') . ');',
                                                    ]) ?>
                                                <?= $this->Form->end() ?>
                                            <?php elseif ($canRemind && !$joined) : ?>
                                                Nincs e-mail címe
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($messages === []) : ?>
                                                Még nem kapott emlékeztetőt.
                                            <?php else : ?>
                                                <?php foreach ($messages as $reminder) : ?>
                                                    <div class="mb-2">
                                                        <strong><?= $this->Input->formatDatetime($reminder->created) ?></strong>
                                                        <div class="small" style="white-space:pre-wrap"><?= h($reminder->message) ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
