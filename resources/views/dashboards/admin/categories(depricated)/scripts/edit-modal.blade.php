(function(){
  const editBtns = document.querySelectorAll('.editCategoryBtn');
  const editModal = document.getElementById('editCategoryModal');
  const closeEditBtn = document.getElementById('closeEditCategoryModal');
  const cancelEditBtn = document.getElementById('cancelEditCategory');
  const editBackdrop = document.getElementById('editCategoryModalBackdrop');
  const editForm = document.getElementById('editCategoryForm');
  const editCategoryId = document.getElementById('editCategoryId');
  const editRoleId = document.getElementById('editRoleId');
  const editName = document.getElementById('editName');
  const editDescription = document.getElementById('editDescription');

  function showEditModal(category) {
    if (!editModal) return;
    editCategoryId.value = category.id;
    editRoleId.value = category.role_id;
    editName.value = category.name;
    editDescription.value = category.description || '';
    editForm.action = `/admin/categories/${category.id}`;
    editModal.classList.remove('hidden');
    editModal.classList.add('flex');
    editModal.classList.add('items-start');
    document.body.classList.add('overflow-hidden');
  }

  function hideEditModal() {
    if (!editModal) return;
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  editBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const category = {
        id: this.getAttribute('data-id'),
        name: this.getAttribute('data-name'),
        description: this.getAttribute('data-description'),
        role_id: this.getAttribute('data-role-id')
      };
      showEditModal(category);
    });
  });

  if (closeEditBtn) closeEditBtn.addEventListener('click', hideEditModal);
  if (cancelEditBtn) cancelEditBtn.addEventListener('click', hideEditModal);
  if (editBackdrop) editBackdrop.addEventListener('click', hideEditModal);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !editModal.classList.contains('hidden')) hideEditModal();
  });

  @if (old('name') && request()->isMethod('PUT'))
    document.addEventListener('DOMContentLoaded', function () {
      const categoryId = '{{ request()->segment(3) }}'; 
      editCategoryId.value = categoryId;
      editRoleId.value = '{{ old('role_id') }}';
      editName.value = '{{ old('name') }}';
      editDescription.value = '{{ old('description') }}';
      editForm.action = `/admin/categories/${categoryId}`;
      showEditModal({id: categoryId, role_id: '{{ old('role_id') }}', name: '{{ old('name') }}', description: '{{ old('description') }}'});
    });
  @endif
})();