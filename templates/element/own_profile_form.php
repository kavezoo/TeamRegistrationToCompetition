<?php
/**
 * Saját név szerkesztése a klubelnöki és a tagi felületen.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 */
?>
<?= $this->Flash->render() ?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-lg-8">
        <div class="card shadow">
            <div class="card-header"><strong>Profil</strong></div>
            <div class="card-body">
                <?= $this->Form->create($user) ?>
                    <?= $this->Form->control('username', [
                        'label' => 'Felhasználónév',
                        'readonly' => true,
                        'disabled' => true,
                    ]) ?>
                    <?= $this->Form->control('email', [
                        'label' => 'E-mail',
                        'readonly' => true,
                        'disabled' => true,
                    ]) ?>
                    <?= $this->Form->control('first_name', [
                        'label' => 'Vezetéknév',
                        'autofocus' => true,
                    ]) ?>
                    <?= $this->Form->control('last_name', [
                        'label' => 'Keresztnév',
                    ]) ?>
                    <?= $this->Form->button('Mentés', ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
