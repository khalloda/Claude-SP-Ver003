<?php
/**
 * File: app/views/products/stock.php
 * Purpose: Product stock management page with adjustment functionality
 * Layout: Uses app layout with stock management interface
 */

$page_title = $page_title ?? t('products.manage_stock');
$active_nav = 'products';
ob_start();

// Get current user from passed data
$currentUser = $current_user ?? null;
$canManageStock = $currentUser && in_array($currentUser['role'] ?? '', ['admin', 'manager']);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-boxes me-2"></i><?= t('products.manage_stock') ?>
            <small class="text-muted ms-2"><?= htmlspecialchars($product->name) ?></small>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/products/<?= $product->id ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> <?= t('common.back') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Stock Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-warehouse me-2"></i><?= t('products.current_stock') ?></h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 <?= $product->stock_quantity <= ($product->min_stock_level ?? 0) ? 'text-danger' : 'text-success' ?>">
                        <?= $product->stock_quantity ?? 0 ?>
                    </div>
                    <div class="text-muted"><?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?></div>
                    
                    <?php if ($product->stock_quantity <= ($product->min_stock_level ?? 0)): ?>
                    <div class="mt-2">
                        <span class="badge bg-danger"><?= t('products.low_stock') ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <?= t('products.min_level') ?>: <?= $product->min_stock_level ?? 0 ?> <?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Adjustment Form -->
        <div class="col-md-8">
            <?php if ($canManageStock): ?>
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-edit me-2"></i><?= t('products.adjust_stock') ?></h5>
                </div>
                <div class="card-body">
                    <form id="stockAdjustmentForm" method="POST" action="/products/<?= $product->id ?>/stock">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? '' ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="adjustment" class="form-label"><?= t('products.adjustment_quantity') ?></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="adjustment" name="adjustment" required>
                                        <span class="input-group-text"><?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?></span>
                                    </div>
                                    <div class="form-text"><?= t('products.adjustment_help') ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reason" class="form-label"><?= t('products.adjustment_reason') ?></label>
                                    <select class="form-select" id="reason" name="reason">
                                        <option value="manual"><?= t('products.manual_adjustment') ?></option>
                                        <option value="damaged"><?= t('products.damaged_goods') ?></option>
                                        <option value="lost"><?= t('products.lost_items') ?></option>
                                        <option value="found"><?= t('products.found_items') ?></option>
                                        <option value="recount"><?= t('products.inventory_recount') ?></option>
                                        <option value="other"><?= t('products.other_reason') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?= t('products.adjustment_notes') ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="<?= t('products.adjustment_notes_placeholder') ?>"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div id="previewAdjustment" class="text-muted small" style="display: none;">
                                <strong><?= t('products.new_stock_level') ?>:</strong> <span id="newStockLevel">0</span> <?= htmlspecialchars($product->unit_of_measure ?? 'pcs') ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?= t('products.apply_adjustment') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <i class="fas fa-lock fa-3x mb-3"></i>
                    <h5><?= t('products.no_stock_permission') ?></h5>
                    <p><?= t('products.no_stock_permission_message') ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Stock Movements -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-history me-2"></i><?= t('products.recent_stock_movements') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stock_movements)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= t('products.date') ?></th>
                                    <th><?= t('products.type') ?></th>
                                    <th><?= t('products.quantity') ?></th>
                                    <th><?= t('products.balance_after') ?></th>
                                    <th><?= t('products.reason') ?></th>
                                    <th><?= t('products.notes') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_movements as $movement): ?>
                                <tr>
                                    <td><?= date('M d, Y H:i', strtotime($movement->created_at ?? 'now')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($movement->quantity ?? 0) > 0 ? 'success' : 'danger' ?>">
                                            <?= ($movement->quantity ?? 0) > 0 ? t('products.stock_in') : t('products.stock_out') ?>
                                        </span>
                                    </td>
                                    <td class="<?= ($movement->quantity ?? 0) > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= ($movement->quantity ?? 0) > 0 ? '+' : '' ?><?= $movement->quantity ?? 0 ?>
                                    </td>
                                    <td><?= $movement->balance_after ?? 0 ?></td>
                                    <td><?= htmlspecialchars($movement->reason ?? t('products.manual_adjustment')) ?></td>
                                    <td><?= htmlspecialchars($movement->notes ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h5><?= t('products.no_stock_movements') ?></h5>
                        <p><?= t('products.no_stock_movements_message') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentInput = document.getElementById('adjustment');
    const currentStock = <?= $product->stock_quantity ?? 0 ?>;
    const previewDiv = document.getElementById('previewAdjustment');
    const newStockSpan = document.getElementById('newStockLevel');
    
    adjustmentInput.addEventListener('input', function() {
        const adjustment = parseInt(this.value) || 0;
        const newStock = currentStock + adjustment;
        
        if (adjustment !== 0) {
            newStockSpan.textContent = newStock;
            previewDiv.style.display = 'block';
            
            if (newStock < 0) {
                newStockSpan.className = 'text-danger';
            } else if (newStock <= <?= $product->min_stock_level ?? 0 ?>) {
                newStockSpan.className = 'text-warning';
            } else {
                newStockSpan.className = 'text-success';
            }
        } else {
            previewDiv.style.display = 'none';
        }
    });
    
    // Handle form submission
    document.getElementById('stockAdjustmentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated stock
                window.location.reload();
            } else {
                alert(data.message || '<?= t('messages.error.general') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= t('messages.error.general') ?>');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>