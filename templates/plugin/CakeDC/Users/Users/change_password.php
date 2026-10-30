<?php
/**
 * Jelszócsere — JeffAdmin auth kártya.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 * @var bool $validatePassword
 */

use Cake\Core\Configure;

$this->set('authHeading', 'Új jelszó');
$this->set('authSubtitle', 'Add meg az új jelszavad');
$this->assign('title', 'Új jelszó');
$field = ['class' => 'au-input au-input--full'];
?>
<?= $this->Form->create($user, [
    'class' => 'login-form',
    'templates' => [
        'inputContainer' => '<div class="form-group">{{content}}</div>',
        'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
        'submitContainer' => '{{content}}',
    ],
]) ?>
    <?php if (!empty($validatePassword)) : ?>
        <?= $this->Form->control('current_password', [
            'type' => 'password',
            'label' => 'Jelenlegi jelszó',
            'required' => true,
            'autocomplete' => 'current-password',
        ] + $field) ?>
    <?php endif; ?>
    <?= $this->Form->control('password', [
        'type' => 'password',
        'label' => 'Új jelszó',
        'required' => true,
        'id' => 'new-password',
        'autocomplete' => 'new-password',
    ] + $field) ?>
    <?php if (Configure::read('Users.passwordMeter.enabled')) : ?>
        <?= $this->User->addPasswordMeter() ?>
    <?php endif; ?>
    <?= $this->Form->control('password_confirm', [
        'type' => 'password',
        'label' => 'Új jelszó még egyszer',
        'required' => true,
        'autocomplete' => 'new-password',
    ] + $field) ?>
    <?= $this->Form->button('Jelszó mentése', ['class' => 'au-btn au-btn--block au-btn--green', 'id' => 'btn-submit']) ?>
<?= $this->Form->end() ?>
<div class="register-link">
    <p><?= $this->Html->link('Vissza a profilhoz', ['action' => 'profile']) ?></p>
</div>
