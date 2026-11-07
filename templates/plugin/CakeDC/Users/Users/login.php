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
$this->Html->css('privacy-notice', ['block' => true]);
?>
<div class="social-login-content">
    <div class="social-button">
        <?= $this->element('google_button', [
            'url' => $this->Url->build('/auth/google'),
            'label' => 'Bejelentkezés Google-fiókkal',
        ]) ?>
    </div>
</div>
<div class="privacy-login">
    <p>A belépéssel az oldal nyilvántartja a Google-fiókból kapott nevet, e-mail-címet és profilképet, valamint a versenyre jelentkezés adatait. Ezeket az adatokat nem adjuk ki harmadik félnek. A részletes nyilatkozat a gombbal nyílik meg.</p>
    <button type="button" class="btn btn-outline-secondary privacy-notice-btn" data-bs-toggle="modal" data-bs-target="#privacy-notice-modal">Adatkezelési nyilatkozat</button>
</div>
<?php $this->append('script', $this->element('privacy_modal')); ?>
