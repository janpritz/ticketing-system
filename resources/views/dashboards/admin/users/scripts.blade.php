<script>
    (function() {
        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
        const body = document.body;

        // SweetAlert2 flash toasts for success/errors
        try {
            if (typeof Swal !== 'undefined') {
                @if (session('status'))
                    Swal.fire({
                        icon: 'success',
                        title: {!! json_encode(session('status')) !!},
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                @endif
                @if ($errors->any())
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation error',
                        text: {!! json_encode($errors->first()) !!}
                    });
                @endif
            }
        } catch (e) {
            /* ignore */ }

        // Delete confirmation via SweetAlert2 (fallback to native confirm if unavailable)
        document.addEventListener('DOMContentLoaded', function() {
            $$('form.deleteUserForm').forEach((form) => {
                form.addEventListener('submit', function(e) {
                    if (typeof Swal === 'undefined') {
                        if (!confirm('Delete this user? This action cannot be undone.')) {
                            e.preventDefault();
                        }
                        return;
                    }
                    e.preventDefault();
                    const name = form.getAttribute('data-user-name') || 'this user';
                    Swal.fire({
                        title: 'Delete ' + name + '?',
                        text: 'This will move the user to trash. You can restore them later.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });

        // Restore confirmation via SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            $$('form.restoreUserForm').forEach((form) => {
                form.addEventListener('submit', function(e) {
                    if (typeof Swal === 'undefined') {
                        if (!confirm('Restore this user?')) {
                            e.preventDefault();
                        }
                        return;
                    }
                    e.preventDefault();
                    const name = form.getAttribute('data-user-name') || 'this user';
                    Swal.fire({
                        title: 'Restore ' + name + '?',
                        text: 'This will restore the user account.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, restore',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });

        // Create modal elements
        const createModal = $('#createStaffModal');
        const createOpenBtn = $('#openCreateModalBtn');
        const createCloseEls = $$('[data-close="create"]', createModal || body);

        // Edit modal elements
        const editModal = $('#editStaffModal');
        const editCloseEls = $$('[data-close="edit"]', editModal || body);
        const updateTemplate = editModal ? editModal.getAttribute('data-update-template') : null;
        const editForm = $('#editStaffForm');
        const editId = $('#edit_user_id');
        const editName = $('#edit_name');
        const editEmail = $('#edit_email');
        const editEmailFeedback = $('#edit_email_feedback');
        const editRole = $('#edit_role');
        const editCategory = $('#edit_category');

        function openModal(modal) {
            if (!modal) return;
            modal.classList.remove('hidden');

            // Reset email validation state when opening create modal
            if (modal === createModal && createEmail && createEmailFeedback) {
                createEmailFeedback.innerHTML = '';
            }

            // Reset email validation state when opening edit modal
            if (modal === editModal && editEmail && editEmailFeedback) {
                editEmailFeedback.innerHTML = '';
            }
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.add('hidden');
        }

        // Open Create
        if (createOpenBtn) {
            createOpenBtn.addEventListener('click', () => {
                // Reset form fields
                const createForm = createModal.querySelector('form');
                if (createForm) {
                    createForm.reset();
                }

                // Reset department dropdown
                if (createDepartment) {
                    createDepartment.value = '';
                }

                // Disable and reset role dropdown
                if (createRole) {
                    createRole.disabled = true;
                    createRole.innerHTML =
                        '<option value="" disabled selected>Select a department first</option>';
                }

                // Hide and reset additional roles
                if (createAdditionalRolesContainer) {
                    createAdditionalRolesContainer.classList.add('hidden');
                    createAdditionalRolesContainer.innerHTML =
                        '<p class="text-sm text-slate-500">Loading roles...</p>';
                }
                if (toggleAdditionalRolesBtn) {
                    toggleAdditionalRolesBtn.innerHTML =
                        `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" /></svg> Add Additional Roles`;
                }

                openModal(createModal);
            });
        }

        // Mobile create button - open the same modal
        const createOpenMobileBtn = $('#openCreateModalBtnMobile');
        if (createOpenMobileBtn) {
            createOpenMobileBtn.addEventListener('click', () => {
                // Reset form fields
                const createForm = createModal.querySelector('form');
                if (createForm) {
                    createForm.reset();
                }

                // Reset department dropdown
                if (createDepartment) {
                    createDepartment.value = '';
                }

                // Disable and reset role dropdown
                if (createRole) {
                    createRole.disabled = true;
                    createRole.innerHTML =
                        '<option value="" disabled selected>Select a department first</option>';
                }

                // Hide and reset additional roles
                if (createAdditionalRolesContainer) {
                    createAdditionalRolesContainer.classList.add('hidden');
                    createAdditionalRolesContainer.innerHTML =
                        '<p class="text-sm text-slate-500">Loading roles...</p>';
                }
                if (toggleAdditionalRolesBtn) {
                    toggleAdditionalRolesBtn.innerHTML =
                        `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" /></svg> Add Additional Roles`;
                }

                openModal(createModal);
            });
        }

        // Close Create
        createCloseEls.forEach(el => el.addEventListener('click', () => closeModal(createModal)));


        // Close Edit
        editCloseEls.forEach(el => el.addEventListener('click', () => closeModal(editModal)));

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal(createModal);
                closeModal(editModal);
            }
        });

        // Auto-open modal after validation error (values read from hidden state element)
        const stateEl = document.getElementById('admin-users-state');
        const HAS_ERRORS = stateEl ? stateEl.getAttribute('data-has-errors') === '1' : false;
        const OLD_EDIT_ID = stateEl ? stateEl.getAttribute('data-old-edit-id') : null;
        const OLD_FORM_CONTEXT = stateEl ? stateEl.getAttribute('data-old-form-context') : null;
        const OLD_NAME = stateEl ? stateEl.getAttribute('data-old-name') : '';
        const OLD_EMAIL = stateEl ? stateEl.getAttribute('data-old-email') : '';
        const OLD_ROLE = stateEl ? stateEl.getAttribute('data-old-role') : '';
        const OLD_CATEGORY_ID = stateEl ? stateEl.getAttribute('data-old-category-id') : '';
        const OLD_PROFILE_PHOTO = stateEl ? stateEl.getAttribute('data-old-profile-photo') : '';

        if (HAS_ERRORS) {
            if (OLD_EDIT_ID) {
                if (editId) editId.value = OLD_EDIT_ID || '';
                if (editName) editName.value = OLD_NAME || '';
                if (editEmail) editEmail.value = OLD_EMAIL || '';
                if (editRole) editRole.value = OLD_ROLE || '';
                if (editCategory) editCategory.value = OLD_CATEGORY_ID || '';

                // Set email verification status badge
                if (editEmail) {
                    const emailVerificationStatus = $('#email_verification_status');
                    const emailVerifiedAt = btn.getAttribute('data-email-verified-at');

                    if (emailVerifiedAt === 'null' || !emailVerifiedAt) {
                        emailVerificationStatus.innerHTML = `
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                    Not Verified
                                </span>
                                <button type="button" class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-white px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50"
                                    onclick="resendVerificationEmail('${email}')">
                                   Resend
                                </button>
                            `;
                    } else {
                        emailVerificationStatus.innerHTML = `
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    Verified
                                </span>
                            `;
                    }
                }

                // Set profile photo
                const photoEl = $('#edit_profile_photo');
                if (photoEl) {
                    if (OLD_PROFILE_PHOTO) {
                        photoEl.src = '/storage/' + OLD_PROFILE_PHOTO;
                    } else {
                        photoEl.src = 'https://via.placeholder.com/80x80?text=No+Photo';
                    }
                }
                if (editForm && updateTemplate && OLD_EDIT_ID) {
                    editForm.setAttribute('action', updateTemplate.replace('__ID__', OLD_EDIT_ID));
                }
                // Load categories for the role
                if (editRole && editRole.value && editCategory) {
                    updateCategories(editCategory, editRole.value);
                }
                openModal(editModal);
            } else if (OLD_FORM_CONTEXT === 'create') {
                // If old create values exist, pre-load categories for the selected role and preserve selection
                if (createRole && createRole.value && createCategory) {
                    // Use the same OLD_CATEGORY_ID stored in the state element
                    updateCategories(createCategory, createRole.value, OLD_CATEGORY_ID);
                }
                openModal(createModal);
            }
        }

        // Mobile search UI (toggles mobile search bar)
        const mobileSearchToggle = $('#mobileSearchToggle');
        const mobileSearchArea = $('#mobileSearchArea');
        const qMobile = $('#q_mobile');
        const mobileSearchBtn = $('#mobileSearchBtn');
        const mobileClearSearch = $('#mobileClearSearch');

        if (mobileSearchToggle) {
            mobileSearchToggle.addEventListener('click', () => {
                if (mobileSearchArea) mobileSearchArea.classList.toggle('hidden');
                if (qMobile) qMobile.focus();
            });
        }

        if (mobileSearchBtn) {
            mobileSearchBtn.addEventListener('click', () => {
                const form = document.createElement('form');
                form.method = 'GET';
                form.action = "{{ route('admin.users.index') }}";
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'q';
                input.value = qMobile ? qMobile.value.trim() : '';
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            });
        }

        if (mobileClearSearch) {
            mobileClearSearch.addEventListener('click', () => {
                if (qMobile) qMobile.value = '';
                // navigate to the index without query
                window.location.href = "{{ route('admin.users.index') }}";
            });
        }

        // Conditional dropdowns for department-role
        function updateRoleForDepartment(selectElement, departmentId, selectedRoleId) {
            const select = selectElement; // This is the role select element

            if (!departmentId) {
                select.innerHTML = '<option value="" disabled selected>Select a department first</option>';
                return;
            }

            // Show loading indicator
            select.innerHTML = '<option value="" disabled selected>Loading roles...</option>';

            fetch(`/admin/roles/by-department/${departmentId}`)
                .then(response => response.json())
                .then(roles => {
                    if (roles.length === 0) {
                        select.innerHTML = '<option value="">No roles in this department</option>';
                        return;
                    }

                    let html = '<option value="" disabled selected>Select role</option>';
                    roles.forEach(role => {
                        const isSelected = selectedRoleId && selectedRoleId == role.id ? 'selected' :
                        '';
                        html += `<option value="${role.id}" ${isSelected}>${role.name}</option>`;
                    });
                    select.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error fetching roles:', error);
                    select.innerHTML = '<option value="">Error loading roles</option>';
                });
        }

        // Load all roles for additional roles section
        function loadAllRoles(containerId, selectedRoles, excludeRoleId) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const isEdit = containerId === 'edit_additional_roles_container';

            // Show loading indicator
            container.innerHTML = '<p class="text-sm text-slate-500">Loading roles...</p>';

            fetch('/admin/roles/all')
                .then(response => response.json())
                .then(roles => {
                    if (roles.length === 0) {
                        container.innerHTML = '<p class="text-sm text-slate-500">No roles available</p>';
                        return;
                    }

                    let html = '';
                    roles.forEach(role => {
                        // Skip if this role is the main selected role
                        if (excludeRoleId && role.id == excludeRoleId) {
                            return;
                        }
                        const isChecked = selectedRoles && selectedRoles.includes(role.id) ? 'checked' :
                            '';
                        const deptName = role.department ? role.department.name : 'No Department';
                        html += `
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="additional_roles[]" value="${role.id}" id="${isEdit ? 'edit' : 'create'}_additional_role_${role.id}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" ${isChecked}>
                                    <label for="${isEdit ? 'edit' : 'create'}_additional_role_${role.id}" class="text-sm text-slate-700">${role.name} <span class="text-xs text-slate-500">(${deptName})</span></label>
                                </div>
                            `;
                    });

                    if (html === '') {
                        container.innerHTML =
                            '<p class="text-sm text-slate-500">No additional roles available</p>';
                    } else {
                        container.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error fetching all roles:', error);
                    container.innerHTML = '<p class="text-sm text-red-500">Error loading roles</p>';
                });
        }

        // Initialize additional roles on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllRoles('create_additional_roles_container', null, null);
            loadAllRoles('edit_additional_roles_container', null, null);
        });

        // Create modal department change
        const createDepartment = $('#create_department');
        const createRole = $('#create_role');
        const createEmail = $('#create_email');
        const createEmailFeedback = $('#create_email_feedback');
        // Update email field UI based on validation result
        function updateEmailUI(result) {
            if (!createEmail || !createEmailFeedback) return;

            if (result.valid === null) {
                // Loading or empty
                if (result.message_type === 'loading') {
                    createEmailFeedback.innerHTML =
                        `<span class="text-slate-500 flex items-center gap-1"><svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>${result.message}</span>`;
                } else {
                    createEmailFeedback.innerHTML = '';
                }
                return;
            }

            if (result.valid === false) {
                // Invalid
                createEmailFeedback.innerHTML =
                    `<span class="text-red-600 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>${result.message}</span>`;
            } else {
                // Valid
                createEmailFeedback.innerHTML =
                    `<span class="text-green-600 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>${result.message}</span>`;
            }
        }

        // Email input event listener with debounce
        if (createEmail) {
            createEmail.addEventListener('input', function() {
                clearTimeout(emailCheckTimeout);
                const email = this.value.trim();

                if (!email) {
                    updateEmailUI({
                        valid: null,
                        message: '',
                        message_type: ''
                    });
                    return;
                }

                // Debounce - wait 500ms after typing stops
                emailCheckTimeout = setTimeout(() => {
                    validateEmail(email, updateEmailUI);
                }, 500);
            });

            // Also validate on blur (when user leaves the field)
            createEmail.addEventListener('blur', function() {
                clearTimeout(emailCheckTimeout);
                const email = this.value.trim();
                if (email) {
                    validateEmail(email, updateEmailUI);
                }
            });
        }

        // Email validation function for edit modal
        let editEmailCheckTimeout = null;

        // Update edit email field UI based on validation result
        function updateEditEmailUI(result) {
            if (!editEmail || !editEmailFeedback) return;

            if (result.valid === null) {
                // Loading or empty
                if (result.message_type === 'loading') {
                    editEmailFeedback.innerHTML =
                        `<span class="text-slate-500 flex items-center gap-1"><svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>${result.message}</span>`;
                } else {
                    editEmailFeedback.innerHTML = '';
                }
                return;
            }

            if (result.valid === false) {
                // Invalid
                editEmailFeedback.innerHTML =
                    `<span class="text-red-600 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>${result.message}</span>`;
            } else {
                // Valid
                editEmailFeedback.innerHTML =
                    `<span class="text-green-600 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>${result.message}</span>`;
            }
        }

        // Edit email input event listener with debounce
        if (editEmail) {
            editEmail.addEventListener('input', function() {
                clearTimeout(editEmailCheckTimeout);
                const email = this.value.trim();
                const userId = editId ? editId.value : null;

                if (!email) {
                    updateEditEmailUI({
                        valid: null,
                        message: '',
                        message_type: ''
                    });
                    return;
                }

                // Debounce - wait 500ms after typing stops
                editEmailCheckTimeout = setTimeout(() => {
                    validateEditEmail(email, userId, updateEditEmailUI);
                }, 500);
            });

            // Also validate on blur (when user leaves the field)
            editEmail.addEventListener('blur', function() {
                clearTimeout(editEmailCheckTimeout);
                const email = this.value.trim();
                const userId = editId ? editId.value : null;
                if (email) {
                    validateEditEmail(email, userId, updateEditEmailUI);
                }
            });
        }

        if (createDepartment && createRole) {
            createDepartment.addEventListener('change', () => {
                // Enable role dropdown when department is selected
                createRole.disabled = false;
                updateRoleForDepartment(createRole, createDepartment.value, null);
                // Reload additional roles, excluding the main selected role
                const selectedMainRoleId = createRole.value || null;
                // Get currently checked additional roles before reloading
                const checkedBoxes = createAdditionalRolesContainer ? createAdditionalRolesContainer
                    .querySelectorAll('input[type="checkbox"]:checked') : [];
                const checkedRoleIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
                loadAllRoles('create_additional_roles_container', checkedRoleIds, selectedMainRoleId);
            });

            // Also reload additional roles when main role changes
            createRole.addEventListener('change', () => {
                const selectedMainRoleId = createRole.value || null;
                // Get currently checked additional roles before reloading
                const checkedBoxes = createAdditionalRolesContainer ? createAdditionalRolesContainer
                    .querySelectorAll('input[type="checkbox"]:checked') : [];
                const checkedRoleIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
                loadAllRoles('create_additional_roles_container', checkedRoleIds, selectedMainRoleId);
            });
        }

        // Toggle additional roles visibility
        const toggleAdditionalRolesBtn = $('#toggle_additional_roles_btn');
        const createAdditionalRolesContainer = $('#create_additional_roles_container');
        if (toggleAdditionalRolesBtn && createAdditionalRolesContainer) {
            toggleAdditionalRolesBtn.addEventListener('click', () => {
                createAdditionalRolesContainer.classList.toggle('hidden');
                const isHidden = createAdditionalRolesContainer.classList.contains('hidden');

                // If showing, reload roles excluding the main selected role
                if (!isHidden && createRole) {
                    const selectedMainRoleId = createRole.value || null;
                    loadAllRoles('create_additional_roles_container', null, selectedMainRoleId);
                }

                toggleAdditionalRolesBtn.innerHTML = isHidden ?
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" /></svg> Add Additional Roles` :
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12" /></svg> Hide Additional Roles`;
            });
        }

        // Edit modal department change
        const editDepartment = $('#edit_department');
        if (editDepartment && editRole) {
            editDepartment.addEventListener('change', () => {
                // Enable role dropdown when department is selected
                editRole.disabled = false;
                updateRoleForDepartment(editRole, editDepartment.value, null);
                // Reload additional roles, excluding the main selected role
                const selectedMainRoleId = editRole.value || null;
                // Get currently checked additional roles before reloading
                const checkedBoxes = editAdditionalRolesContainer ? editAdditionalRolesContainer
                    .querySelectorAll('input[type="checkbox"]:checked') : [];
                const checkedRoleIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
                loadAllRoles('edit_additional_roles_container', checkedRoleIds, selectedMainRoleId);
            });

            // Also reload additional roles when main role changes
            editRole.addEventListener('change', () => {
                const selectedMainRoleId = editRole.value || null;
                // Get currently checked additional roles before reloading
                const checkedBoxes = editAdditionalRolesContainer ? editAdditionalRolesContainer
                    .querySelectorAll('input[type="checkbox"]:checked') : [];
                const checkedRoleIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
                loadAllRoles('edit_additional_roles_container', checkedRoleIds, selectedMainRoleId);
            });
        }

        // Toggle edit additional roles visibility
        const toggleEditAdditionalRolesBtn = $('#toggle_edit_additional_roles_btn');
        const editAdditionalRolesContainer = $('#edit_additional_roles_container');
        if (toggleEditAdditionalRolesBtn && editAdditionalRolesContainer) {
            toggleEditAdditionalRolesBtn.addEventListener('click', () => {
                editAdditionalRolesContainer.classList.toggle('hidden');
                const isHidden = editAdditionalRolesContainer.classList.contains('hidden');

                // If showing, reload roles excluding the main selected role
                if (!isHidden && editRole) {
                    const selectedMainRoleId = editRole.value || null;
                    // Get currently checked additional roles
                    const checkedBoxes = editAdditionalRolesContainer.querySelectorAll(
                        'input[type="checkbox"]:checked');
                    const checkedRoleIds = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
                    loadAllRoles('edit_additional_roles_container', checkedRoleIds, selectedMainRoleId);
                }

                toggleEditAdditionalRolesBtn.innerHTML = isHidden ?
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" /></svg> Add Additional Roles` :
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12" /></svg> Hide Additional Roles`;
            });
        }

        // Open Edit Modal
        document.querySelectorAll('.openEditModalBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name') || '';
                const email = btn.getAttribute('data-email') || '';
                const role = btn.getAttribute('data-role') || '';
                const category = btn.getAttribute('data-category') || '';
                const categoryId = btn.getAttribute('data-category-id') || '';
                const profilePhoto = btn.getAttribute('data-profile-photo') || '';

                if (editId) editId.value = id || '';
                if (editName) editName.value = name;
                if (editEmail) editEmail.value = email;
                if (editCategory) editCategory.value = categoryId || '';


                // Set email verification status badge
                if (editEmail) {
                    const emailVerificationStatus = $('#email_verification_status');
                    const emailVerifiedAt = btn.getAttribute('data-email-verified-at');

                    if (emailVerifiedAt === 'null' || !emailVerifiedAt) {
                        emailVerificationStatus.innerHTML = `
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                    Not Verified
                                </span>
                                <button type="button" class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-white px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50"
                                    onclick="resendVerificationEmail('${email}')">
                                    Resend
                                </button>
                            `;
                    } else {
                        emailVerificationStatus.innerHTML = `
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    Verified
                                </span>
                            `;
                    }
                }

                // Set profile photo
                const photoEl = $('#edit_profile_photo');
                if (photoEl) {
                    if (profilePhoto) {
                        photoEl.src = '/storage/' + profilePhoto;
                    } else {
                        photoEl.src =
                        'https://via.placeholder.com/80x80?text=No+Photo'; // Fallback placeholder
                    }
                }

                if (editForm && updateTemplate && id) {
                    editForm.setAttribute('action', updateTemplate.replace('__ID__', id));
                }

                // Load user's current roles and department
                if (id) {
                    fetch('/admin/users/' + id + '/roles')
                        .then(response => response.json())
                        .then(userData => {
                            // Set department dropdown
                            if (editDepartment) {
                                editDepartment.value = userData.department_id || '';
                            }

                            // Get primary role and additional roles from API response
                            const primaryRoleId = userData.primary_role_id;
                            const additionalRoleIds = userData.additional_role_ids || [];

                            // Load department role (dropdown - single select)
                            if (editDepartment && userData.department_id) {
                                // Enable role dropdown
                                editRole.disabled = false;
                                updateRoleForDepartment(editRole, userData.department_id,
                                    primaryRoleId);
                            }

                            // Load all roles for additional roles section (excluding primary role)
                            loadAllRoles('edit_additional_roles_container', additionalRoleIds,
                                primaryRoleId);
                        })
                        .catch(error => {
                            console.error('Error fetching user roles:', error);
                        });
                }

                // Load categories for the role (preserve selected)
                if (editRole && editRole.value && editCategory) {
                    updateCategories(editCategory, editRole.value, editCategory.value);
                }

                openModal(editModal);
            });
        });

        // Also for auto-open on validation errors
        if (HAS_ERRORS && OLD_EDIT_ID && editRole && editRole.value && editCategory) {
            updateCategories(editCategory, editRole.value, OLD_CATEGORY_ID);
        }

        // Ensure form submits integer category_id only (protect against legacy name values)
        function sanitizeCategorySelectBeforeSubmit(form) {
            const sel = form.querySelector('select[name="category_id"]');
            if (!sel) return;
            // If selected value is not an integer, clear it so server receives null
            if (!/^\d+$/.test(String(sel.value))) {
                sel.value = '';
            }
        }

        // Make resendVerificationEmail available in global scope
        window.resendVerificationEmail = function(email) {
            if (typeof Swal === 'undefined') {
                if (confirm('Resend verification email to ' + email + '?')) {
                    sendVerificationRequest(email);
                }
                return;
            }

            Swal.fire({
                title: 'Resend verification email?',
                text: 'This will send a new verification email to ' + email + '.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, resend',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendVerificationRequest(email);
                }
            });
        }

        function sendVerificationRequest(email) {
            // Find the resend button and add spinner
            const allButtons = document.querySelectorAll('button[onclick^="resendVerificationEmail"]');
            let resendButton = null;
            allButtons.forEach(btn => {
                if (btn.getAttribute('onclick').includes(email)) {
                    resendButton = btn;
                }
            });

            const originalHtml = resendButton ? resendButton.innerHTML : '';
            if (resendButton) {
                resendButton.disabled = true;
                resendButton.innerHTML = `
                        <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    `;
            }

            fetch('/admin/users/resend-verification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Verification email sent',
                            text: 'A new verification email has been sent to ' + email + '.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to send verification email.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(error => {
                    console.error('Error sending verification email:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to send verification email. Please try again.',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                })
                .finally(() => {
                    // Restore original button state
                    if (resendButton) {
                        resendButton.disabled = false;
                        resendButton.innerHTML = originalHtml;
                    }
                });
        }

        // Attach sanitizer to create/edit forms
        const createForm = createModal ? createModal.querySelector('form') : null;
        if (createForm) {
            createForm.addEventListener('submit', function() {
                sanitizeCategorySelectBeforeSubmit(createForm);
            });
        }
        if (editForm) {
            editForm.addEventListener('submit', function() {
                sanitizeCategorySelectBeforeSubmit(editForm);
            });
        }
    })();
</script>
