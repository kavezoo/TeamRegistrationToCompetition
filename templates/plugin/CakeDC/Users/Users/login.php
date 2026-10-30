<?php
/**
 * Bejelentkezés — JeffAdmin auth kártya.
 *
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$this->set('authHeading', 'Bejelentkezés');
$this->set('authSubtitle', 'Add meg az e-mail-címed és a jelszavad');
$this->assign('title', 'Bejelentkezés');

$registrationActive = Configure::read('Users.Registration.active');
?>
<?= $this->Form->create(null, [
    'class' => 'login-form',
    'templates' => [
        'inputContainer' => '<div class="form-group">{{content}}</div>',
        'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
        'submitContainer' => '{{content}}',
    ],
]) ?>
    <?= $this->Form->control('email', [
        'type' => 'email',
        'label' => 'E-mail',
        'class' => 'au-input au-input--full',
        'required' => true,
        'autofocus' => true,
        'autocomplete' => 'username',
    ]) ?>
    <?= $this->Form->control('password', [
        'label' => 'Jelszó',
        'class' => 'au-input au-input--full',
        'required' => true,
        'autocomplete' => 'current-password',
    ]) ?>
    <?php if (Configure::read('Users.reCaptcha.login')) : ?>
        <div class="form-group"><?= $this->User->addReCaptcha() ?></div>
    <?php endif; ?>
    <div class="login-checkbox">
        <?php if (Configure::read('Users.RememberMe.active')) : ?>
            <?= $this->Form->control(Configure::read('Users.Key.Data.rememberMe'), [
                'type' => 'checkbox',
                'label' => 'Emlékezz rám',
                'checked' => Configure::read('Users.RememberMe.checked'),
                'templates' => ['inputContainer' => '{{content}}'],
            ]) ?>
        <?php else : ?>
            <span></span>
        <?php endif; ?>
        <?= $this->Html->link('Elfelejtett jelszó', ['action' => 'requestResetPassword']) ?>
    </div>
    <?= $this->Form->button('Bejelentkezés', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
<?= $this->Form->end() ?>

<div class="social-login-content">
    <div class="social-button">
        <?= $this->element('google_button', [
            'url' => $this->Url->build('/auth/google'),
            'label' => 'Bejelentkezés Google-fiókkal',
        ]) ?>
    </div>
</div>

<?php if ($registrationActive) : ?>
    <div class="register-link">
        <p>
            Nincs még fiókod?
            <?= $this->Html->link('Regisztráció', ['action' => 'register']) ?>
        </p>
    </div>
<?php endif; ?>
