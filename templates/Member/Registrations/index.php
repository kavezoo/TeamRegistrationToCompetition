<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var array<int, \App\Model\Entity\Registration> $mineByCompetition
 * @var array<int, array<int, \App\Model\Entity\Registration>> $rosterByCompetition
 */
use App\Model\Entity\User;

$this->assign('title', 'Versenyek');
$focusCompetition = (int)$this->request->getQuery('competition');
if ($focusCompetition > 0) {
    $this->Html->scriptBlock(
        'document.getElementById(' . json_encode('competition-' . $focusCompetition) . ')?.scrollIntoView({block:"start"});',
        ['block' => true]
    );
}

?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header"><strong>Versenyek</strong></div>
            <div class="card-body">
                <?php if (count($competitions) === 0) : ?>
                    <p>Jelenleg nincs verseny, amire jelentkezni lehet.</p>
                <?php else : ?>
                    <?php foreach ($competitions as $competition) : ?>
                        <?= $this->element('competition_registration_card', [
                            'competition' => $competition,
                            'registration' => $mineByCompetition[(int)$competition->id] ?? null,
                            'deleteAction' => 'delete',
                            'deleteLabel' => 'Nem tudok a versenyen részt venni',
                        ]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php foreach ($competitions as $competition) :
    $rosterRows = $rosterByCompetition[(int)$competition->id] ?? [];
    usort($rosterRows, [\App\Model\Entity\Registration::class, 'compareByAssignment']);
    ?>
    <div class="row" style="margin-top: 16px;">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header"><strong>Jelentkezők</strong></div>
                <div class="card-body">
                    <h2 class="h5"><?= h($competition->name) ?></h2>
                    <?php if ($rosterRows === []) : ?>
                        <p class="mb-0">Még nincs jelentkező.</p>
                    <?php else : ?>
                        <div class="table-responsive mb-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Név</th>
                                        <th>Alcsapat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rosterRows as $registration) : ?>
                                        <tr>
                                            <td><?= h(User::displayName($registration->user)) ?></td>
                                            <?php $teamName = trim((string)($registration->team?->name ?? '')); ?>
                                            <td><?= h($teamName !== '' ? $teamName : 'Nincs besorolva') ?></td>
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
<?php endforeach; ?>
