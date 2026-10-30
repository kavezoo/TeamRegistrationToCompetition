<?php
/**
 * Hiányzó e-mail a közösségi belépés után — JeffAdmin auth kártya.
 *
 * @var \App\View\AppView $this
 */
$this->set('authHeading', 'E-mail-cím');
$this->set('authSubtitle', 'A Google-fiókhoz tartozó e-mail kell a belépéshez');
$this->assign('title', 'E-mail-cím');
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
    ]) ?>
    <?= $this->Form->button('Tovább', ['class' => 'au-btn au-btn--block au-btn--green']) ?>
<?= $this->Form->end() ?>
