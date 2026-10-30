<?php
/**
 * Regisztráció — ugyanaz a JeffAdmin auth kártya, mint a bejelentkezés.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 */

use Cake\Core\Configure;

$this->set('authHeading', 'Regisztráció');
$this->set('authSubtitle', 'Hozd létre a fiókodat');
$this->assign('title', 'Regisztráció');

$input = ['class' => 'au-input au-input--full'];
?>
<div class="login-form">
<?= $this->Form->create($user, [
    'templates' => [
        'inputContainer' => '<div class="form-group">{{content}}</div>',
        'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
        'submitContainer' => '{{content}}',
    ],
]) ?>
    <?= $this->Form->control('username', ['label' => 'Felhasználónév', 'autocomplete' => 'username'] + $input) ?>
    <?= $this->Form->control('email', ['type' => 'email', 'label' => 'E-mail', 'autocomplete' => 'email'] + $input) ?>
    <?= $this->Form->control('password', [
        'label' => 'Jelszó',
        'id' => 'new-password',
        'autocomplete' => 'new-password',
    ] + $input) ?>
    <?php if (Configure::read('Users.passwordMeter.enabled')) : ?>
        <?= $this->User->addPasswordMeter() ?>
    <?php endif; ?>
    <?= $this->Form->control('password_confirm', [
        'type' => 'password',
        'label' => 'Jelszó még egyszer',
        'required' => true,
        'autocomplete' => 'new-password',
    ] + $input) ?>
    <?= $this->Form->control('first_name', ['label' => 'Keresztnév', 'autocomplete' => 'given-name'] + $input) ?>
    <?= $this->Form->control('last_name', ['label' => 'Vezetéknév', 'autocomplete' => 'family-name'] + $input) ?>
    <?php if (Configure::read('Users.Tos.required')) : ?>
        <div class="login-checkbox">
            <?= $this->Form->control('tos', [
                'type' => 'checkbox',
                'label' => 'Elfogadom a feltételeket',
                'required' => true,
                'templates' => ['inputContainer' => '{{content}}'],
            ]) ?>
        </div>
    <?php endif; ?>
    <?php if (Configure::read('Users.reCaptcha.registration')) : ?>
        <div class="form-group"><?= $this->User->addReCaptcha() ?></div>
    <?php endif; ?>
    <?= $this->Form->button('Regisztráció', ['class' => 'au-btn au-btn--block au-btn--green', 'id' => 'btn-submit']) ?>
<?= $this->Form->end() ?>
</div>

<div class="social-login-content">
    <div class="social-button">
        <?= $this->element('google_button', [
            'url' => $this->Url->build('/auth/google'),
            'label' => 'Regisztráció Google-fiókkal',
        ]) ?>
    </div>
</div>

<div class="register-link">
    <p>
        Van már fiókod?
        <?= $this->Html->link('Bejelentkezés', ['action' => 'login']) ?>
    </p>
</div>
