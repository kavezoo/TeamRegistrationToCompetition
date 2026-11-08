<?php
/**
 * @var \App\View\AppView $this
 * @var array<string, mixed> $thread
 */
$this->assign('title', (string)($thread['subject'] ?? 'Üzenet'));
echo $this->element('conversation_thread');
