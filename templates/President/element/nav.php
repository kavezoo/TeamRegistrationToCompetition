<?php
/**
 * Klubelnöki menü.
 *
 * @var \Cake\View\View $this
 * @var string $controller
 * @var string $prefix
 * @var int|null $pendingCount
 */
$current = (string)($controller ?? $this->request->getParam('controller') ?? '');
$prefixName = (string)($prefix ?? $this->request->getParam('prefix') ?? 'President');
$pending = (int)($pendingCount ?? 0);

$items = [
    'Groups' => ['Nevezők', 'fa-people-group'],
    'Applicants' => ['Jelentkezők', 'fa-user-check'],
    'Competitions' => ['Versenyek', 'fa-trophy'],
    'Registrations' => ['Nevezésem', 'fa-clipboard-list'],
    'Members' => ['Tagok', 'fa-users'],
    'Teams' => ['Alcsapatok', 'fa-user-group'],
    'Messages' => ['Üzenetek', 'fa-comment-dots'],
];
?>
<aside class="menu-sidebar" id="main-sidebar">
    <div class="logo">
        <a class="logo-link" href="<?= $this->Url->build(['prefix' => $prefixName, 'controller' => 'Dashboard', 'action' => 'home']) ?>" aria-label="Klubelnök">
            <span class="logo-mark" aria-hidden="true">T</span>
            <span class="logo-text">TeamReg</span>
        </a>
        <button type="button" class="sidebar-close js-sidebar-toggle" aria-label="Menü bezárása">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        <nav class="navbar-sidebar">
            <ul class="list-unstyled navbar__list">
                <?php foreach ($items as $name => [$label, $icon]) : ?>
                    <li<?= $current === $name ? ' class="active"' : '' ?>>
                        <?= $this->Html->link(
                            '<i class="fa-solid ' . h($icon) . '" aria-hidden="true"></i>' . h($label)
                            . ($name === 'Applicants' && $pending > 0 ? ' (' . $pending . ')' : ''),
                            ['prefix' => $prefixName, 'controller' => $name, 'action' => 'index'],
                            ['escape' => false]
                        ) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
