<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 * @var array<int|string, string> $teams
 */

$this->assign('title', $competition->name);
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= h($competition->name) ?></strong>
                <span>
                    <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $competition->id], ['class' => 'btn btn-sm btn-secondary']) ?>
                    <?= $this->Html->link('Vissza', ['action' => 'index'], ['class' => 'btn btn-sm btn-link']) ?>
                </span>
            </div>
            <div class="card-body">
                <?= $this->element('competition_schedule', ['competition' => $competition]) ?>
                <?= $this->element('competition_info_link', ['competition' => $competition, 'empty' => true]) ?>
                <h2 class="h5">Jelentkezők</h2>
                <?php if ($teams === []) : ?>
                    <p>Előbb hozz létre alcsapatot, utána lehet besorolni a jelentkezőket.</p>
                    <p><?= $this->Html->link('Alcsapatok', ['controller' => 'Teams', 'action' => 'index'], ['class' => 'btn btn-sm btn-primary']) ?></p>
                <?php endif; ?>
                <?php
                $registrations = $competition->registrations ?? [];
                if (!is_array($registrations)) {
                    $registrations = iterator_to_array($registrations);
                }
                usort($registrations, [\App\Model\Entity\Registration::class, 'compareByAssignment']);
                ?>
                <?php if ($registrations === []) : ?>
                    <p>Erre a versenyre még nem nevezett senki.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Alcsapat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $registration) : ?>
                                    <tr>
                                        <td>
                                            <?= $this->element('user_name', ['user' => $registration->user ?? null]) ?>
                                            <?= $this->element('registration_absence_note', ['registration' => $registration]) ?>
                                        </td>
                                        <td><?= $this->element('registration_team_form', ['registration' => $registration, 'teams' => $teams]) ?></td>
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
