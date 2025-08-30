<?php

/**
 * File: app/controllers/ReportsController.php
 * Purpose: Analytics and reporting controller
 * Depends on: Controller, various models for data aggregation
 * Notes: Handles business intelligence, report generation, and data analysis
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\Warehouse;

class ReportsController extends Controller
{
    /**
     * Reports dashboard
     */
    public function index(): void
    {
        $this->requireAuth();

        // Get report categories and quick stats
        $quickStats = [
            'total_sales' => $this->getTotalSales(),
            'total_orders' => $this->getTotalOrders(),
            'active_clients' => $this->getActiveClients(),
            'low_stock_items' => $this->getLowStockItems(),
            'pending_payments' => $this->getPendingPayments(),
            'monthly_revenue' => $this->getMonthlyRevenue()
        ];

        $reportCategories = [
            'sales' => [
                'title' => t('reports.sales_reports'),
                'icon' => 'fa-chart-line',
                'reports' => [
                    'sales-summary' => t('reports.sales_summary'),
                    'sales-by-client' => t('reports.sales_by_client'),
                    'sales-by-product' => t('reports.sales_by_product'),
                    'monthly-sales' => t('reports.monthly_sales')
                ]
            ],
            'inventory' => [
                'title' => t('reports.inventory_reports'),
                'icon' => 'fa-boxes',
                'reports' => [
                    'stock-levels' => t('reports.stock_levels'),
                    'stock-movements' => t('reports.stock_movements'),
                    'low-stock' => t('reports.low_stock'),
                    'inventory-valuation' => t('reports.inventory_valuation')
                ]
            ],
            'financial' => [
                'title' => t('reports.financial_reports'),
                'icon' => 'fa-dollar-sign',
                'reports' => [
                    'revenue-analysis' => t('reports.revenue_analysis'),
                    'profit-loss' => t('reports.profit_loss'),
                    'aging-report' => t('reports.aging_report'),
                    'payment-history' => t('reports.payment_history')
                ]
            ],
            'clients' => [
                'title' => t('reports.client_reports'),
                'icon' => 'fa-users',
                'reports' => [
                    'client-analysis' => t('reports.client_analysis'),
                    'top-clients' => t('reports.top_clients'),
                    'client-activity' => t('reports.client_activity'),
                    'client-profitability' => t('reports.client_profitability')
                ]
            ]
        ];

        $this->view('reports/index', [
            'quick_stats' => $quickStats,
            'report_categories' => $reportCategories,
            'page_title' => t('nav.reports')
        ]);
    }

    /**
     * Sales reports
     */
    public function sales(array $params = []): void
    {
        $this->requireAuth();

        $reportType = $params['type'] ?? 'summary';
        $dateRange = $this->input['date_range'] ?? '30';
        $format = $this->input['format'] ?? 'html';

        $dateFrom = date('Y-m-d', strtotime("-{$dateRange} days"));
        $dateTo = date('Y-m-d');

        // Override with custom dates if provided
        if (!empty($this->input['date_from'])) {
            $dateFrom = $this->input['date_from'];
        }
        if (!empty($this->input['date_to'])) {
            $dateTo = $this->input['date_to'];
        }

        $data = [];
        
        switch ($reportType) {
            case 'summary':
                $data = $this->getSalesSummary($dateFrom, $dateTo);
                break;
                
            case 'by-client':
                $data = $this->getSalesByClient($dateFrom, $dateTo);
                break;
                
            case 'by-product':
                $data = $this->getSalesByProduct($dateFrom, $dateTo);
                break;
                
            case 'monthly':
                $data = $this->getMonthlySales();
                break;
                
            default:
                $this->setFlash('error', t('messages.error.invalid_report_type'));
                $this->redirect('/reports');
        }

        if ($format === 'json') {
            $this->json(['success' => true, 'data' => $data]);
        } elseif ($format === 'csv') {
            $this->exportCSV($data, "sales_{$reportType}_" . date('Y-m-d'));
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, "sales_{$reportType}_" . date('Y-m-d'), 'sales', $reportType);
        } else {
            $this->view('reports/sales', [
                'report_type' => $reportType,
                'data' => $data,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'date_range' => $dateRange,
                'page_title' => t('reports.sales_reports')
            ]);
        }
    }

    /**
     * Inventory reports
     */
    public function inventory(array $params = []): void
    {
        $this->requireAuth();

        $reportType = $params['type'] ?? 'stock-levels';
        $format = $this->input['format'] ?? 'html';
        $warehouseId = $this->input['warehouse_id'] ?? 'all';
        $categoryId = $this->input['category_id'] ?? 'all';

        $data = [];
        
        switch ($reportType) {
            case 'stock-levels':
                $data = $this->getStockLevels($warehouseId, $categoryId);
                break;
                
            case 'stock-movements':
                $dateFrom = $this->input['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
                $dateTo = $this->input['date_to'] ?? date('Y-m-d');
                $data = $this->getStockMovements($dateFrom, $dateTo, $warehouseId);
                break;
                
            case 'low-stock':
                $data = $this->getLowStockReport();
                break;
                
            case 'inventory-valuation':
                $data = $this->getInventoryValuation($warehouseId);
                break;
                
            default:
                $this->setFlash('error', t('messages.error.invalid_report_type'));
                $this->redirect('/reports');
        }

        // Get filter options
        $warehouses = Warehouse::where('status', 'active')->get();
        $categories = Product::select('category')->distinct()->get();

        if ($format === 'json') {
            $this->json(['success' => true, 'data' => $data]);
        } elseif ($format === 'csv') {
            $this->exportCSV($data, "inventory_{$reportType}_" . date('Y-m-d'));
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, "inventory_{$reportType}_" . date('Y-m-d'), 'inventory', $reportType);
        } else {
            $this->view('reports/inventory', [
                'report_type' => $reportType,
                'data' => $data,
                'warehouses' => $warehouses,
                'categories' => $categories,
                'selected_warehouse' => $warehouseId,
                'selected_category' => $categoryId,
                'page_title' => t('reports.inventory_reports')
            ]);
        }
    }

    /**
     * Financial reports
     */
    public function financial(array $params = []): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'manager');

        $reportType = $params['type'] ?? 'revenue-analysis';
        $format = $this->input['format'] ?? 'html';
        $dateRange = $this->input['date_range'] ?? '30';

        $dateFrom = date('Y-m-d', strtotime("-{$dateRange} days"));
        $dateTo = date('Y-m-d');

        if (!empty($this->input['date_from'])) {
            $dateFrom = $this->input['date_from'];
        }
        if (!empty($this->input['date_to'])) {
            $dateTo = $this->input['date_to'];
        }

        $data = [];
        
        switch ($reportType) {
            case 'revenue-analysis':
                $data = $this->getRevenueAnalysis($dateFrom, $dateTo);
                break;
                
            case 'profit-loss':
                $data = $this->getProfitLossReport($dateFrom, $dateTo);
                break;
                
            case 'aging-report':
                $data = $this->getAgingReport();
                break;
                
            case 'payment-history':
                $data = $this->getPaymentHistory($dateFrom, $dateTo);
                break;
                
            default:
                $this->setFlash('error', t('messages.error.invalid_report_type'));
                $this->redirect('/reports');
        }

        if ($format === 'json') {
            $this->json(['success' => true, 'data' => $data]);
        } elseif ($format === 'csv') {
            $this->exportCSV($data, "financial_{$reportType}_" . date('Y-m-d'));
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, "financial_{$reportType}_" . date('Y-m-d'), 'financial', $reportType);
        } else {
            $this->view('reports/financial', [
                'report_type' => $reportType,
                'data' => $data,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'date_range' => $dateRange,
                'page_title' => t('reports.financial_reports')
            ]);
        }
    }

    /**
     * Client reports
     */
    public function clients(array $params = []): void
    {
        $this->requireAuth();

        $reportType = $params['type'] ?? 'analysis';
        $format = $this->input['format'] ?? 'html';
        $dateRange = $this->input['date_range'] ?? '90';

        $data = [];
        
        switch ($reportType) {
            case 'analysis':
                $data = $this->getClientAnalysis($dateRange);
                break;
                
            case 'top-clients':
                $data = $this->getTopClients($dateRange);
                break;
                
            case 'activity':
                $data = $this->getClientActivity($dateRange);
                break;
                
            case 'profitability':
                $data = $this->getClientProfitability($dateRange);
                break;
                
            default:
                $this->setFlash('error', t('messages.error.invalid_report_type'));
                $this->redirect('/reports');
        }

        if ($format === 'json') {
            $this->json(['success' => true, 'data' => $data]);
        } elseif ($format === 'csv') {
            $this->exportCSV($data, "clients_{$reportType}_" . date('Y-m-d'));
        } elseif ($format === 'pdf') {
            $this->exportPDF($data, "clients_{$reportType}_" . date('Y-m-d'), 'clients', $reportType);
        } else {
            $this->view('reports/clients', [
                'report_type' => $reportType,
                'data' => $data,
                'date_range' => $dateRange,
                'page_title' => t('reports.client_reports')
            ]);
        }
    }

    /**
     * Custom report builder
     */
    public function custom(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'manager');

        $this->view('reports/custom', [
            'page_title' => t('reports.custom_reports')
        ]);
    }

    /**
     * Generate custom report
     */
    public function generateCustom(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'manager');

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => t('messages.error.invalid_csrf')
            ], 419);
        }

        $reportConfig = [
            'name' => $this->input['report_name'] ?? 'Custom Report',
            'type' => $this->input['report_type'] ?? 'table',
            'data_source' => $this->input['data_source'] ?? 'sales',
            'fields' => $this->input['fields'] ?? [],
            'filters' => $this->input['filters'] ?? [],
            'grouping' => $this->input['grouping'] ?? [],
            'sorting' => $this->input['sorting'] ?? [],
            'date_range' => $this->input['date_range'] ?? 30
        ];

        try {
            $data = $this->buildCustomReport($reportConfig);
            
            $this->json([
                'success' => true,
                'data' => $data,
                'config' => $reportConfig
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => t('reports.custom_report_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods for data aggregation

    private function getTotalSales(): int
    {
        return (int)Invoice::where('status', 'paid')
                          ->whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
                          ->sum('total_amount');
    }

    private function getTotalOrders(): int
    {
        return SalesOrder::whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')
                        ->count();
    }

    private function getActiveClients(): int
    {
        return Client::where('is_active', 1)->count();
    }

    private function getLowStockItems(): int
    {
        return Product::whereRaw('quantity <= reorder_level')->count();
    }

    private function getPendingPayments(): int
    {
        return (int)Invoice::where('status', 'sent')
                          ->sum('total_amount');
    }

    private function getMonthlyRevenue(): int
    {
        return (int)Invoice::where('status', 'paid')
                          ->whereRaw('MONTH(created_at) = MONTH(CURDATE())')
                          ->whereRaw('YEAR(created_at) = YEAR(CURDATE())')
                          ->sum('total_amount');
    }

    private function getSalesSummary(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE(i.created_at) as date,
                COUNT(i.id) as total_invoices,
                SUM(i.total_amount) as total_revenue,
                AVG(i.total_amount) as avg_invoice_value,
                COUNT(DISTINCT i.client_id) as unique_clients
            FROM invoices i 
            WHERE i.status = 'paid' 
            AND DATE(i.created_at) BETWEEN ? AND ?
            GROUP BY DATE(i.created_at)
            ORDER BY date DESC
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getSalesByClient(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                c.name as client_name,
                c.type as client_type,
                COUNT(i.id) as total_invoices,
                SUM(i.total_amount) as total_revenue,
                AVG(i.total_amount) as avg_invoice_value,
                MAX(i.created_at) as last_purchase
            FROM clients c
            LEFT JOIN invoices i ON c.id = i.client_id 
            WHERE i.status = 'paid' 
            AND DATE(i.created_at) BETWEEN ? AND ?
            GROUP BY c.id, c.name, c.type
            ORDER BY total_revenue DESC
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getSalesByProduct(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                p.name as product_name,
                p.sku as product_sku,
                p.category,
                SUM(ii.quantity) as total_quantity,
                SUM(ii.total_amount) as total_revenue,
                AVG(ii.unit_price) as avg_unit_price,
                COUNT(DISTINCT i.client_id) as unique_clients
            FROM products p
            JOIN invoice_items ii ON p.id = ii.product_id
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.status = 'paid' 
            AND DATE(i.created_at) BETWEEN ? AND ?
            GROUP BY p.id, p.name, p.sku, p.category
            ORDER BY total_revenue DESC
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getMonthlySales(): array
    {
        $sql = "
            SELECT 
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                MONTHNAME(created_at) as month_name,
                COUNT(id) as total_invoices,
                SUM(total_amount) as total_revenue
            FROM invoices 
            WHERE status = 'paid' 
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year DESC, month DESC
        ";

        return $this->db->prepare($sql)->execute()->fetchAll();
    }

    private function getStockLevels(string $warehouseId, string $categoryId): array
    {
        $sql = "SELECT p.*, w.name as warehouse_name FROM products p 
                JOIN warehouses w ON p.warehouse_id = w.id 
                WHERE p.is_active = 1";
        $params = [];

        if ($warehouseId !== 'all') {
            $sql .= " AND p.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        if ($categoryId !== 'all') {
            $sql .= " AND p.category = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY p.quantity ASC";

        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }

    private function getStockMovements(string $dateFrom, string $dateTo, string $warehouseId): array
    {
        // This would require a stock_movements table
        // For now, return mock data
        return [];
    }

    private function getLowStockReport(): array
    {
        $sql = "
            SELECT p.*, w.name as warehouse_name,
                   (p.reorder_level - p.quantity) as shortage_quantity
            FROM products p 
            JOIN warehouses w ON p.warehouse_id = w.id 
            WHERE p.quantity <= p.reorder_level 
            AND p.is_active = 1
            ORDER BY shortage_quantity DESC
        ";

        return $this->db->prepare($sql)->execute()->fetchAll();
    }

    private function getInventoryValuation(string $warehouseId): array
    {
        $sql = "SELECT p.*, w.name as warehouse_name,
                       (p.quantity * p.unit_cost) as total_value
                FROM products p 
                JOIN warehouses w ON p.warehouse_id = w.id 
                WHERE p.is_active = 1";
        $params = [];

        if ($warehouseId !== 'all') {
            $sql .= " AND p.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        $sql .= " ORDER BY total_value DESC";

        return $this->db->prepare($sql)->execute($params)->fetchAll();
    }

    private function getRevenueAnalysis(string $dateFrom, string $dateTo): array
    {
        return [
            'revenue_by_month' => $this->getRevenueByMonth($dateFrom, $dateTo),
            'revenue_by_client_type' => $this->getRevenueByClientType($dateFrom, $dateTo),
            'revenue_trends' => $this->getRevenueTrends($dateFrom, $dateTo)
        ];
    }

    private function getRevenueByMonth(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as revenue
            FROM invoices 
            WHERE status = 'paid' 
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getRevenueByClientType(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                c.type,
                SUM(i.total_amount) as revenue,
                COUNT(i.id) as invoice_count
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            WHERE i.status = 'paid' 
            AND DATE(i.created_at) BETWEEN ? AND ?
            GROUP BY c.type
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getRevenueTrends(string $dateFrom, string $dateTo): array
    {
        // Calculate revenue growth trends
        return [];
    }

    private function getProfitLossReport(string $dateFrom, string $dateTo): array
    {
        // This would require cost tracking
        return [];
    }

    private function getAgingReport(): array
    {
        $sql = "
            SELECT 
                c.name as client_name,
                i.invoice_number,
                i.total_amount,
                i.created_at as invoice_date,
                DATEDIFF(CURDATE(), i.created_at) as days_outstanding,
                CASE 
                    WHEN DATEDIFF(CURDATE(), i.created_at) <= 30 THEN '0-30 days'
                    WHEN DATEDIFF(CURDATE(), i.created_at) <= 60 THEN '31-60 days'
                    WHEN DATEDIFF(CURDATE(), i.created_at) <= 90 THEN '61-90 days'
                    ELSE '90+ days'
                END as age_bucket
            FROM invoices i
            JOIN clients c ON i.client_id = c.id
            WHERE i.status IN ('sent', 'overdue')
            ORDER BY days_outstanding DESC
        ";

        return $this->db->prepare($sql)->execute()->fetchAll();
    }

    private function getPaymentHistory(string $dateFrom, string $dateTo): array
    {
        $sql = "
            SELECT 
                p.*,
                c.name as client_name,
                i.invoice_number
            FROM payments p
            JOIN invoices i ON p.invoice_id = i.id
            JOIN clients c ON i.client_id = c.id
            WHERE DATE(p.payment_date) BETWEEN ? AND ?
            ORDER BY p.payment_date DESC
        ";

        return $this->db->prepare($sql)->execute([$dateFrom, $dateTo])->fetchAll();
    }

    private function getClientAnalysis(string $dateRange): array
    {
        return [
            'client_distribution' => $this->getClientDistribution(),
            'client_growth' => $this->getClientGrowth($dateRange),
            'client_retention' => $this->getClientRetention($dateRange)
        ];
    }

    private function getClientDistribution(): array
    {
        $sql = "SELECT type, COUNT(*) as count FROM clients GROUP BY type";
        return $this->db->prepare($sql)->execute()->fetchAll();
    }

    private function getClientGrowth(string $dateRange): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_clients
            FROM clients 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ";

        return $this->db->prepare($sql)->execute([$dateRange])->fetchAll();
    }

    private function getClientRetention(string $dateRange): array
    {
        // Calculate client retention rates
        return [];
    }

    private function getTopClients(string $dateRange): array
    {
        $sql = "
            SELECT 
                c.name,
                c.type,
                COUNT(i.id) as total_orders,
                SUM(i.total_amount) as total_revenue,
                MAX(i.created_at) as last_order
            FROM clients c
            JOIN invoices i ON c.id = i.client_id
            WHERE i.status = 'paid'
            AND i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY c.id, c.name, c.type
            ORDER BY total_revenue DESC
            LIMIT 20
        ";

        return $this->db->prepare($sql)->execute([$dateRange])->fetchAll();
    }

    private function getClientActivity(string $dateRange): array
    {
        $sql = "
            SELECT 
                c.name,
                COUNT(DISTINCT q.id) as quotes,
                COUNT(DISTINCT so.id) as orders,
                COUNT(DISTINCT i.id) as invoices
            FROM clients c
            LEFT JOIN quotes q ON c.id = q.client_id AND q.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            LEFT JOIN sales_orders so ON c.id = so.client_id AND so.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            LEFT JOIN invoices i ON c.id = i.client_id AND i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY c.id, c.name
            HAVING (quotes + orders + invoices) > 0
            ORDER BY (quotes + orders + invoices) DESC
        ";

        return $this->db->prepare($sql)->execute([$dateRange, $dateRange, $dateRange])->fetchAll();
    }

    private function getClientProfitability(string $dateRange): array
    {
        // This would require cost analysis
        return [];
    }

    private function buildCustomReport(array $config): array
    {
        // Custom report builder implementation
        return [];
    }

    private function exportCSV(array $data, string $filename): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys((array)$data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, (array)$row);
            }
        }
        
        fclose($output);
        exit;
    }

    private function exportPDF(array $data, string $filename, string $category, string $type): void
    {
        // Generate PDF report using template
        ob_start();
        include APP_PATH . "/views/reports/pdf/{$category}_{$type}.php";
        $html = ob_get_clean();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        
        // In production, use proper PDF library like TCPDF or mPDF
        echo $html;
        exit;
    }
}