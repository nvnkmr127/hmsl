import './bootstrap';

document.addEventListener('click', (event) => {
    const el = event.target.closest('[wire\\:confirm]');
    if (!el) return;

    if (el.dataset.wireConfirmAccepted === '1') {
        delete el.dataset.wireConfirmAccepted;
        return;
    }

    const message = el.getAttribute('wire:confirm') || 'Are you sure?';
    const ok = window.confirm(message);

    if (!ok) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    el.dataset.wireConfirmAccepted = '1';
    setTimeout(() => el.click(), 0);
}, true);
