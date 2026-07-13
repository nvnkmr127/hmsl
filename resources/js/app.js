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

// Expose triggerOfflineSync on window
window.triggerOfflineSync = async function(btn) {
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Syncing...';
    }
    
    try {
        if (window.__TAURI__) {
            await window.__TAURI__.invoke('trigger_sync');
        } else {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const resp = await fetch('/sync/now', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                }
            });
            if (resp.ok) {
                const banner = document.getElementById('offline-banner');
                if (banner) banner.style.display = 'none';
            }
        }
    } catch (e) {
        console.error('Sync failed:', e);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerText = 'Sync Now';
        }
    }
};

import { listen } from '@tauri-apps/api/event';

if (window.__TAURI__) {
    listen('connectivity-changed', (event) => {
        const isOnline = event.payload.online;
        
        const banner = document.getElementById('offline-banner');
        if (banner) {
            banner.style.display = isOnline ? 'none' : 'flex';
        }

        if (window.Livewire) {
            window.Livewire.dispatch('notify', {
                type: isOnline ? 'success' : 'warning',
                message: isOnline ? 'Connected to server — syncing...' : 'Working offline — changes will sync when connected'
            });
        }
    });
}

// Fallback listener for Livewire sync-status-changed events
window.addEventListener('sync-status-changed', (event) => {
    const isOnline = event.detail.isOnline;
    const banner = document.getElementById('offline-banner');
    if (banner) {
        banner.style.display = isOnline ? 'none' : 'flex';
    }
});

// Intercept PDF/print links in Tauri and open in OS default viewer
document.addEventListener('click', async (e) => {
    if (!window.__TAURI__) return;
    
    const link = e.target.closest('a[href*="/pdf"], a[href*="/print"]');
    if (!link) return;
    
    e.preventDefault();
    try {
        const { invoke } = window.__TAURI__;
        await invoke('open_pdf_url', { url: link.href });
    } catch (err) {
        console.error('Failed to open PDF in default viewer:', err);
    }
});
