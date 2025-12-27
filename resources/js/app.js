import './bootstrap';
import 'flowbite';
import Swal from 'sweetalert2';

// Make SweetAlert2 globally available for inline scripts
window.Swal = Swal;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}