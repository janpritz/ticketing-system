(function(){
  const rolesContainer = document.getElementById('rolesContainer');
  const addRoleBtn = document.getElementById('addRoleBtn');

  window.updateRemoveButtons = function() {
    const rows = rolesContainer.querySelectorAll('.role-row');
    rows.forEach((row) => {
      const removeBtn = row.querySelector('.remove-role-btn');
      rows.length > 1 ? removeBtn.classList.remove('hidden') : removeBtn.classList.add('hidden');
    });
  };

  window.addRoleField = function() {
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 role-row';
    div.innerHTML = `
      <input type="text" name="roles[]" placeholder="Role name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
      <button type="button" class="remove-role-btn text-red-500 hover:text-red-700 p-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
      </button>`;
    
    rolesContainer.appendChild(div);
    div.querySelector('.remove-role-btn').addEventListener('click', () => {
      div.remove();
      updateRemoveButtons();
    });
    updateRemoveButtons();
  };

  // Initialize existing rows
  document.querySelectorAll('.remove-role-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      this.closest('.role-row').remove();
      updateRemoveButtons();
    });
  });

  if (addRoleBtn) addRoleBtn.addEventListener('click', addRoleField);
})();