<?php
/**
 * Új jelentkező főoldala: csak a saját neve szerkeszthető.
 *
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $user
 * @var bool $profileSaved
 */
$requestState = (string)($user->get('membership_request') ?? '');
$this->assign('title', 'Saját adatok');
?>
<?= $this->Form->create($user, [
    'url' => ['controller' => 'Pages', 'action' => 'saveProfile'],
    'class' => 'login-form',
    'templates' => [
        'inputContainer' => '<div class="form-group">{{content}}</div>',
        'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
        'submitContainer' => '{{content}}',
    ],
]) ?>
    <?= $this->Form->control('username', [
        'label' => 'Felhasználónév',
        'class' => 'au-input au-input--full',
        'readonly' => true,
        'disabled' => true,
    ]) ?>
    <?= $this->Form->control('email', [
        'label' => 'E-mail',
        'class' => 'au-input au-input--full',
        'readonly' => true,
        'disabled' => true,
    ]) ?>
    <?= $this->Form->control('first_name', [
        'label' => 'Vezetéknév',
        'class' => 'au-input au-input--full',
        'autofocus' => true,
    ]) ?>
    <?= $this->Form->control('last_name', [
        'label' => 'Keresztnév',
        'class' => 'au-input au-input--full',
    ]) ?>
    <?= $this->Form->button('Mentés', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
<?= $this->Form->end() ?>

<?php if ($profileSaved && $requestState === 'pending') : ?>
    <p class="auth-subtitle">A felvételi kérelem elküldve. A klubelnök döntésére vár.</p>
<?php elseif ($profileSaved && $requestState === 'denied') : ?>
    <p class="auth-subtitle">A klubelnök elutasította a felvételt. Újra kérheted.</p>
    <?= $this->Form->create(null, ['url' => ['controller' => 'Pages', 'action' => 'requestMembership']]) ?>
        <?= $this->Form->button('Kérem a klubba való felvételemet', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
    <?= $this->Form->end() ?>
<?php elseif ($profileSaved) : ?>
    <?= $this->Form->create(null, ['url' => ['controller' => 'Pages', 'action' => 'requestMembership']]) ?>
        <?= $this->Form->button('Kérem a klubba való felvételemet', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
    <?= $this->Form->end() ?>
<?php endif; ?>

<div class="register-link">
    <p><?= $this->Html->link('Kijelentkezés', '/logout') ?></p>
</div>

