(function(){
  document.querySelectorAll('.deleteDeptBtn').forEach(btn => {
    btn.addEventListener('click', async function () {
      const id = this.getAttribute('data-id');
      const deptRow = this.closest('tr');

      const result = await Swal.fire({
        title: 'Delete department?',
        text: 'Action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#d33'
      });

      if (!result.isConfirmed) return;

      try {
        const response = await fetch(`/admin/departments/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
          }
        });
        const data = await response.json();
        if (response.ok && data.success) {
          if (deptRow) deptRow.remove();
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Deleted', showConfirmButton: false, timer: 3000 });
        }
      } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error occurred' });
      }
    });
  });
})();