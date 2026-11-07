<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Competition> $competitions
 * @var array<int, \App\Model\Entity\Registration> $mineByCompetition
 */
$this->assign('title', 'Nevezésem');
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
            <div class="card-header"><strong>Nevezésem</strong></div>
            <div class="card-body">
                <?php if (count($competitions) === 0) : ?>
                    <p>Jelenleg nincs verseny, amire jelentkezni lehet.</p>
                <?php else : ?>
                    <?php foreach ($competitions as $competition) : ?>
                        <?= $this->element('competition_registration_card', [
                            'competition' => $competition,
                            'registration' => $mineByCompetition[(int)$competition->id] ?? null,
                            'deleteAction' => 'deleteOwn',
                        ]) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
