<?php
/**
 * Egy beszélgetés és a válasz. A válasz flashként megy a másik félnek.
 *
 * @var \App\View\AppView $this
 * @var array<string, mixed> $thread
 */
$prefix = (string)$this->request->getParam('prefix');
$posts = $thread['posts'] ?? [];
?>
<div class="row" style="margin-top: 16px;">
    <div class="col-12 col-lg-8">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?= h((string)$thread['subject']) ?></strong>
                <?= $this->Html->link('Vissza az üzenetekhez', ['prefix' => $prefix, 'controller' => 'Messages', 'action' => 'index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
            <div class="card-body">
                <p>Beszélgetés vele: <strong><?= h((string)$thread['peer_name']) ?></strong></p>
                <?php foreach ($posts as $post) : ?>
                    <div class="border rounded p-3 mb-3<?= !empty($post['mine']) ? ' bg-light' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <strong><?= h((string)$post['sender_name']) ?></strong>
                            <small><?= h((string)$post['created']) ?></small>
                        </div>
                        <div class="mt-2" style="white-space: pre-wrap;"><?= h((string)$post['body']) ?></div>
                    </div>
                <?php endforeach; ?>
                <?= $this->Form->create(null) ?>
                <div class="mb-3">
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => 'Válasz',
                        'class' => 'form-control',
                        'rows' => 4,
                        'required' => true,
                    ]) ?>
                </div>
                <?= $this->Form->button('Válasz küldése a telefonra', ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
