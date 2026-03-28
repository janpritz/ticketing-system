<!-- SweetAlert2 -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
    (function() {

        const createModal = document.getElementById('createRoleModal');

        const editModal = document.getElementById('editRoleModal');

        const openCreateBtn = document.getElementById('openCreateRoleBtn');



        function openModal(modal) {

            if (modal) modal.classList.remove('hidden');

        }



        function closeModal(modal) {

            if (modal) modal.classList.add('hidden');

        }



        // Open create

        if (openCreateBtn) openCreateBtn.addEventListener('click', () => openModal(createModal));



        // Close handlers

        document.querySelectorAll('[data-close="create-role"]').forEach(el => el.addEventListener('click', () =>

            closeModal(createModal)));

        document.querySelectorAll('[data-close="edit-role"]').forEach(el => el.addEventListener('click', () =>

            closeModal(editModal)));



        // Create form submission with SweetAlert

        const createForm = document.getElementById('createRoleForm');

        if (createForm) {

            createForm.addEventListener('submit', async function(e) {

                e.preventDefault();



                const submitBtn = document.getElementById('createRoleSubmit');

                const originalText = submitBtn.textContent;

                submitBtn.textContent = 'Saving...';

                submitBtn.disabled = true;



                try {

                    const formData = new FormData(this);

                    const response = await fetch(this.action, {

                        method: 'POST',

                        body: formData,

                        headers: {

                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')

                                .getAttribute('content'),

                            'X-Requested-With': 'XMLHttpRequest'

                        }

                    });



                    const data = await response.json();



                    if (response.ok && data.success) {

                        closeModal(createModal);

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'success',

                            title: data.message || 'Role created successfully',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        }).then(() => {

                            window.location.reload();

                        });

                    } else {

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'error',

                            title: data.message || data.errors?.name?.[0] ||

                                'Failed to create role',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        });

                    }

                } catch (error) {

                    Swal.fire({

                        toast: true,

                        position: 'top-end',

                        icon: 'error',

                        title: 'An error occurred',

                        showConfirmButton: false,

                        timer: 3000,

                        timerProgressBar: true

                    });

                } finally {

                    submitBtn.textContent = originalText;

                    submitBtn.disabled = false;

                }

            });

        }



        // Edit form submission with SweetAlert

        const editForm = document.getElementById('editRoleForm');

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

                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')

                                .getAttribute('content'),

                            'X-Requested-With': 'XMLHttpRequest'

                        }

                    });



                    const data = await response.json();



                    if (response.ok && data.success) {

                        closeModal(editModal);

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'success',

                            title: data.message || 'Role updated successfully',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        }).then(() => {

                            window.location.reload();

                        });

                    } else {

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'error',

                            title: data.message || data.errors?.name?.[0] ||

                                'Failed to update role',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        });

                    }

                } catch (error) {

                    Swal.fire({

                        toast: true,

                        position: 'top-end',

                        icon: 'error',

                        title: 'An error occurred',

                        showConfirmButton: false,

                        timer: 3000,

                        timerProgressBar: true

                    });

                } finally {

                    submitBtn.textContent = originalText;

                    submitBtn.disabled = false;

                }

            });

        }



        // Edit buttons

        document.querySelectorAll('.openEditRoleBtn').forEach(btn => {

            btn.addEventListener('click', () => {

                const id = btn.getAttribute('data-id');

                const name = btn.getAttribute('data-name') || '';

                const description = btn.getAttribute('data-description') || '';

                const departmentId = btn.getAttribute('data-department-id') || '';



                // populate form

                document.getElementById('edit_role_id').value = id;

                document.getElementById('edit_role_name').value = name;

                document.getElementById('edit_role_description').value = description;

                document.getElementById('edit_role_department').value = departmentId;



                // set form action

                const form = document.getElementById('editRoleForm');

                form.action = "{{ url('/admin/roles') }}/" + id;

                openModal(editModal);

            });

        });



        // Intercept delete buttons and use SweetAlert confirmation

        document.querySelectorAll('.deleteRoleBtn').forEach(btn => {

            btn.addEventListener('click', async function(e) {

                const id = this.getAttribute('data-id');

                const name = this.getAttribute('data-name') || 'this role';

                const roleRow = this.closest('tr');



                const result = await Swal.fire({

                    title: 'Delete role?',

                    text: 'This will unassign the role from any users. This action cannot be undone.',

                    icon: 'warning',

                    showCancelButton: true,

                    confirmButtonText: 'Delete',

                    cancelButtonText: 'Cancel',

                    confirmButtonColor: '#d33'

                });



                if (!result.isConfirmed) return;



                try {

                    const response = await fetch(`/admin/roles/${id}`, {

                        method: 'DELETE',

                        headers: {

                            'X-CSRF-TOKEN': document.querySelector(

                                'meta[name="csrf-token"]').getAttribute('content'),

                            'X-Requested-With': 'XMLHttpRequest',

                            'Content-Type': 'application/json'

                        }

                    });



                    const data = await response.json();



                    if (response.ok && data.success) {

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'success',

                            title: data.message || 'Role deleted successfully',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        });

                        // Remove the row

                        if (roleRow) roleRow.remove();

                    } else {

                        Swal.fire({

                            toast: true,

                            position: 'top-end',

                            icon: 'error',

                            title: data.message || 'Failed to delete role',

                            showConfirmButton: false,

                            timer: 3000,

                            timerProgressBar: true

                        });

                    }

                } catch (error) {

                    Swal.fire({

                        toast: true,

                        position: 'top-end',

                        icon: 'error',

                        title: 'An error occurred',

                        showConfirmButton: false,

                        timer: 3000,

                        timerProgressBar: true

                    });

                }

            });

        });



        // Success toasts are now handled by AJAX responses



    })();
</script>
