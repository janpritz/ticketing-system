import './bootstrap';
import 'flowbite';

// Get the version passed from Laravel (rendered in your Blade file)
const APP_VERSION = window.appConfig ? window.appConfig.version : '1.0.0';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // We append the version here. 
        // If v=1.0.6 changes to v=1.0.7, the browser treats it as a brand new file.
        navigator.serviceWorker.register(`/sw.js?v=${APP_VERSION}`).then(reg => {
            if (reg.waiting) showUpdateToast(reg.waiting);

            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateToast(newWorker);
                    }
                });
            });
        });
    });
}

function showUpdateToast(worker) {
    const toast = document.getElementById('pwa-update-toast');
    toast.classList.remove('translate-y-32'); // Slides the toast up
    
    document.getElementById('pwa-update-btn').onclick = () => {
        worker.postMessage({ type: 'SKIP_WAITING' });
    };
}