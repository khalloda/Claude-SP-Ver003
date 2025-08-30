<?php 
$title = 'Backup & Restore - MISP System';
$page = 'settings';
include_once '../layouts/app.php';
startContent();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Backup & Restore</h1>
        <p class="text-muted mb-0">Manage system backups and data recovery</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/settings" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
        <button type="button" class="btn btn-success" onclick="createBackup()">
            <i class="fas fa-plus"></i> Create New Backup
        </button>
    </div>
</div>

<div class="row">
    <!-- Backup Options -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog text-primary me-2"></i>
                    Backup Options
                </h5>
            </div>
            <div class="card-body">
                <form id="backupOptionsForm">
                    <div class="mb-3">
                        <label class="form-label">Backup Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="backup_type" value="full" checked>
                            <label class="form-check-label">
                                <strong>Full Backup</strong>
                                <br><small class="text-muted">Database + Files</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="backup_type" value="database">
                            <label class="form-check-label">
                                <strong>Database Only</strong>
                                <br><small class="text-muted">Tables and data</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="backup_type" value="files">
                            <label class="form-check-label">
                                <strong>Files Only</strong>
                                <br><small class="text-muted">Uploads and configs</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Compression</label>
                        <select class="form-select form-select-sm" name="compression">
                            <option value="gzip">GZIP (Recommended)</option>
                            <option value="zip">ZIP</option>
                            <option value="none">No Compression</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="exclude_logs" checked>
                        <label class="form-check-label">
                            Exclude log files
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="exclude_cache" checked>
                        <label class="form-check-label">
                            Exclude cache files
                        </label>
                    </div>
                </form>
                
                <hr>
                
                <div class="d-grid">
                    <button class="btn btn-primary" onclick="createCustomBackup()">
                        <i class="fas fa-save"></i> Create Custom Backup
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Scheduled Backups -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-warning me-2"></i>
                    Scheduled Backups
                </h5>
            </div>
            <div class="card-body">
                <form id="scheduleForm">
                    <div class="mb-3">
                        <label class="form-label">Frequency</label>
                        <select class="form-select" name="frequency">
                            <option value="disabled">Disabled</option>
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="time" class="form-control" name="backup_time" value="02:00">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keep Backups</label>
                        <select class="form-select" name="retention">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30" selected>30 days</option>
                            <option value="90">90 days</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-save"></i> Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Backup History -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-history text-info me-2"></i>
                    Backup History
                </h5>
                <div>
                    <button class="btn btn-outline-danger btn-sm" onclick="cleanupOldBackups()">
                        <i class="fas fa-trash"></i> Cleanup Old
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupHistory">
                            <tr>
                                <td>
                                    <strong>2025-08-29 02:00:15</strong>
                                    <br><small class="text-muted">Auto backup</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Full</span>
                                </td>
                                <td>45.2 MB</td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Complete
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="downloadBackup('backup_20250829_020015.sql.gz')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewBackupInfo('backup_20250829_020015')">
                                            <i class="fas fa-info"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteBackup('backup_20250829_020015')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>2025-08-28 14:30:42</strong>
                                    <br><small class="text-muted">Manual backup</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">Database</span>
                                </td>
                                <td>12.8 MB</td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Complete
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="downloadBackup('backup_20250828_143042.sql.gz')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewBackupInfo('backup_20250828_143042')">
                                            <i class="fas fa-info"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteBackup('backup_20250828_143042')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>2025-08-27 02:00:08</strong>
                                    <br><small class="text-muted">Auto backup</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Full</span>
                                </td>
                                <td>43.1 MB</td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Warning
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="downloadBackup('backup_20250827_020008.sql.gz')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewBackupInfo('backup_20250827_020008')">
                                            <i class="fas fa-info"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteBackup('backup_20250827_020008')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Restore Section -->
        <div class="card mt-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-dark me-2"></i>
                    <span class="text-dark">Restore from Backup</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> Restoring from backup will overwrite current data. Make sure to create a backup of current state before proceeding.
                </div>
                
                <form id="restoreForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Select Backup File</label>
                                <input type="file" class="form-control" name="backup_file" accept=".sql,.gz,.zip" required>
                                <div class="form-text">Supported formats: .sql, .sql.gz, .zip</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Restore Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="backup_current" checked>
                                    <label class="form-check-label">
                                        Backup current data first
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="verify_backup">
                                    <label class="form-check-label">
                                        Verify backup integrity
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" onclick="$('#restoreForm')[0].reset()">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-warning" onclick="confirmRestore()">
                            <i class="fas fa-upload"></i> Restore from Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Backup Info Modal -->
