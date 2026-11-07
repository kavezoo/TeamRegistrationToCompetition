<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var iterable<\App\Model\Entity\Registration> $registrations
 */
use App\Model\Entity\User;

$this->assign('title', 'Versenyek');

$namesByCompetition = [];
foreach ($registrations as $registration) {
    $namesByCompetition[(int)$registration->competition_id][] = User::displayName($registration->user);
}
foreach ($namesByCompetition as $competitionId => $names) {
    usort($names, 'strnatcasecmp');
    $namesByCompetition[$competitionId] = $names;
}
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Versenyek</strong>
                <?= $this->Html->link('Új verseny', ['action' => 'add'], ['class' => 'btn btn-sm btn-primary']) ?>
            </div>
            <div class="card-body">
                <?php if ($competitions->count() === 0) : ?>
                    <p>Még nincs kiírt verseny.</p>
                <?php else : ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Verseny ideje</th>
                                    <th>Jelentkezés kezdete</th>
                                    <th>Nevezési határidő</th>
                                    <th>Helyszín</th>
                                    <th>Nevezők</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competitions as $competition) : ?>
                                    <tr>
                                        <td><?= h($competition->name) ?></td>
                                        <td><?= $this->Input->formatDatetime($competition->date_competition) ?></td>
                                        <td><?= $this->Input->formatDate($competition->date_from) ?></td>
                                        <td><?= $this->Input->formatDate($competition->date_deadline) ?></td>
                                        <td>
                                            <?php $map = $competition->mapUrl(); ?>
                                            <?= $map === null ? '' : $this->Html->link('Térkép', $map, ['target' => '_blank', 'rel' => 'noopener noreferrer']) ?>
                                        </td>
                                        <td>
                                            <?php $names = $namesByCompetition[(int)$competition->id] ?? []; ?>
                                            <?php if ($names === []) : ?>
                                                0
                                            <?php else : ?>
                                                <?= h(implode(', ', $names)) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?= $this->Html->link('Nevezők', ['action' => 'view', $competition->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                            <?= $this->Html->link('Szerkesztés', ['action' => 'edit', $competition->id], ['class' => 'btn btn-sm btn-secondary']) ?>
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
