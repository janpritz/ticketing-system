import './bootstrap';
import 'flowbite';
import Swal from 'sweetalert2';

// Get the version passed from Laravel (rendered in your Blade file)
const APP_VERSION = window.appConfig ? window.appConfig.version : '1.0.0';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // We append the version here.
        // If v=1.0.6 changes to v=1.0.7, the browser treats it as a brand new file.
        navigator.serviceWorker.register(`/sw.js?v=${APP_VERSION}`).then(reg => {
            if (reg.waiting) showUpdateModal(reg.waiting);

            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateModal(newWorker);
                    }
                });
            });
        });
    });

    // Listen for service worker controller change to refresh the page
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        window.location.reload();
    });
}

function showUpdateModal(worker) {
    Swal.fire({
        title: 'New version available!',
        text: 'A new version of the app is available. Update now?',
        icon: 'info',
        background: '#1f2937',
        color: '#ffffff',
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Update Now',
        cancelButtonText: 'Later',
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            worker.postMessage({ type: 'SKIP_WAITING' });
        }
    });
}