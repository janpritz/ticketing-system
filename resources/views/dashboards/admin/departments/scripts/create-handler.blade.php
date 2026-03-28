(function(){
  const createModal = document.getElementById('createDeptModal');
  const openCreateBtn = document.getElementById('openCreateDeptBtn');
  const createForm = document.getElementById('createDeptForm');

  if (openCreateBtn) {
    openCreateBtn.addEventListener('click', () => {
      document.getElementById('rolesContainer').innerHTML = `
        <div class="flex items-center gap-2 role-row">
          <input type="text" name="roles[]" placeholder="Role name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          <button type="button" class="remove-role-btn hidden text-red-500 p-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg></button>
        </div>`.repeat(2);
      
      document.querySelectorAll('.remove-role-btn').forEach(btn => {
        btn.addEventListener('click', function() { this.closest('.role-row').remove(); updateRemoveButtons(); });
      });
      updateRemoveButtons();
      createModal.classList.remove('hidden');
    });
  }

  if (createForm) {
    createForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const submitBtn = document.getElementById('createDeptSubmit');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving...';
      submitBtn.disabled = true;

      try {
        const response = await fetch(this.action, {
          method: 'POST',
          body: new FormData(this),
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

  document.querySelectorAll('[data-close="create-dept"]').forEach(el => el.addEventListener('click', () => createModal.classList.add('hidden')));
})();