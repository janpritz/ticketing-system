(function(){
  const openBtn = document.getElementById('openAddCategoryModal');
  const modal = document.getElementById('addCategoryModal');
  const closeBtn = document.getElementById('closeAddCategoryModal');
  const cancelBtn = document.getElementById('cancelAddCategory');
  const backdrop = document.getElementById('addCategoryModalBackdrop');

  function showModal() {
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.add('items-start');
    document.body.classList.add('overflow-hidden');
  }
  function hideModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  if (openBtn) openBtn.addEventListener('click', function () { showModal(); });
  if (closeBtn) closeBtn.addEventListener('click', function () { hideModal(); });
  if (cancelBtn) cancelBtn.addEventListener('click', function () { hideModal(); });
  if (backdrop) backdrop.addEventListener('click', function () { hideModal(); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideModal();
  });

  @if (old('name') || old('role_id') || old('description'))
    document.addEventListener('DOMContentLoaded', function () { showModal(); });
  @endif
})();