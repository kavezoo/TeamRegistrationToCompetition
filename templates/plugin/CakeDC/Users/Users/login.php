<?php
/**
 * Bejelentkezés — csak Google-fiókkal.
 *
 * @var \App\View\AppView $this
 */

$this->set('authHeading', 'Bejelentkezés');
$this->set('authSubtitle', 'A belépéshez Google-fiók kell.');
$this->set('authGoogleOnly', true);
$this->assign('title', 'Bejelentkezés');
?>
<div class="social-login-content">
    <div class="social-button">
        <?= $this->element('google_button', [
            'url' => $this->Url->build('/auth/google'),
            'label' => 'Bejelentkezés Google-fiókkal',
        ]) ?>
    </div>
</div>
