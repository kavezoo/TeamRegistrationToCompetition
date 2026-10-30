<?php
/**
 * Profil — JeffAdmin auth kártya, menü nélkül.
 *
 * @var \App\View\AppView $this
 * @var \CakeDC\Users\Model\Entity\User $user
 * @var bool $isCurrentUser
 * @var string $avatarPlaceholder
 */
$this->set('authWide', true);
$this->set('authHeading', trim((string)$user->first_name . ' ' . (string)$user->last_name) ?: (string)$user->username);
$this->set('authSubtitle', 'Profil');
$this->assign('title', 'Profil');

$connectedGoogle = false;
foreach ($user->social_accounts ?? [] as $account) {
    if (strtolower((string)$account->provider) === 'google') {
        $connectedGoogle = true;
        break;
    }
}
?>
<dl class="auth-dl">
    <dt>Felhasználónév</dt>
    <dd><?= h($user->username) ?></dd>
    <dt>E-mail</dt>
    <dd><?= h($user->email) ?></dd>
</dl>

<?php if ($connectedGoogle) : ?>
    <p class="auth-subtitle">Google-fiók csatlakoztatva</p>
<?php else : ?>
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
        <?php if ($isCurrentUser) : ?>
            <?= $this->Html->link('Jelszócsere', ['action' => 'changePassword']) ?>
            ·
        <?php endif; ?>
        <?= $this->Html->link('Kijelentkezés', ['action' => 'logout']) ?>
    </p>
</div>
