<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Competition $competition
 */
$this->assign('title', $competition->isNew() ? 'Új verseny' : 'Verseny szerkesztése');
?>
<?= $this->Flash->render() ?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-lg-8">
        <div class="card shadow">
            <div class="card-header"><strong><?= h($this->fetch('title')) ?></strong></div>
            <div class="card-body">
                <?= $this->Form->create($competition) ?>
                    <?= $this->Form->control('name', ['label' => 'Név']) ?>
                    <?= $this->Form->control('date_from', ['label' => 'Időpont', 'type' => 'date']) ?>
                    <?= $this->Form->control('date_deadline', ['label' => 'Nevezési határidő', 'type' => 'date']) ?>
                    <?= $this->Form->control('comment', ['label' => 'Megjegyzés']) ?>
                    <?= $this->Form->control('visible', ['label' => 'Látható']) ?>
                    <?= $this->Form->control('pos', ['label' => 'Sorrend', 'type' => 'number']) ?>
                    <?= $this->Form->button('Mentés', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Mégse', ['action' => 'index'], ['class' => 'btn btn-link']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
