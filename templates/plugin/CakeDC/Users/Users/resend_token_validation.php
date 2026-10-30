<?php
/**
 * Megerősítő levél újraküldése — JeffAdmin auth kártya.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 */
$this->set('authHeading', 'Megerősítő levél');
$this->set('authSubtitle', 'Újraküldjük a fiók megerősítéséhez');
$this->assign('title', 'Megerősítő levél');
?>
<?= $this->Form->create($user, [
    'class' => 'login-form',
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
    <?= $this->Form->button('Levél küldése', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
<?= $this->Form->end() ?>
<div class="register-link">
    <p><?= $this->Html->link('Vissza a bejelentkezéshez', ['action' => 'login']) ?></p>
</div>
