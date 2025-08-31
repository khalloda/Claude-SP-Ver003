<?php

/**
 * File: app/controllers/DashboardController.php
 * Purpose: Dashboard controller for main application dashboard
 * Depends on: Controller, Auth, Models
 * Notes: Displays dashboard with KPIs, charts, recent activities
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Product;
use App\Models\Client;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Models\Invoice;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $data = [
            'stats' => $this->getDashboardStats(),
            'charts' => $this->getChartData(),
            'recent_activities' => $this->getRecentActivities(),
            'low_stock_products' => $this->getLowStockProducts(),
            'pending_quotes' => $this->getPendingQuotes(),
            'overdue_invoices' => $this->getOverdueInvoices()
        ];

        $this->view('dashboard/index', $data);
    }

    private function getDashboardStats(): array
    {
        return [
            'total_products' => $this->getTotalProducts(),
            'total_clients' => $this->getTotalClients(),
            'pending_quotes' => $this->getPendingQuotesCount(),
            'monthly_sales' => $this->getMonthlySales(),
            'low_stock_count' => $this->getLowStockCount(),
            'total_inventory_value' => $this->getTotalInventoryValue(),
            'outstanding_invoices' => $this->getOutstandingInvoicesAmount(),
            'this_month_revenue' => $this->getThisMonthRevenue()
        ];
    }

    private function getTotalProducts(): int
    {
        return count(Product::where('status', Product::STATUS_ACTIVE)->get());
    }

    private function getTotalClients(): int
    {
        return count(Client::where('status', Client::STATUS_ACTIVE)->get());
    }

    private function getPendingQuotesCount(): int
    {
        return count(Quote::where('status', Quote::STATUS_SENT)->get());
    }

    private function getMonthlySales(): float
    {
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        $invoices = Invoice::where('invoice_date', '>=', $startOfMonth)
                          ->where('invoice_date', '<=', $endOfMonth)
                          ->where('status', '!=', Invoice::STATUS_CANCELLED)
                          ->get();
        
        $total = 0;
        foreach ($invoices as $invoice) {
            $total += $invoice->total_amount;
        }
        
        return $total;
    }

    private function getLowStockCount(): int
    {
        return count(Product::getLowStockProducts());
    }

    private function getTotalInventoryValue(): float
    {
        $products = Product::where('status', Product::STATUS_ACTIVE)->get();
        $total = 0;
        
        foreach ($products as $product) {
            $total += $product->getStockValue();
        }
        
        return $total;
    }

    private function getOutstandingInvoicesAmount(): float
    {
        $invoices = Invoice::where('status', '!=', Invoice::STATUS_PAID)
                          ->where('status', '!=', Invoice::STATUS_CANCELLED)
                          ->get();
        
        $total = 0;
        foreach ($invoices as $invoice) {
            $total += ($invoice->total_amount - $invoice->paid_amount);
        }
        
        return $total;
    }

    private function getThisMonthRevenue(): float
    {
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        $invoices = Invoice::where('invoice_date', '>=', $startOfMonth)
                          ->where('invoice_date', '<=', $endOfMonth)
                          ->where('status', Invoice::STATUS_PAID)
                          ->get();
        
        $total = 0;
        foreach ($invoices as $invoice) {
            $total += $invoice->total_amount;
        }
        
        return $total;
    }

    private function getChartData(): array
    {
        return [
            'sales_chart' => $this->getSalesChartData(),
            'product_categories' => $this->getProductCategoriesData(),
            'monthly_revenue' => $this->getMonthlyRevenueData(),
            'quote_status' => $this->getQuoteStatusData()
        ];
    }

    private function getSalesChartData(): array
    {
        $data = [];
        
        // Get last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            
            $invoices = Invoice::where('invoice_date', '>=', $monthStart)
                              ->where('invoice_date', '<=', $monthEnd)
                              ->where('status', Invoice::STATUS_PAID)
                              ->get();
            
            $total = 0;
            foreach ($invoices as $invoice) {
                $total += $invoice->total_amount;
            }
            
            $data[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'sales' => $total
            ];
        }
        
        return $data;
    }

    private function getProductCategoriesData(): array
    {
        $categories = [];
        $products = Product::where('status', Product::STATUS_ACTIVE)->get();
        
        foreach ($products as $product) {
            $category = $product->category();
            $categoryName = $category ? $category->name : 'Uncategorized';
            
            if (!isset($categories[$categoryName])) {
                $categories[$categoryName] = 0;
            }
            
            $categories[$categoryName]++;
        }
        
        return $categories;
    }

    private function getMonthlyRevenueData(): array
    {
        $data = [];
        
        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            
            $invoices = Invoice::where('invoice_date', '>=', $monthStart)
                              ->where('invoice_date', '<=', $monthEnd)
                              ->get();
            
            $revenue = 0;
            $expenses = 0; // You can add expense calculation here
            
            foreach ($invoices as $invoice) {
                if ($invoice->status === Invoice::STATUS_PAID) {
                    $revenue += $invoice->total_amount;
                }
            }
            
            $data[] = [
                'month' => date('M', strtotime($monthStart)),
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $revenue - $expenses
            ];
        }
        
        return $data;
    }

    private function getQuoteStatusData(): array
    {
        $statuses = [
            'Draft' => Quote::STATUS_DRAFT,
            'Sent' => Quote::STATUS_SENT,
            'Accepted' => Quote::STATUS_ACCEPTED,
            'Rejected' => Quote::STATUS_REJECTED,
            'Expired' => Quote::STATUS_EXPIRED,
            'Converted' => Quote::STATUS_CONVERTED
        ];
        
        $data = [];
        
        foreach ($statuses as $label => $status) {
            $count = count(Quote::where('status', $status)->get());
            if ($count > 0) {
                $data[] = [
                    'label' => $label,
                    'count' => $count,
                    'status' => $status
                ];
            }
        }
        
        return $data;
    }

    private function getRecentActivities(): array
    {
        $activities = [];
        
        // Recent quotes
        $recentQuotes = Quote::orderBy('created_at', 'DESC')->limit(5)->get();
        foreach ($recentQuotes as $quote) {
            $client = $quote->client();
            $activities[] = [
                'type' => 'quote',
                'icon' => 'file-text',
                'title' => "Quote #{$quote->quote_number} created",
                'description' => "For " . ($client ? $client->getDisplayName() : 'Unknown Client'),
                'date' => $quote->created_at,
                'url' => "/quotes/{$quote->id}"
            ];
        }
        
        // Recent sales orders
        $recentOrders = SalesOrder::orderBy('created_at', 'DESC')->limit(5)->get();
        foreach ($recentOrders as $order) {
            $client = $order->client();
            $activities[] = [
                'type' => 'order',
                'icon' => 'shopping-cart',
                'title' => "Sales Order #{$order->order_number} created",
                'description' => "For " . ($client ? $client->getDisplayName() : 'Unknown Client'),
                'date' => $order->created_at,
                'url' => "/salesorders/{$order->id}"
            ];
        }
        
        // Recent invoices
        $recentInvoices = Invoice::orderBy('created_at', 'DESC')->limit(5)->get();
        foreach ($recentInvoices as $invoice) {
            $client = $invoice->client();
            $activities[] = [
                'type' => 'invoice',
                'icon' => 'file-invoice-dollar',
                'title' => "Invoice #{$invoice->invoice_number} created",
                'description' => "For " . ($client ? $client->getDisplayName() : 'Unknown Client'),
                'date' => $invoice->created_at,
                'url' => "/invoices/{$invoice->id}"
            ];
        }
        
        // Sort by date and limit to 10
        usort($activities, function($a, $b) {
            $dateA = $a['date'] ? strtotime($a['date']) : 0;
            $dateB = $b['date'] ? strtotime($b['date']) : 0;
            return $dateB - $dateA;
        });
        
        return array_slice($activities, 0, 10);
    }

    private function getLowStockProducts(): array
    {
        return array_slice(Product::getLowStockProducts(), 0, 10);
    }

    private function getPendingQuotes(): array
    {
        return array_slice(Quote::where('status', Quote::STATUS_SENT)->get(), 0, 5);
    }

    private function getOverdueInvoices(): array
    {
        $today = date('Y-m-d');
        $overdueInvoices = [];
        
        $invoices = Invoice::where('status', '!=', Invoice::STATUS_PAID)
                          ->where('status', '!=', Invoice::STATUS_CANCELLED)
                          ->get();
        
        foreach ($invoices as $invoice) {
            if ($invoice->due_date && strtotime($invoice->due_date) < strtotime($today)) {
                $overdueInvoices[] = $invoice;
            }
        }
        
        return array_slice($overdueInvoices, 0, 5);
    }

    public function quickStats(): void
    {
        $this->requireAuth();
        
        // Return JSON for AJAX requests
        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => true,
                'data' => $this->getDashboardStats()
            ]);
        } else {
            $this->redirect('/dashboard');
        }
    }

    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}