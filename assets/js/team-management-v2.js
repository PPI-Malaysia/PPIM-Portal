// Team Management V2 - Enhanced JavaScript
// Handles Periods, Departments, and Members

// Use direct path for PHP built-in server compatibility (no .htaccess rewrite)
const API_BASE = 'assets/php/API/team-v2.php';

// Helper function to build API URL with query parameters (for PHP built-in server)
function apiUrl(resource, id = null, subresource = null, subid = null) {
    let url = `${API_BASE}?resource=${encodeURIComponent(resource)}`;
    if (id) url += `&id=${encodeURIComponent(id)}`;
    if (subresource) url += `&subresource=${encodeURIComponent(subresource)}`;
    if (subid) url += `&subid=${encodeURIComponent(subid)}`;
    return url;
}

// Position levels configuration
const POSITION_LEVELS = {
    core: {
        'ketua_umum': 'Ketua Umum',
        'wakil_ketua': 'Wakil Ketua',
        'sekretaris': 'Sekretaris Umum',
        'wakil_sekretaris': 'Wakil Sekretaris',
        'bendahara': 'Bendahara Umum',
        'wakil_bendahara': 'Wakil Bendahara'
    },
    biro: {
        'kepala_biro': 'Kepala Biro',
        'wakil_kepala_biro': 'Wakil Kepala Biro',
        'staff': 'Staff'
    },
    dept: {
        'kepala_dept': 'Kepala Departemen',
        'wakil_kepala_dept': 'Wakil Kepala Departemen',
        'staff': 'Staff'
    }
};

// ============================================================
// INITIALIZATION
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize forms
    initPeriodForm();
    initDepartmentForm();
    initMemberForm();
    
    // Initialize sortable for departments
    initDepartmentSortable();
    
    // Initialize sortable for members
    initMemberSortable();
    
    // Initialize icon picker
    initIconPicker();
    
    // Initialize color picker sync
    initColorPicker();
    
    // Initialize position level dropdown
    updatePositionLevels();
    
    // Image preview
    initImagePreview();
});

// ============================================================
// PERIOD MANAGEMENT
// ============================================================

function openPeriodModal(data = null) {
    const modal = new bootstrap.Modal(document.getElementById('periodModal'));
    const form = document.getElementById('periodForm');
    form.reset();
    
    if (data) {
        document.getElementById('periodModalTitle').textContent = 'Edit Period';
        document.getElementById('periodId').value = data.id;
        document.getElementById('periodName').value = data.name || '';
        document.getElementById('periodSlug').value = data.slug || '';
        document.getElementById('periodStartDate').value = data.startDate || '';
        document.getElementById('periodEndDate').value = data.endDate || '';
        document.getElementById('periodTheme').value = data.theme || '';
        document.getElementById('periodDescription').value = data.description || '';
        document.getElementById('periodIsActive').checked = data.isActive || false;
    } else {
        document.getElementById('periodModalTitle').textContent = 'Add Period';
        document.getElementById('periodId').value = '';
    }
    
    modal.show();
}

function initPeriodForm() {
    const form = document.getElementById('periodForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('periodId').value;
        const data = {
            name: document.getElementById('periodName').value,
            slug: document.getElementById('periodSlug').value,
            startDate: document.getElementById('periodStartDate').value,
            endDate: document.getElementById('periodEndDate').value,
            theme: document.getElementById('periodTheme').value,
            description: document.getElementById('periodDescription').value,
            isActive: document.getElementById('periodIsActive').checked
        };
        
        try {
            const url = id ? apiUrl('periods', id) : apiUrl('periods');
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message || 'Period saved successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('periodModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.error?.message || 'Failed to save period', 'error');
            }
        } catch (error) {
            showToast('Request failed', 'error');
            console.error(error);
        }
    });
}

