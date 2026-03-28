(function(){
  document.querySelectorAll('.deleteCategoryBtn').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      const id = this.getAttribute('data-id');
      const row = this.closest('tr');

      const result = await Swal.fire({
        title: 'Delete category?',
        text: 'This will remove the category. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
      });

      if (!result.isConfirmed) return;

      try {
        const response = await fetch(`/admin/categories/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (response.ok && data.success) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: data.message || 'Category deleted successfully',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
          if (row) row.remove();
        } else {
          Swal.fire({
            title: 'Cannot Delete Category',
            text: data.message || 'Failed to delete category',
            icon: 'error',
            confirmButtonColor: '#d33'
          });
        }
      } catch (error) {
        console.error('Delete error:', error);
        Swal.fire({ title: 'Error', text: 'An error occurred', icon: 'error' });
      }
    });
  });
})();