<div class="modal fade" id="backupInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Backup Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="backupInfoContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function createBackup() {
    if (confirm('Create a new full backup? This may take a few minutes.')) {
        showLoading('Creating backup...');
        
        fetch('/settings/backup/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                type: 'full',
                compression: 'gzip'
            })
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('Backup created successfully!', 'success');
                refreshBackupHistory();
                if (data.download_url) {
                    window.open(data.download_url, '_blank');
                }
            } else {
                showNotification('Error creating backup: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('Error creating backup: ' + error.message, 'danger');
        });
    }
}

function createCustomBackup() {
    const form = document.getElementById('backupOptionsForm');
    const formData = new FormData(form);
    
    const options = {
        type: formData.get('backup_type'),
        compression: formData.get('compression'),
        exclude_logs: formData.get('exclude_logs') === 'on',
        exclude_cache: formData.get('exclude_cache') === 'on'
    };
    
    showLoading('Creating custom backup...');
    
    fetch('/settings/backup/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(options)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showNotification('Custom backup created successfully!', 'success');
            refreshBackupHistory();
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            }
        } else {
            showNotification('Error creating backup: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showNotification('Error creating backup: ' + error.message, 'danger');
    });
}

function downloadBackup(filename) {
    window.open('/settings/backup/download/' + filename, '_blank');
}

function viewBackupInfo(backupId) {
    fetch('/settings/backup/info/' + backupId)
    .then(response => response.text())
    .then(html => {
        document.getElementById('backupInfoContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('backupInfoModal')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading backup information', 'danger');
    });
}

function deleteBackup(backupId) {
    if (confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
        fetch('/settings/backup/delete/' + backupId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Backup deleted successfully', 'success');
                refreshBackupHistory();
            } else {
                showNotification('Error deleting backup: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting backup', 'danger');
        });
    }
}

function cleanupOldBackups() {
    if (confirm('Delete all backups older than 30 days?')) {
        fetch('/settings/backup/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ days: 30 })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                refreshBackupHistory();
            } else {
                showNotification('Error cleaning up backups: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error cleaning up backups', 'danger');
        });
    }
}

function confirmRestore() {
    const fileInput = document.querySelector('input[name="backup_file"]');
    if (!fileInput.files[0]) {
        showNotification('Please select a backup file', 'warning');
        return;
    }
    
    const confirmMessage = 'Are you sure you want to restore from this backup?\n\n' +
                          'This will:\n' +
                          '• Overwrite all current data\n' +
                          '• Replace the entire database\n' +
                          '• Log out all users\n\n' +
                          'This action CANNOT be undone!';
    
    if (confirm(confirmMessage)) {
        const form = document.getElementById('restoreForm');
        const formData = new FormData(form);
        
        showLoading('Restoring backup... This may take several minutes.');
        
        fetch('/settings/backup/restore', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                showNotification('Backup restored successfully! Please login again.', 'success');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
            } else {
                showNotification('Error restoring backup: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showNotification('Error restoring backup: ' + error.message, 'danger');
        });
    }
}

function refreshBackupHistory() {
    fetch('/settings/backup/history')
    .then(response => response.text())
    .then(html => {
        document.getElementById('backupHistory').innerHTML = html;
    })
    .catch(error => {
        console.error('Error refreshing backup history:', error);
    });
}

// Save schedule form
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const schedule = Object.fromEntries(formData.entries());
    
    fetch('/settings/backup/schedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(schedule)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Backup schedule saved successfully', 'success');
        } else {
            showNotification('Error saving schedule: ' + data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving schedule', 'danger');
    });
});

// Utility functions
function showLoading(message) {
    // Implementation depends on your loading system
    console.log('Loading:', message);
}

function hideLoading() {
    // Implementation depends on your loading system
    console.log('Loading complete');
}

function showNotification(message, type) {
    // Create toast notification
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}
</script>

<?php endContent(); ?>