async function editPeriod(id) {
    try {
        const response = await fetch(apiUrl('periods', id));
        const result = await response.json();
        
        if (result.success) {
            openPeriodModal(result.data);
        } else {
            showToast('Failed to fetch period', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

async function activatePeriod(id) {
    if (!confirm('Set this period as active? This will deactivate all other periods.')) return;
    
    try {
        const response = await fetch(apiUrl('periods', id, 'activate'), {
            method: 'PUT'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Period activated', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error?.message || 'Failed to activate period', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

async function deletePeriod(id) {
    if (!confirm('Delete this period? This action cannot be undone.')) return;
    
    try {
        const response = await fetch(apiUrl('periods', id), {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Period deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error?.message || 'Failed to delete period', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

// ============================================================
// DEPARTMENT MANAGEMENT
// ============================================================

function openDepartmentModal(data = null) {
    const modal = new bootstrap.Modal(document.getElementById('departmentModal'));
    const form = document.getElementById('departmentForm');
    form.reset();
    
    // Reset icon selection
    document.querySelectorAll('.icon-picker-item').forEach(item => item.classList.remove('selected'));
    document.getElementById('departmentIcon').value = '';
    
    if (data) {
        document.getElementById('departmentModalTitle').textContent = 'Edit Department';
        document.getElementById('departmentId').value = data.id;
        document.getElementById('departmentName').value = data.name || '';
        document.getElementById('departmentShortName').value = data.shortName || '';
        document.getElementById('departmentDescription').value = data.description || '';
        document.getElementById('departmentIsBiro').value = data.isBiro ? '1' : '0';
        document.getElementById('departmentColor').value = data.color || '#0d6efd';
        document.getElementById('departmentColorText').value = data.color || '#0d6efd';
        document.getElementById('departmentIsActive').checked = data.isActive !== false;
        
        if (data.icon) {
            document.getElementById('departmentIcon').value = data.icon;
            const iconItem = document.querySelector(`.icon-picker-item[data-icon="${data.icon}"]`);
            if (iconItem) iconItem.classList.add('selected');
        }
    } else {
        document.getElementById('departmentModalTitle').textContent = 'Add Department';
        document.getElementById('departmentId').value = '';
        document.getElementById('departmentIsActive').checked = true;
    }
    
    modal.show();
}

function initDepartmentForm() {
    const form = document.getElementById('departmentForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('departmentId').value;
        const data = {
            name: document.getElementById('departmentName').value,
            shortName: document.getElementById('departmentShortName').value,
            description: document.getElementById('departmentDescription').value,
            isBiro: document.getElementById('departmentIsBiro').value === '1',
            color: document.getElementById('departmentColor').value,
            icon: document.getElementById('departmentIcon').value,
            isActive: document.getElementById('departmentIsActive').checked
        };
        
        try {
            const url = id ? apiUrl('departments', id) : apiUrl('departments');
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast(result.message || 'Department saved successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('departmentModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.error?.message || 'Failed to save department', 'error');
            }
        } catch (error) {
            showToast('Request failed', 'error');
            console.error(error);
        }
    });
}

async function editDepartment(id) {
    try {
        const response = await fetch(apiUrl('departments', id));
        const result = await response.json();
        
        if (result.success) {
            openDepartmentModal(result.data);
        } else {
            showToast('Failed to fetch department', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

async function deleteDepartment(id) {
    if (!confirm('Delete this department? Members will be unassigned from this department.')) return;
    
    try {
        const response = await fetch(apiUrl('departments', id), {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Department deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error?.message || 'Failed to delete department', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

function initDepartmentSortable() {
    const container = document.getElementById('departmentsList');
    if (!container) return;
    
    new Sortable(container, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.dept-card',
        onEnd: async function(evt) {
            const items = container.querySelectorAll('[data-id]');
            const order = {};
            items.forEach((item, index) => {
                order[item.dataset.id] = index + 1;
            });
            
            try {
                const response = await fetch(apiUrl('departments', 'reorder'), {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order })
                });
                const result = await response.json();
                
                if (result.success) {
                    showToast('Order updated', 'success');
                } else {
                    showToast('Failed to update order', 'error');
                }
            } catch (error) {
                showToast('Request failed', 'error');
            }
        }
    });
}

// ============================================================
// MEMBER MANAGEMENT
// ============================================================

function openMemberModal(data = null) {
    const modal = new bootstrap.Modal(document.getElementById('memberModal'));
    const form = document.getElementById('memberForm');
    form.reset();
    
    // Reset photo preview
    document.getElementById('memberPhotoPreview').src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Crect fill='%23e9ecef' width='120' height='120'/%3E%3Ctext x='50%25' y='50%25' fill='%236c757d' text-anchor='middle' dy='.3em'%3EPhoto%3C/text%3E%3C/svg%3E";
    
    if (data) {
        document.getElementById('memberModalTitle').textContent = 'Edit Member';
        document.getElementById('memberId').value = data.id;
        document.getElementById('memberName').value = data.name || '';
        document.getElementById('memberPeriodId').value = data.periodId || '';
        document.getElementById('memberDepartmentId').value = data.departmentId || '';
        document.getElementById('memberPosition').value = data.position || '';
        document.getElementById('memberBio').value = data.bio || '';
        document.getElementById('memberUniversity').value = data.university || '';
        document.getElementById('memberMajor').value = data.major || '';
        document.getElementById('memberEmail').value = data.email || '';
        document.getElementById('memberPhone').value = data.phone || '';
        document.getElementById('memberLinkedin').value = data.linkedin || '';
        document.getElementById('memberInstagram').value = data.instagram || '';
        document.getElementById('memberDisplayOrder').value = data.displayOrder || 0;
        document.getElementById('memberIsActive').checked = data.isActive !== false;
        
        if (data.imageUrl) {
            document.getElementById('memberPhotoPreview').src = data.imageUrl;
        }
        
        // Update position levels based on department
        updatePositionLevels();
        
        // Set position level after options are populated
        setTimeout(() => {
            document.getElementById('memberPositionLevel').value = data.positionLevel || '';
        }, 100);
    } else {
        document.getElementById('memberModalTitle').textContent = 'Add Member';
        document.getElementById('memberId').value = '';
        document.getElementById('memberIsActive').checked = true;
        updatePositionLevels();
    }
    
    modal.show();
}

function initMemberForm() {
    const form = document.getElementById('memberForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const id = document.getElementById('memberId').value;
        const formData = new FormData();
        
        // Add all fields
        formData.append('periodId', document.getElementById('memberPeriodId').value);
        formData.append('departmentId', document.getElementById('memberDepartmentId').value || '');
        formData.append('name', document.getElementById('memberName').value);
        formData.append('position', document.getElementById('memberPosition').value);
        formData.append('positionLevel', document.getElementById('memberPositionLevel').value);
        formData.append('bio', document.getElementById('memberBio').value);
        formData.append('university', document.getElementById('memberUniversity').value);
        formData.append('major', document.getElementById('memberMajor').value);
        formData.append('email', document.getElementById('memberEmail').value);
        formData.append('phone', document.getElementById('memberPhone').value);
        formData.append('linkedin', document.getElementById('memberLinkedin').value);
        formData.append('instagram', document.getElementById('memberInstagram').value);
        formData.append('displayOrder', document.getElementById('memberDisplayOrder').value);
        formData.append('isActive', document.getElementById('memberIsActive').checked ? '1' : '0');
        
        // Add image if selected
        const imageInput = document.getElementById('memberImage');
        if (imageInput.files.length > 0) {
            formData.append('image', imageInput.files[0]);
        }
        
        try {
            let url, method;
            if (id) {
                // For update with image, use POST with _method override
                // For update without image, use PUT with JSON
                const hasNewImage = imageInput.files.length > 0;
                
                if (hasNewImage) {
                    // Use POST with FormData for file upload, add _method=PUT for override
                    url = apiUrl('members', id);
                    formData.append('_method', 'PUT');
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast(result.message || 'Member updated successfully', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(result.error?.message || 'Failed to update member', 'error');
                    }
                } else {
                    // No image change, use regular PUT with JSON
                    url = apiUrl('members', id);
                    method = 'PUT';
                    
                    const data = {};
                    formData.forEach((value, key) => {
                        if (key !== 'image') {
                            data[key] = value;
                        }
                    });
                    
                    const response = await fetch(url, {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast(result.message || 'Member updated successfully', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast(result.error?.message || 'Failed to update member', 'error');
                    }
                }
            } else {
                url = apiUrl('members');
                method = 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Member created successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('memberModal')).hide();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.error?.message || 'Failed to create member', 'error');
                }
            }
        } catch (error) {
            showToast('Request failed', 'error');
            console.error(error);
        }
    });
}

async function viewMember(id) {
    try {
        const response = await fetch(apiUrl('members', id));
        const result = await response.json();
        
        if (result.success) {
            const m = result.data;
            const content = `
                <div class="text-center mb-3">
                    ${m.imageUrl ? 
                        `<img src="${m.imageUrl}" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">` :
                        `<div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;"><i class="ti ti-user fs-1"></i></div>`
                    }
                </div>
                <h5 class="text-center">${escapeHtml(m.name)}</h5>
                <p class="text-center text-muted">${escapeHtml(m.position)}</p>
                <hr>
                <table class="table table-sm">
                    <tr><th width="40%">Period</th><td>${escapeHtml(m.periodName || '-')}</td></tr>
                    <tr><th>Department</th><td>${escapeHtml(m.departmentName || 'Core Team')}</td></tr>
                    <tr><th>Position Level</th><td>${escapeHtml(m.positionLevelLabel || '-')}</td></tr>
                    <tr><th>University</th><td>${escapeHtml(m.university || '-')}</td></tr>
                    <tr><th>Major</th><td>${escapeHtml(m.major || '-')}</td></tr>
                    <tr><th>Email</th><td>${m.email ? `<a href="mailto:${escapeHtml(m.email)}">${escapeHtml(m.email)}</a>` : '-'}</td></tr>
                    <tr><th>Phone</th><td>${escapeHtml(m.phone || '-')}</td></tr>
                    ${m.linkedin ? `<tr><th>LinkedIn</th><td><a href="${escapeHtml(m.linkedin)}" target="_blank">Profile</a></td></tr>` : ''}
                    ${m.instagram ? `<tr><th>Instagram</th><td>@${escapeHtml(m.instagram)}</td></tr>` : ''}
                </table>
                ${m.bio ? `<hr><h6>Bio</h6><p>${escapeHtml(m.bio)}</p>` : ''}
            `;
            document.getElementById('viewMemberContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewMemberModal')).show();
        } else {
            showToast('Failed to fetch member', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

async function editMember(id) {
    try {
        const response = await fetch(apiUrl('members', id));
        const result = await response.json();
        
        if (result.success) {
            openMemberModal(result.data);
        } else {
            showToast('Failed to fetch member', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

async function deleteMember(id) {
    if (!confirm('Delete this member? This action cannot be undone.')) return;
    
    try {
        const response = await fetch(apiUrl('members', id), {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('Member deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(result.error?.message || 'Failed to delete member', 'error');
        }
    } catch (error) {
        showToast('Request failed', 'error');
    }
}

function updatePositionLevels() {
    const deptSelect = document.getElementById('memberDepartmentId');
    const levelSelect = document.getElementById('memberPositionLevel');
    if (!deptSelect || !levelSelect) return;
    
    const deptId = deptSelect.value;
    const selectedOption = deptSelect.options[deptSelect.selectedIndex];
    const isBiro = selectedOption?.dataset?.isBiro === '1';
    
    let levels;
    if (!deptId) {
        levels = POSITION_LEVELS.core;
    } else if (isBiro) {
        levels = POSITION_LEVELS.biro;
    } else {
        levels = POSITION_LEVELS.dept;
    }
    
    // Clear and populate
    levelSelect.innerHTML = '<option value="">Select Position Level</option>';
    for (const [value, label] of Object.entries(levels)) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        levelSelect.appendChild(option);
    }
}

function initMemberSortable() {
    const tbody = document.getElementById('membersList');
    if (!tbody) return;
    
    new Sortable(tbody, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        handle: '.drag-handle',
        onEnd: async function(evt) {
            const rows = tbody.querySelectorAll('tr[data-id]');
            const order = {};
            rows.forEach((row, index) => {
                order[row.dataset.id] = index + 1;
            });
            
            try {
                const response = await fetch(apiUrl('members', 'reorder'), {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order })
                });
                const result = await response.json();
                
                if (result.success) {
                    showToast('Order updated', 'success');
                } else {
                    showToast('Failed to update order', 'error');
                }
            } catch (error) {
                showToast('Request failed', 'error');
            }
        }
    });
}

// ============================================================
// FILTER FUNCTIONS
// ============================================================

function applyMemberFilters() {
    const period = document.getElementById('periodFilter').value;
    const department = document.getElementById('departmentFilter').value;
    const search = document.getElementById('searchFilter')?.value;
    
    let url = 'team-management-v2.php?tab=members';
    if (period) url += `&period=${encodeURIComponent(period)}`;
    if (department) url += `&department=${encodeURIComponent(department)}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    
    window.location.href = url;
}

function clearMemberFilters() {
    window.location.href = 'team-management-v2.php?tab=members';
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

function initIconPicker() {
    const iconPicker = document.getElementById('iconPicker');
    if (!iconPicker) return;
    
    iconPicker.addEventListener('click', function(e) {
        const item = e.target.closest('.icon-picker-item');
        if (!item) return;
        
        // Deselect all
        iconPicker.querySelectorAll('.icon-picker-item').forEach(i => i.classList.remove('selected'));
        
        // Select clicked
        item.classList.add('selected');
        document.getElementById('departmentIcon').value = item.dataset.icon;
    });
}

function initColorPicker() {
    const colorInput = document.getElementById('departmentColor');
    const colorText = document.getElementById('departmentColorText');
    
    if (colorInput && colorText) {
        colorInput.addEventListener('input', function() {
            colorText.value = this.value;
        });
        
        colorText.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    }
}

function initImagePreview() {
    const imageInput = document.getElementById('memberImage');
    const preview = document.getElementById('memberPhotoPreview');
    
    if (imageInput && preview) {
        imageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
}

function showToast(message, type = 'info') {
    const bgColors = {
        success: '#198754',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#0d6efd'
    };
    
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: 'top',
            position: 'right',
            backgroundColor: bgColors[type] || bgColors.info,
            stopOnFocus: true
        }).showToast();
    } else {
        alert(message);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
