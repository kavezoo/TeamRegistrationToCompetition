<?php
/**
 * Profil — JeffAdmin auth kártya, menü nélkül.
 * A new szerep itt szerkeszti a saját nevét.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 * @var bool $isCurrentUser
 * @var string $avatarPlaceholder
 */
use App\Model\Entity\User;

$displayName = User::displayName($user);
$isNew = (string)$user->role === User::ROLE_NEW;

$this->set('authWide', true);
$this->set('authHeading', $displayName);
$this->set(
    'authSubtitle',
    $isNew
        ? 'A jelentkezésed még nincs elfogadva. Addig a főoldal és a saját adataid érhetők el.'
        : 'Saját adatok'
);
$this->assign('title', 'Profil');

$connectedGoogle = false;
foreach ($user->social_accounts ?? [] as $account) {
    if (strtolower((string)$account->provider) === 'google') {
        $connectedGoogle = true;
        break;
    }
}
?>
<?php if ($isCurrentUser) : ?>
    <?= $this->Form->create($user, [
        'class' => 'login-form',
        'templates' => [
            'inputContainer' => '<div class="form-group">{{content}}</div>',
            'inputContainerError' => '<div class="form-group has-error">{{content}}{{error}}</div>',
            'submitContainer' => '{{content}}',
        ],
    ]) ?>
        <?= $this->Form->control('last_name', [
            'label' => 'Vezetéknév',
            'class' => 'au-input au-input--full',
            'autofocus' => true,
        ]) ?>
        <?= $this->Form->control('first_name', [
            'label' => 'Keresztnév',
            'class' => 'au-input au-input--full',
        ]) ?>
        <p class="auth-subtitle">E-mail: <?= h($user->email) ?></p>
        <?= $this->Form->button('Mentés', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
    <?= $this->Form->end() ?>
<?php else : ?>
    <dl class="auth-dl">
        <dt>Név</dt>
        <dd><?= h($displayName) ?></dd>
        <dt>E-mail</dt>
        <dd><?= h($user->email) ?></dd>
    </dl>
<?php endif; ?>

<?php if ($connectedGoogle) : ?>
    <p class="auth-subtitle">Google-fiók csatlakoztatva</p>
<?php elseif ($isCurrentUser) : ?>
    <div class="social-login-content">
        <div class="social-button">
            <?= $this->element('google_button', [
                'url' => $this->Url->build('/link-social/google'),
                'label' => 'Google-fiók csatlakoztatása',
            ]) ?>
        </div>
    </div>
<?php endif; ?>

<div class="register-link">
    <p>
        <?= $this->Html->link('Főoldal', '/') ?>
        <?php if ($isCurrentUser) : ?>
            ·
            <?= $this->Html->link('Jelszócsere', ['action' => 'changePassword']) ?>
        <?php endif; ?>
        ·
        <?= $this->Html->link('Kijelentkezés', ['action' => 'logout']) ?>
    </p>
</div>
