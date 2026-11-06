<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Registration $registration
 * @var \App\Model\Entity\Competition $competition
 */
use App\Model\Entity\Registration;

$this->assign('title', 'Jelentkezés');
?>
<div class="row row-tight" style="margin-top: 16px;">
    <div class="col-12 col-xxl-11">
        <?= $this->Form->create($registration, ['class' => 'form-horizontal']) ?>
        <div class="card shadow">
            <div class="card-header form-card-header">
                <div class="form-card-header__title">
                    <strong><?= h($competition->name) ?></strong>
                    <small class="d-block">Jelentkezés</small>
                </div>
            </div>
            <div class="card-body">
                <?php foreach (Registration::TITLED_COUNTS as $titleField => $countField) : ?>
                    <?php $title = trim((string)$competition->get($titleField)); ?>
                    <?php if ($title === '') {
                        continue;
                    } ?>
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label fw-bold" for="<?= h($countField) ?>"><?= h($title) ?></label>
                        <div class="col-md-4">
                            <?= $this->Input->control($countField, [
                                'type' => 'text',
                                'label' => false,
                                'class' => 'form-control',
                                'id' => $countField,
                                'data-number-spinner' => true,
                                'data-integer' => '1',
                                'inputmode' => 'numeric',
                                'min' => '0',
                                'max' => '1000',
                                'step' => '1',
                                'autocomplete' => 'off',
                                'value' => (int)($registration->get($countField) ?? 0),
                            ]) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label fw-bold" for="comment">Megjegyzés</label>
                    <div class="col-md-8">
                        <?= $this->Form->control('comment', [
                            'type' => 'textarea',
                            'label' => false,
                            'class' => 'form-control',
                            'id' => 'comment',
                            'rows' => 4,
                            'maxlength' => 1000,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <?= $this->Form->button('Mentés', ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link('Mégse', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
