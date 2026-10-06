-- =============================================================================
-- PADMA INVENTORY — A to Z MySQL / MariaDB (XAMPP local)
-- Charset: utf8mb4. Run: mysql -u root -p dbname < PAD_INVENTORY_A_TO_Z_MYSQL.sql
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- A. Organization & structure
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `organization` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NULL,
  `address` TEXT NOT NULL,
  `phone_number` VARCHAR(50) NULL,
  `thumb_image` VARCHAR(500) NULL,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_organization_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `division` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `division_name` VARCHAR(255) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `branch` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `division_id` BIGINT UNSIGNED NULL,
  `hr_branch_id` BIGINT UNSIGNED NULL,
  `branch_name` VARCHAR(255) NULL,
  `branch_code` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_branch_org` (`org_id`),
  KEY `idx_branch_hr` (`hr_branch_id`),
  KEY `idx_branch_division` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `department` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `department_name` VARCHAR(255) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- B. Master data
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `category` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `category_title` VARCHAR(255) NULL,
  `category` VARCHAR(255) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `brand` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `brand_name` VARCHAR(255) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `supplier_name` VARCHAR(255) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `unit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `unit_name` VARCHAR(100) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_info` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `category_id` BIGINT UNSIGNED NULL,
  `brand_id` BIGINT UNSIGNED NULL,
  `supplier_id` BIGINT UNSIGNED NULL,
  `item_title` VARCHAR(400) NULL,
  `item` VARCHAR(400) NULL,
  `code` VARCHAR(100) NULL,
  `size` VARCHAR(100) NULL,
  `image` VARCHAR(500) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_item_info_category` (`category_id`),
  KEY `idx_item_info_org` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- C. item_inventory
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `item_inventory` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` BIGINT UNSIGNED NULL,
  `slug` VARCHAR(255) NULL,
  `code` VARCHAR(100) NULL,
  `unit_size` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `branch_id` BIGINT UNSIGNED NULL,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `image` VARCHAR(500) NULL,
  `from_device_serial_no` VARCHAR(100) NULL,
  `to_device_serial_no` VARCHAR(100) NULL,
  `uniq_serial_no` VARCHAR(100) NULL,
  `set_uniq_no` VARCHAR(100) NULL,
  `service_warranty` VARCHAR(100) NULL,
  `warranty` VARCHAR(100) NULL,
  `supplier_id` BIGINT UNSIGNED NULL,
  `store_employee_id` VARCHAR(50) NULL,
  `from_employee_id` VARCHAR(50) NULL,
  `to_employee_id` VARCHAR(50) NULL,
  `from_branch_id` BIGINT UNSIGNED NULL,
  `to_branch_id` BIGINT UNSIGNED NULL,
  `brand_id` BIGINT UNSIGNED NULL,
  `category_id` BIGINT UNSIGNED NULL,
  `quantity` DECIMAL(12,2) DEFAULT 1,
  `price` DECIMAL(14,2) NULL,
  `vat` DECIMAL(14,2) NULL,
  `tax` DECIMAL(14,2) NULL,
  `store_type` VARCHAR(10) NULL COMMENT 'SP=stock position, SI=stock in, SO=stock out',
  `is_set` TINYINT DEFAULT 0,
  `ram` VARCHAR(100) NULL,
  `storage` VARCHAR(100) NULL,
  `processor` VARCHAR(100) NULL,
  `graphics` VARCHAR(100) NULL,
  `display` VARCHAR(100) NULL,
  `battery` VARCHAR(100) NULL,
  `color` VARCHAR(50) NULL,
  `send_employee` VARCHAR(50) NULL,
  `received_employee_id` VARCHAR(50) NULL,
  `warehouse` VARCHAR(100) NULL,
  `condition` VARCHAR(50) NULL,
  `stock_in_no` VARCHAR(100) NULL,
  `stock_out_no` VARCHAR(100) NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `remarks` TEXT NULL,
  `system_generate_flag` TINYINT DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  `created_date` DATE NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `from_designation` VARCHAR(255) NULL,
  `to_designation` VARCHAR(255) NULL,
  `receive_date_time` TIMESTAMP NULL,
  `received_branch_id` BIGINT UNSIGNED NULL,
  `updated_by` BIGINT UNSIGNED NULL,
  `receive_remarks` VARCHAR(500) NULL,
  `invoice_attachment` VARCHAR(500) NULL,
  `stock_out_note` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_inv_org_branch` (`org_id`, `branch_id`),
  KEY `idx_inv_item` (`item_id`),
  KEY `idx_inv_store_type` (`store_type`),
  KEY `idx_inv_set_uniq` (`set_uniq_no`),
  KEY `idx_inv_status` (`status`),
  KEY `idx_inv_stock_in` (`stock_in_no`),
  KEY `idx_inv_stock_out` (`stock_out_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- D. Users & RBAC
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `branch_id` BIGINT UNSIGNED NULL,
  `user_types` VARCHAR(100) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `backend_menu` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_name` VARCHAR(255) NULL,
  `slug` VARCHAR(255) NULL,
  `url` VARCHAR(500) NULL,
  `parent_id` BIGINT UNSIGNED DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `access_menu` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` BIGINT UNSIGNED NULL,
  `user_type` VARCHAR(100) NULL,
  `view_access` TINYINT DEFAULT 0,
  `add_access` TINYINT DEFAULT 0,
  `edit_access` TINYINT DEFAULT 0,
  `delete_access` TINYINT DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- E. Movement logs
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `item_transfer_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_inventory_id` BIGINT UNSIGNED NOT NULL,
  `org_id` BIGINT UNSIGNED NULL,
  `stock_out_no` VARCHAR(100) NULL,
  `set_uniq_no` VARCHAR(100) NULL,
  `uniq_serial_no` VARCHAR(100) NULL,
  `condition` VARCHAR(50) NULL,
  `from_employee_id` VARCHAR(50) NULL,
  `to_employee_id` VARCHAR(50) NULL,
  `from_branch_id` BIGINT UNSIGNED NULL,
  `to_branch_id` BIGINT UNSIGNED NULL,
  `from_device_serial_no` VARCHAR(100) NULL,
  `to_device_serial_no` VARCHAR(100) NULL,
  `transferred_by` BIGINT UNSIGNED NULL,
  `transferred_at` TIMESTAMP NULL,
  `remarks` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_itl_inv` (`item_inventory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `item_swap_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `swap_no` VARCHAR(100) NULL,
  `item_inventory_id` BIGINT UNSIGNED NOT NULL,
  `org_id` BIGINT UNSIGNED NULL,
  `stock_out_no` VARCHAR(100) NULL,
  `set_uniq_no` VARCHAR(100) NULL,
  `uniq_serial_no` VARCHAR(100) NULL,
  `condition` VARCHAR(50) NULL,
  `from_employee_id` VARCHAR(50) NULL,
  `to_employee_id` VARCHAR(50) NULL,
  `from_branch_id` BIGINT UNSIGNED NULL,
  `to_branch_id` BIGINT UNSIGNED NULL,
  `from_division_id` BIGINT UNSIGNED NULL,
  `to_division_id` BIGINT UNSIGNED NULL,
  `swapped_by` VARCHAR(50) NULL,
  `swapped_at` TIMESTAMP NULL,
  `remarks` VARCHAR(500) NULL,
  `receive_remarks` VARCHAR(500) NULL,
  `received_by` VARCHAR(50) NULL,
  `received_at` TIMESTAMP NULL,
  `status` TINYINT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_swap_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_out_gate_pass_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stock_out_no` VARCHAR(191) NOT NULL,
  `event_type` VARCHAR(32) NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `org_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_gp_stock_out` (`stock_out_no`, `event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- F. Procurement (see also database/requisition_quotation_oracle.sql)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `requisitions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `branch_id` BIGINT UNSIGNED NULL,
  `requisition_no` VARCHAR(100) NULL,
  `requisition_date` DATE NULL,
  `employee_id` VARCHAR(100) NULL,
  `employee_name` VARCHAR(255) NULL,
  `notes` TEXT NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisitions_no_unique` (`requisition_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `requisition_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `requisition_id` BIGINT UNSIGNED NOT NULL,
  `item_id` BIGINT UNSIGNED NULL,
  `item_name` VARCHAR(400) NOT NULL,
  `quantity` DECIMAL(12,2) DEFAULT 1,
  `uom` VARCHAR(50) NULL,
  `notes` TEXT NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_req_items_req` (`requisition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `branch_id` BIGINT UNSIGNED NULL,
  `supplier_id` BIGINT UNSIGNED NULL,
  `quotation_no` VARCHAR(100) NULL,
  `quotation_date` DATE NULL,
  `employee_id` VARCHAR(100) NULL,
  `employee_name` VARCHAR(255) NULL,
  `subtotal` DECIMAL(12,2) DEFAULT 0,
  `discount_amount` DECIMAL(12,2) DEFAULT 0,
  `vat_amount` DECIMAL(12,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) DEFAULT 0,
  `note` TEXT NULL,
  `status` VARCHAR(50) DEFAULT 'draft',
  `active_status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_no_unique` (`quotation_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` BIGINT UNSIGNED NOT NULL,
  `item_id` BIGINT UNSIGNED NULL,
  `item_name` VARCHAR(400) NOT NULL,
  `description` TEXT NULL,
  `quantity` DECIMAL(10,2) DEFAULT 1,
  `uom` VARCHAR(50) NULL,
  `unit_price` DECIMAL(12,2) DEFAULT 0,
  `discount` DECIMAL(12,2) DEFAULT 0,
  `vat` DECIMAL(12,2) DEFAULT 0,
  `tax` DECIMAL(12,2) DEFAULT 0,
  `line_total` DECIMAL(12,2) DEFAULT 0,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qt_items_qt` (`quotation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation_approvals` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` BIGINT UNSIGNED NOT NULL,
  `approval_level` VARCHAR(50) NULL,
  `approved_by` BIGINT UNSIGNED NULL,
  `approver_name` VARCHAR(255) NULL,
  `approver_designation` VARCHAR(255) NULL,
  `comments` TEXT NULL,
  `status` VARCHAR(20) DEFAULT 'pending',
  `approved_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_qt_appr_qt` (`quotation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_challans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `branch_id` BIGINT UNSIGNED NULL,
  `challan_no` VARCHAR(100) NOT NULL,
  `challan_date` DATE NULL,
  `buyer_name` VARCHAR(255) NULL,
  `buyer_address` VARCHAR(500) NULL,
  `delivery_location` VARCHAR(255) NULL,
  `reference_no` VARCHAR(100) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_challans_no_unique` (`challan_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_challan_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `delivery_challan_id` BIGINT UNSIGNED NOT NULL,
  `item_inventory_id` BIGINT UNSIGNED NULL,
  `item_id` BIGINT UNSIGNED NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `description` VARCHAR(500) NULL,
  `quantity` DECIMAL(10,2) DEFAULT 1,
  `uom` VARCHAR(50) NULL,
  `warranty` VARCHAR(100) NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dc_items_dc` (`delivery_challan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_returns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` BIGINT UNSIGNED DEFAULT 1,
  `return_no` VARCHAR(100) NULL,
  `return_date` DATE NULL,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_return_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_return_id` BIGINT UNSIGNED NOT NULL,
  `item_id` BIGINT UNSIGNED NULL,
  `item_name` VARCHAR(400) NULL,
  `quantity` DECIMAL(10,2) DEFAULT 1,
  `status` TINYINT DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- G. Laravel helpers
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Optional seed:
-- INSERT INTO category (org_id, category_title, category, status) VALUES (1, 'IT Equipment', 'IT Equipment', 1);

-- END PAD_INVENTORY_A_TO_Z_MYSQL.sql
