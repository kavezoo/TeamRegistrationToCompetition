<?php
/**
 * Elfelejtett jelszó — ugyanaz a JeffAdmin auth kártya, mint a bejelentkezés.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 */
$this->set('authHeading', 'Elfelejtett jelszó');
$this->set('authSubtitle', 'Add meg az e-mail-címed vagy a felhasználóneved');
$this->assign('title', 'Elfelejtett jelszó');
?>
<div class="login-form">
<?= $this->Form->create($user, [
    'templates' => [
        'inputContainer' => '<div class="form-group">{{content}}</div>',
        'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
        'submitContainer' => '{{content}}',
    ],
]) ?>
    <?= $this->Form->control('reference', [
        'label' => 'E-mail vagy felhasználónév',
        'class' => 'au-input au-input--full',
        'required' => true,
        'autofocus' => true,
    ]) ?>
    <?= $this->Form->button('Küldés', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
<?= $this->Form->end() ?>
</div>
<div class="register-link">
    <p><?= $this->Html->link('Vissza a bejelentkezéshez', ['action' => 'login']) ?></p>
</div>
