(function(){
  const editModal = document.getElementById('editDeptModal');
  const editForm = document.getElementById('editDeptForm');

  document.querySelectorAll('.openEditDeptBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_dept_id').value = btn.getAttribute('data-id');
      document.getElementById('edit_dept_name').value = btn.getAttribute('data-name') || '';
      document.getElementById('edit_dept_description').value = btn.getAttribute('data-description') || '';
      editForm.action = "{{ url('/admin/departments') }}/" + btn.getAttribute('data-id');
      editModal.classList.remove('hidden');
    });
  });

  if (editForm) {
    editForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        const response = await fetch(this.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        const data = await response.json();
        if (response.ok && data.success) {
          window.location.reload();
        } else {
          Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: data.message || 'Error', showConfirmButton: false, timer: 3000 });
        }
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }

  document.querySelectorAll('[data-close="edit-dept"]').forEach(el => el.addEventListener('click', () => editModal.classList.add('hidden')));
})();