<?php
/**
 * JeffAdmin auth kártya: a plugin CSS-e, oldalsáv és fejléc nélkül.
 *
 * @var \App\View\AppView $this
 * @var string|null $authHeading
 * @var string|null $authSubtitle
 * @var bool|null $authWide
 * @var bool|null $authGoogleOnly
 */
$wide = !empty($authWide);
$googleOnly = !empty($authGoogleOnly);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= h($this->fetch('title') ?: ($authHeading ?? 'TeamReg')) ?></title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css([
        'JeffAdmin./vendor/fontawesome-7.3.1/css/all.min',
        'JeffAdmin./vendor/bootstrap-5.3.8.min',
        'JeffAdmin./css/theme',
        'JeffAdmin./css/app',
        'JeffAdmin./css/main',
        'google-button',
    ]) ?>
    <?= $this->fetch('css') ?>
    <style>
        body.app.auth-page.auth-page--wide .page-wrapper,
        body.app.auth-page.auth-page--wide .login-wrap {
            max-width: 560px;
        }
        body.app.auth-page .message {
            margin: 0 0 16px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13.5px;
        }
        body.app.auth-page .message.error {
            background: #fde8e8;
            color: #9b1c1c;
        }
        body.app.auth-page .message.success,
        body.app.auth-page .message.info {
            background: #e8f0fe;
            color: #1e3a8a;
        }
        body.app.auth-page .error-message {
            color: #9b1c1c;
            font-size: 12.5px;
            margin-top: 4px;
        }
        <?php if ($googleOnly) : ?>
        body.app.auth-page .social-login-content {
            margin-top: 0;
            border: 0;
            padding: 0;
        }
        body.app.auth-page .social-login-content::before {
            content: none;
            display: none;
        }
        <?php else : ?>
        body.app.auth-page .social-login-content::before {
            content: "vagy";
            background: none;
        }
        <?php endif; ?>
        body.app.auth-page .auth-dl {
            margin: 0;
        }
        body.app.auth-page .auth-dl dt {
            margin-top: 14px;
            font-size: 12px;
            font-weight: 600;
            color: var(--m-text-muted);
        }
        body.app.auth-page .auth-dl dd {
            margin: 2px 0 0;
        }
    </style>
</head>
<body class="app auth-page<?= $wide ? ' auth-page--wide' : '' ?>">
    <div class="page-wrapper">
        <div class="login-wrap">
            <div class="login-content">
                <a class="auth-brand" href="<?= $this->Url->build('/') ?>">
                    <span class="logo-mark" aria-hidden="true">T</span>
                    <span class="logo-text">TeamReg</span>
                </a>
                <?php if (!empty($authHeading)) : ?>
                    <h1 class="auth-title"><?= h($authHeading) ?></h1>
                <?php endif; ?>
                <?php if (!empty($authSubtitle)) : ?>
                    <p class="auth-subtitle"><?= h($authSubtitle) ?></p>
                <?php endif; ?>
                <?= $this->Flash->render() ?>
                <?= $this->Flash->render('auth') ?>
                <?= $this->fetch('content') ?>
            </div>
        </div>
    </div>
    <?= $this->Html->script('JeffAdmin./vendor/bootstrap-5.3.8.bundle.min') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
