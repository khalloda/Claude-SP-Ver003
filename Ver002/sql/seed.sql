-- File: sql/seed.sql
-- Purpose: Initial seed data for the application
-- Depends on: schema.sql must be run first
-- Notes: Creates admin user, basic dropdowns, sample data

USE `sp_main`;

-- Insert default admin user (password: Admin@123)
INSERT INTO `sp_users` (`name`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
('System Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW(), NOW()),
('Manager User', 'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 1, NOW(), NOW()),
('Regular User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, NOW(), NOW());

-- Insert product categories
INSERT INTO `sp_dropdowns` (`type`, `name`, `value`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('category', 'Automotive Parts', 'automotive', NULL, 1, 1, NOW(), NOW()),
('category', 'Electronics', 'electronics', NULL, 2, 1, NOW(), NOW()),
('category', 'Mechanical Parts', 'mechanical', NULL, 3, 1, NOW(), NOW()),
('category', 'Tools & Equipment', 'tools', NULL, 4, 1, NOW(), NOW()),
('category', 'Safety Equipment', 'safety', NULL, 5, 1, NOW(), NOW());

-- Insert subcategories for Automotive Parts
INSERT INTO `sp_dropdowns` (`type`, `name`, `value`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('category', 'Engine Parts', 'engine_parts', 1, 1, 1, NOW(), NOW()),
('category', 'Brake System', 'brake_system', 1, 2, 1, NOW(), NOW()),
('category', 'Transmission', 'transmission', 1, 3, 1, NOW(), NOW()),
('category', 'Electrical', 'electrical', 1, 4, 1, NOW(), NOW());

-- Insert units of measure
INSERT INTO `sp_dropdowns` (`type`, `name`, `value`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('unit_of_measure', 'Piece', 'pcs', NULL, 1, 1, NOW(), NOW()),
('unit_of_measure', 'Kilogram', 'kg', NULL, 2, 1, NOW(), NOW()),
('unit_of_measure', 'Liter', 'l', NULL, 3, 1, NOW(), NOW()),
('unit_of_measure', 'Meter', 'm', NULL, 4, 1, NOW(), NOW()),
('unit_of_measure', 'Set', 'set', NULL, 5, 1, NOW(), NOW()),
('unit_of_measure', 'Box', 'box', NULL, 6, 1, NOW(), NOW()),
('unit_of_measure', 'Pack', 'pack', NULL, 7, 1, NOW(), NOW());

-- Insert payment terms
INSERT INTO `sp_dropdowns` (`type`, `name`, `value`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('payment_terms', 'Cash', 'cash', NULL, 1, 1, NOW(), NOW()),
('payment_terms', 'Net 15 Days', 'net_15', NULL, 2, 1, NOW(), NOW()),
('payment_terms', 'Net 30 Days', 'net_30', NULL, 3, 1, NOW(), NOW()),
('payment_terms', 'Net 45 Days', 'net_45', NULL, 4, 1, NOW(), NOW()),
('payment_terms', 'Net 60 Days', 'net_60', NULL, 5, 1, NOW(), NOW());

-- Insert countries
INSERT INTO `sp_dropdowns` (`type`, `name`, `value`, `parent_id`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
('country', 'United States', 'US', NULL, 1, 1, NOW(), NOW()),
('country', 'Canada', 'CA', NULL, 2, 1, NOW(), NOW()),
('country', 'United Kingdom', 'GB', NULL, 3, 1, NOW(), NOW()),
('country', 'Germany', 'DE', NULL, 4, 1, NOW(), NOW()),
('country', 'France', 'FR', NULL, 5, 1, NOW(), NOW()),
('country', 'Saudi Arabia', 'SA', NULL, 6, 1, NOW(), NOW()),
('country', 'United Arab Emirates', 'AE', NULL, 7, 1, NOW(), NOW()),
('country', 'Egypt', 'EG', NULL, 8, 1, NOW(), NOW());

-- Insert default currency
INSERT INTO `sp_currencies` (`code`, `name`, `symbol`, `exchange_rate`, `is_default`, `status`, `created_at`, `updated_at`) VALUES
('USD', 'US Dollar', '$', 1.0000, 1, 1, NOW(), NOW()),
('EUR', 'Euro', '€', 0.8500, 0, 1, NOW(), NOW()),
('SAR', 'Saudi Riyal', 'ر.س', 3.7500, 0, 1, NOW(), NOW()),
('AED', 'UAE Dirham', 'د.إ', 3.6700, 0, 1, NOW(), NOW()),
('EGP', 'Egyptian Pound', 'ج.م', 30.9000, 0, 1, NOW(), NOW());

-- Insert sample suppliers
INSERT INTO `sp_suppliers` (`company_name`, `contact_person`, `email`, `phone`, `address`, `city`, `country`, `status`, `created_at`, `updated_at`) VALUES
('AutoParts International', 'John Smith', 'john@autoparts-intl.com', '+1-555-0123', '123 Industrial Blvd', 'Detroit', 'United States', 1, NOW(), NOW()),
('European Components Ltd', 'Hans Mueller', 'hans@eurocomp.de', '+49-30-1234567', 'Industriestr. 45', 'Berlin', 'Germany', 1, NOW(), NOW()),
('Gulf Spare Parts Co.', 'Ahmed Al-Rashid', 'ahmed@gulfspares.com', '+971-4-1234567', 'Sheikh Zayed Road', 'Dubai', 'United Arab Emirates', 1, NOW(), NOW()),
('Precision Tools Inc.', 'Sarah Johnson', 'sarah@precisiontools.com', '+1-555-0456', '789 Manufacturing Ave', 'Chicago', 'United States', 1, NOW(), NOW());

-- Insert sample clients
INSERT INTO `sp_clients` (`company_name`, `contact_person`, `email`, `phone`, `address`, `city`, `country`, `credit_limit`, `payment_terms`, `status`, `created_at`, `updated_at`) VALUES
('ABC Manufacturing', 'Michael Brown', 'michael@abc-manufacturing.com', '+1-555-1001', '456 Factory St', 'Los Angeles', 'United States', 50000.00, 'net_30', 1, NOW(), NOW()),
('Desert Motors LLC', 'Omar Hassan', 'omar@desertmotors.ae', '+971-4-2345678', 'Al Qusais Industrial Area', 'Dubai', 'United Arab Emirates', 75000.00, 'net_45', 1, NOW(), NOW()),
('Tech Solutions GmbH', 'Klaus Weber', 'klaus@techsol.de', '+49-40-9876543', 'Technologiepark 12', 'Hamburg', 'Germany', 60000.00, 'net_30', 1, NOW(), NOW()),
('Riyadh Auto Center', 'Abdullah Al-Saud', 'abdullah@riyadhauto.sa', '+966-11-4567890', 'King Fahd Road', 'Riyadh', 'Saudi Arabia', 40000.00, 'net_15', 1, NOW(), NOW()),
('Cairo Engineering', 'Ahmed Mohamed', 'ahmed@cairoeng.eg', '+20-2-12345678', 'Nasr City', 'Cairo', 'Egypt', 25000.00, 'net_30', 1, NOW(), NOW());

-- Insert sample products
INSERT INTO `sp_products` (`name`, `description`, `sku`, `part_number`, `category_id`, `supplier_id`, `purchase_price`, `selling_price`, `stock_quantity`, `min_stock_level`, `unit_of_measure`, `brand`, `status`, `created_at`, `updated_at`) VALUES
('Brake Pad Set', 'Front brake pads for sedan vehicles', 'BP-001', 'BP-FRT-001', 7, 1, 45.00, 89.99, 25, 5, 'set', 'BrakeMaster', 1, NOW(), NOW()),
('Oil Filter', 'High-quality oil filter for most engines', 'OF-002', 'OF-STD-002', 6, 1, 12.50, 24.99, 150, 20, 'pcs', 'FilterPro', 1, NOW(), NOW()),
('Spark Plug Set', 'Platinum spark plugs - 4 pack', 'SP-003', 'SP-PLT-003', 6, 1, 28.00, 55.99, 75, 10, 'set', 'IgniteMax', 1, NOW(), NOW()),
('LED Headlight Kit', 'H7 LED headlight conversion kit', 'HL-004', 'HL-H7-004', 8, 2, 85.00, 169.99, 35, 5, 'set', 'BrightLED', 1, NOW(), NOW()),
('Transmission Fluid', 'ATF transmission fluid - 1L bottle', 'TF-005', 'TF-ATF-005', 8, 3, 18.00, 35.99, 100, 15, 'l', 'FluidTech', 1, NOW(), NOW()),
('Wheel Bearing', 'Front wheel bearing assembly', 'WB-006', 'WB-FRT-006', 7, 1, 65.00, 129.99, 20, 3, 'pcs', 'BearingPro', 1, NOW(), NOW()),
('Clutch Kit', 'Complete clutch kit for manual transmission', 'CK-007', 'CK-MAN-007', 9, 2, 180.00, 359.99, 8, 2, 'set', 'ClutchMaster', 1, NOW(), NOW()),
('Air Filter', 'Engine air filter - replaceable element', 'AF-008', 'AF-ENG-008', 6, 1, 15.00, 29.99, 80, 12, 'pcs', 'AirFlow', 1, NOW(), NOW()),
('Shock Absorber', 'Rear shock absorber for SUV', 'SA-009', 'SA-RR-009', 7, 4, 95.00, 189.99, 15, 3, 'pcs', 'SmoothRide', 1, NOW(), NOW()),
('Battery Terminal', 'Universal battery terminal clamp', 'BT-010', 'BT-UNI-010', 8, 1, 8.50, 16.99, 200, 25, 'pcs', 'PowerConnect', 1, NOW(), NOW());

-- Insert sample warehouse
INSERT INTO `sp_warehouses` (`name`, `code`, `address`, `city`, `country`, `manager`, `phone`, `email`, `status`, `created_at`, `updated_at`) VALUES
('Main Warehouse', 'WH001', '100 Storage Drive', 'Chicago', 'United States', 'Robert Wilson', '+1-555-2000', 'warehouse@company.com', 1, NOW(), NOW()),
('Dubai Distribution Center', 'WH002', 'Jebel Ali Free Zone', 'Dubai', 'United Arab Emirates', 'Khalid Al-Mansouri', '+971-4-3456789', 'dubai@company.com', 1, NOW(), NOW());

-- Create some sample stock movements
INSERT INTO `sp_stock_movements` (`product_id`, `movement_type`, `quantity`, `old_quantity`, `new_quantity`, `reason`, `created_by`, `created_at`) VALUES
(1, 'in', 25, 0, 25, 'Initial stock', 1, NOW()),
(2, 'in', 150, 0, 150, 'Initial stock', 1, NOW()),
(3, 'in', 75, 0, 75, 'Initial stock', 1, NOW()),
(4, 'in', 35, 0, 35, 'Initial stock', 1, NOW()),
(5, 'in', 100, 0, 100, 'Initial stock', 1, NOW()),
(6, 'in', 20, 0, 20, 'Initial stock', 1, NOW()),
(7, 'in', 8, 0, 8, 'Initial stock', 1, NOW()),
(8, 'in', 80, 0, 80, 'Initial stock', 1, NOW()),
(9, 'in', 15, 0, 15, 'Initial stock', 1, NOW()),
(10, 'in', 200, 0, 200, 'Initial stock', 1, NOW());

-- Create some sample quotes
INSERT INTO `sp_quotes` (`quote_number`, `client_id`, `quote_date`, `valid_until`, `subtotal`, `tax_rate`, `tax_amount`, `total_amount`, `notes`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
('QT20250001', 1, '2025-01-15', '2025-02-15', 295.97, 8.25, 24.42, 320.39, 'Brake service package', 1, 1, '2025-01-15 10:00:00', '2025-01-15 10:00:00'),
('QT20250002', 2, '2025-01-20', '2025-02-20', 169.99, 5.00, 8.50, 178.49, 'LED headlight upgrade', 2, 1, '2025-01-20 14:30:00', '2025-01-20 14:30:00'),
('QT20250003', 3, '2025-01-25', '2025-02-25', 529.97, 19.00, 100.69, 630.66, 'Major service components', 0, 2, '2025-01-25 09:15:00', '2025-01-25 09:15:00');

-- Create quote items
INSERT INTO `sp_quote_items` (`quote_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 89.99, 89.99, '2025-01-15 10:05:00', '2025-01-15 10:05:00'),
(1, 2, 2, 24.99, 49.98, '2025-01-15 10:05:00', '2025-01-15 10:05:00'),
(1, 8, 2, 29.99, 59.98, '2025-01-15 10:05:00', '2025-01-15 10:05:00'),
(1, 3, 1, 55.99, 55.99, '2025-01-15 10:05:00', '2025-01-15 10:05:00'),
(1, 10, 2, 16.99, 33.98, '2025-01-15 10:05:00', '2025-01-15 10:05:00'),
(2, 4, 1, 169.99, 169.99, '2025-01-20 14:35:00', '2025-01-20 14:35:00'),
(3, 7, 1, 359.99, 359.99, '2025-01-25 09:20:00', '2025-01-25 09:20:00'),
(3, 9, 1, 189.99, 189.99, '2025-01-25 09:20:00', '2025-01-25 09:20:00');

-- Create sample sales order from accepted quote
INSERT INTO `sp_sales_orders` (`order_number`, `client_id`, `order_date`, `subtotal`, `tax_rate`, `tax_amount`, `total_amount`, `status`, `created_by`, `quote_id`, `created_at`, `updated_at`) VALUES
('SO20250001', 2, '2025-01-22', 169.99, 5.00, 8.50, 178.49, 1, 1, 2, '2025-01-22 10:00:00', '2025-01-22 10:00:00');

-- Create sales order items
INSERT INTO `sp_sales_order_items` (`sales_order_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 169.99, 169.99, '2025-01-22 10:05:00', '2025-01-22 10:05:00');

-- Create sample invoice from sales order
INSERT INTO `sp_invoices` (`invoice_number`, `client_id`, `invoice_date`, `due_date`, `subtotal`, `tax_rate`, `tax_amount`, `total_amount`, `paid_amount`, `status`, `created_by`, `sales_order_id`, `created_at`, `updated_at`) VALUES
('INV20250001', 2, '2025-01-23', '2025-02-22', 169.99, 5.00, 8.50, 178.49, 0.00, 0, 1, 1, '2025-01-23 11:00:00', '2025-01-23 11:00:00');

-- Create invoice items
INSERT INTO `sp_invoice_items` (`invoice_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 169.99, 169.99, '2025-01-23 11:05:00', '2025-01-23 11:05:00');

-- Update quote status to converted and link to sales order
UPDATE `sp_quotes` SET `status` = 5, `sales_order_id` = 1 WHERE `id` = 2;

-- Update sales order to link to invoice
UPDATE `sp_sales_orders` SET `invoice_id` = 1 WHERE `id` = 1;

-- Update stock quantities after sales
UPDATE `sp_products` SET `stock_quantity` = `stock_quantity` - 1 WHERE `id` = 4;

-- Record stock movement for sale
INSERT INTO `sp_stock_movements` (`product_id`, `movement_type`, `quantity`, `old_quantity`, `new_quantity`, `reason`, `reference_type`, `reference_id`, `created_by`, `created_at`) VALUES
(4, 'out', 1, 35, 34, 'Sale - Invoice INV20250001', 'invoice', 1, 1, NOW());

-- Create a sample payment
INSERT INTO `sp_payments` (`payment_number`, `client_id`, `invoice_id`, `payment_date`, `amount`, `payment_method`, `reference_number`, `notes`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
('PAY20250001', 2, 1, '2025-01-25', 178.49, 'Bank Transfer', 'TXN-20250125-001', 'Full payment for invoice INV20250001', 1, 1, '2025-01-25 15:00:00', '2025-01-25 15:00:00');

-- Update invoice as paid
UPDATE `sp_invoices` SET `paid_amount` = 178.49, `status` = 2 WHERE `id` = 1;

COMMIT;