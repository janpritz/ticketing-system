function showToast(type, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

function showFieldErrors(errors) {
    if (!errors) return;

    // Title
    if (errors.title && document.querySelector('#title_error')) {
        $('#title_error').textContent = errors.title[0];
        $('#title_error').classList.remove('hidden');
    }

    // Dates
    if (errors.starts_at || errors.expires_at || errors.date_error) {
        const msg = (errors.starts_at && errors.starts_at[0]) ||
            (errors.expires_at && errors.expires_at[0]) ||
            (errors.date_error && errors.date_error[0]) ||
            null;
        if (msg && document.querySelector('#date_error')) {
            $('#date_error').textContent = msg;
            $('#date_error').classList.remove('hidden');
        }
    }

    // Content
    if (errors.content && document.querySelector('#announcement_error')) {
        $('#announcement_error').textContent = errors.content[0];
        $('#announcement_error').classList.remove('hidden');
    }
}
