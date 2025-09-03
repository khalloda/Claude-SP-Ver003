<?php
/**
 * File: app/views/clients/index.php
 * Purpose: Clients listing page with search and filters
 * Layout: Uses app layout with responsive design
 */

$page_title = $page_title ?? t('nav.clients');
$active_nav = 'clients';
ob_start();

// Get current user from session or passed data
$currentUser = $current_user ?? ($_SESSION['user'] ?? null);
$canCreate = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'sales']);
$canEdit = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager', 'sales']);
$canDelete = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-users me-2"></i><?= t('nav.clients') ?>
            <span class="badge bg-secondary ms-2"><?= count($clients) ?></span>
        </h1>
        <?php if ($canCreate): ?>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/clients/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?= t('clients.add_client') ?>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/clients" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label"><?= t('common.search') ?></label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?= htmlspecialchars($search ?? '') ?>" 
                           placeholder="<?= t('clients.search_placeholder') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label"><?= t('common.status') ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value=""><?= t('common.all_statuses') ?></option>
                        <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>><?= t('common.active') ?></option>
                        <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>><?= t('common.inactive') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label"><?= t('clients.type') ?></label>
                    <select class="form-select" id="type" name="type">
                        <option value=""><?= t('common.all_types') ?></option>
                        <option value="individual" <?= ($type ?? '') === 'individual' ? 'selected' : '' ?>><?= t('clients.type.individual') ?></option>
                        <option value="company" <?= ($type ?? '') === 'company' ? 'selected' : '' ?>><?= t('clients.type.company') ?></option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="country" class="form-label"><?= t('clients.country') ?></label>
                    <select class="form-select" id="country" name="country">
                        <option value=""><?= t('common.all_countries') ?></option>
                        <?php foreach ($countries ?? [] as $country): ?>
                        <option value="<?= $country ?>" <?= ($country_filter ?? '') === $country ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-filter"></i> <?= t('common.filter') ?>
                    </button>
                    <a href="/clients" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Clients Grid/List -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($clients)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4><?= t('clients.no_clients_found') ?></h4>
                    <p class="text-muted"><?= t('clients.no_clients_desc') ?></p>
                    <?php if ($canCreate): ?>
                    <a href="/clients/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?= t('clients.add_first_client') ?>
                    </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($clients as $client): ?>
                    <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 client-card">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="user-avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                        <?php if ($client->type === 'company'): ?>
                                            <i class="fas fa-building"></i>
                                        <?php else: ?>
                                            <?= strtoupper(substr($client->name ?? '', 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <?php 
                                                    $clientId = $client->attributes['id'] ?? $client->id ?? null;
                                                    if ($clientId && $clientId > 0): 
                                                    ?>
                                                        <a href="/clients/<?= $clientId ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($client->display_name ?? $client->company_name ?? '') ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($client->display_name ?? $client->company_name ?? 'Unknown Client') ?>
                                                    <?php endif; ?>
                                                </h6>
                                                <div class="small text-muted mb-2">
                                                    <?php if ($client->is_active): ?>
                                                        <span class="badge bg-success"><?= t('common.active') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary"><?= t('common.inactive') ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php 
                                                    $clientId = $client->attributes['id'] ?? $client->id ?? null;
                                                    if ($clientId && $clientId > 0): 
                                                    ?>
                                                    <li><a class="dropdown-item" href="/clients/<?= $clientId ?>">
                                                        <i class="fas fa-eye me-2"></i><?= t('common.view') ?>
                                                    </a></li>
                                                    <?php if ($canEdit): ?>
                                                    <li><a class="dropdown-item" href="/clients/<?= $clientId ?>/edit">
                                                        <i class="fas fa-edit me-2"></i><?= t('common.edit') ?>
                                                    </a></li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item" href="/quotes/create?client_id=<?= $client->id ?>">
                                                        <i class="fas fa-file-alt me-2"></i><?= t('quotes.create_quote') ?>
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/sales-orders/create?client_id=<?= $client->id ?>">
                                                        <i class="fas fa-shopping-cart me-2"></i><?= t('sales_orders.create_order') ?>
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="/invoices/create?client_id=<?= $client->id ?>">
                                                        <i class="fas fa-file-invoice me-2"></i><?= t('invoices.create_invoice') ?>
                                                    </a></li>
                                                    <?php if ($canDelete): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteClient(<?= $client->id ?>, '<?= htmlspecialchars($client->display_name ?? $client->company_name ?? 'Unknown') ?>')">
                                                        <i class="fas fa-trash me-2"></i><?= t('common.delete') ?>
                                                    </a></li>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                    <li><span class="dropdown-item-text text-muted">
                                                        <i class="fas fa-exclamation-triangle me-2"></i><?= t('messages.error.invalid_client_data') ?>
                                                    </span></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <?php if ($client->email): ?>
                                        <div class="small mb-2">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <a href="mailto:<?= htmlspecialchars($client->email ?? '') ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($client->email ?? '') ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($client->phone): ?>
                                        <div class="small mb-2">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <a href="tel:<?= htmlspecialchars($client->phone ?? '') ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($client->phone ?? '') ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($client->city || $client->country): ?>
                                        <div class="small mb-3">
                                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                            <?= htmlspecialchars(trim(($client->city ?? '') . ', ' . ($client->country ?? ''), ', ')) ?>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Client Stats -->
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="small">
                                                    <div class="fw-bold text-primary"><?= $client->quotes_count ?? 0 ?></div>
                                                    <div class="text-muted"><?= t('nav.quotes') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small">
                                                    <div class="fw-bold text-success"><?= $client->orders_count ?? 0 ?></div>
                                                    <div class="text-muted"><?= t('nav.sales_orders') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small">
                                                    <div class="fw-bold text-info"><?= number_format($client->total_value ?? 0, 0) ?></div>
                                                    <div class="text-muted"><?= t('clients.total_value') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                <nav aria-label="<?= t('common.pagination') ?>" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/clients?page=1<?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.first') ?>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/clients?page=<?= $pagination['current_page'] - 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.previous') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                        <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="/clients?page=<?= $i ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="/clients?page=<?= $pagination['current_page'] + 1 ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.next') ?>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/clients?page=<?= $pagination['total_pages'] ?><?= !empty($query_string) ? '&' . $query_string : '' ?>">
                                <?= t('pagination.last') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.client-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.client-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.user-avatar {
    font-size: 1.2rem;
    font-weight: 600;
}
</style>

<script>
function deleteClient(clientId, clientName) {
    if (confirm('<?= t('clients.confirm_delete') ?>'.replace(':name', clientName))) {
        fetch(`/clients/${clientId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                location.reload();
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', '<?= t('messages.error.general') ?>');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>