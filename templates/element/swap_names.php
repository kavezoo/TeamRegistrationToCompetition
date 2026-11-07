<?php
/**
 * A vezetéknév és a keresztnév mező tartalmát megcseréli. A mentés külön történik.
 *
 * @var \App\View\AppView $this
 * @var string|null $swapClass
 */
$swapClass = $swapClass ?? 'btn btn-outline-secondary btn-sm';
?>
<button type="button" class="<?= h($swapClass) ?>" data-swap-names>Nevek cseréje</button>
<?php
$this->Html->scriptBlock(
    <<<'JS'
if (!window.__swapNamesBound) {
    window.__swapNamesBound = true;
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-swap-names]');
        if (!button) {
            return;
        }
        const form = button.closest('form');
        if (!form) {
            return;
        }
        const first = form.querySelector('[name="first_name"]');
        const last = form.querySelector('[name="last_name"]');
        if (!first || !last) {
            return;
        }
        const value = first.value;
        first.value = last.value;
        last.value = value;
    });
}
JS,
    ['block' => true]
);
?>
