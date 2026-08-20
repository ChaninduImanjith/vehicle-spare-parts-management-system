-- ============================================================
-- Vehicle Spare Parts Management System
-- Final database schema matching the agreed single-ADMIN ER
-- MySQL 8+ / MariaDB-compatible design
-- ============================================================

CREATE DATABASE IF NOT EXISTS vehicle_spare_parts
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vehicle_spare_parts;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payment;
DROP TABLE IF EXISTS payment_gateway;
DROP TABLE IF EXISTS order_item;
DROP TABLE IF EXISTS customer_order;
DROP TABLE IF EXISTS product_request;
DROP TABLE IF EXISTS stock_movement;
DROP TABLE IF EXISTS search_log;
DROP TABLE IF EXISTS part_vehicle_compatibility;
DROP TABLE IF EXISTS vehicle_model;
DROP TABLE IF EXISTS vehicle_make;
DROP TABLE IF EXISTS spare_part;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS brand;
DROP TABLE IF EXISTS country;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS guest_user;
DROP TABLE IF EXISTS registered_user;
DROP TABLE IF EXISTS admin;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. ADMIN
-- Single ADMIN entity as requested by the supervisor.
-- No SUPER_ADMIN / ADMIN_PART tables.
-- ============================================================
CREATE TABLE admin (
    admin_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. REGISTERED USER
-- ============================================================
CREATE TABLE registered_user (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25) NULL,
    address VARCHAR(255) NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE
) ENGINE=InnoDB;

-- ============================================================
-- 3. GUEST USER
-- One session_id identifies an anonymous browsing session.
-- ============================================================
CREATE TABLE guest_user (
    session_id VARCHAR(128) PRIMARY KEY,
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 4. CATEGORY
-- Self-reference supports subcategories.
-- ============================================================
CREATE TABLE category (
    category_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_category_id INT UNSIGNED NULL,
    category_name VARCHAR(100) NOT NULL,
    description TEXT NULL,

    CONSTRAINT fk_category_parent
        FOREIGN KEY (parent_category_id)
        REFERENCES category(category_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    UNIQUE KEY uq_category_name_parent (category_name, parent_category_id)
) ENGINE=InnoDB;

-- ============================================================
-- 5. COUNTRY
-- ============================================================
CREATE TABLE country (
    country_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_name VARCHAR(100) NOT NULL UNIQUE,
    country_code CHAR(2) NOT NULL UNIQUE,
    import_duty_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT chk_country_import_duty
        CHECK (import_duty_rate >= 0)
) ENGINE=InnoDB;

-- ============================================================
-- 6. BRAND
-- One country can originate many brands.
-- ============================================================
CREATE TABLE brand (
    brand_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id INT UNSIGNED NOT NULL,
    brand_name VARCHAR(100) NOT NULL,
    is_authorized BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT fk_brand_country
        FOREIGN KEY (country_id)
        REFERENCES country(country_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE KEY uq_brand_country (brand_name, country_id),
    KEY idx_brand_country (country_id)
) ENGINE=InnoDB;

-- ============================================================
-- 7. SUPPLIER
-- One country can have many suppliers.
-- ============================================================
CREATE TABLE supplier (
    supplier_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    country_id INT UNSIGNED NOT NULL,
    supplier_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(25) NULL,
    address VARCHAR(255) NULL,

    CONSTRAINT fk_supplier_country
        FOREIGN KEY (country_id)
        REFERENCES country(country_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    UNIQUE KEY uq_supplier_country (supplier_name, country_id),
    KEY idx_supplier_country (country_id)
) ENGINE=InnoDB;

-- ============================================================
-- 8. SPARE PART
-- Matches the agreed ER: category, brand and supplier are FKs.
-- ============================================================
CREATE TABLE spare_part (
    part_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    brand_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    part_name VARCHAR(150) NOT NULL,
    part_number VARCHAR(100) NOT NULL UNIQUE,
    oem_number VARCHAR(100) NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL,
    size VARCHAR(100) NULL,
    stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
    min_stock_level INT UNSIGNED NOT NULL DEFAULT 0,
    reorder_level INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    status ENUM('ACTIVE', 'INACTIVE', 'DISCONTINUED') NOT NULL DEFAULT 'ACTIVE',

    CONSTRAINT fk_spare_part_category
        FOREIGN KEY (category_id)
        REFERENCES category(category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_spare_part_brand
        FOREIGN KEY (brand_id)
        REFERENCES brand(brand_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_spare_part_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES supplier(supplier_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_spare_part_price
        CHECK (price >= 0),

    KEY idx_spare_part_category (category_id),
    KEY idx_spare_part_brand (brand_id),
    KEY idx_spare_part_supplier (supplier_id),
    KEY idx_spare_part_oem_number (oem_number),
    KEY idx_spare_part_name (part_name),
    KEY idx_spare_part_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 9. VEHICLE MAKE
-- ============================================================
CREATE TABLE vehicle_make (
    make_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    make_name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ============================================================
-- 10. VEHICLE MODEL
-- ============================================================
CREATE TABLE vehicle_model (
    model_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    make_id INT UNSIGNED NOT NULL,
    model_name VARCHAR(120) NOT NULL,
    year_from SMALLINT UNSIGNED NULL,
    year_to SMALLINT UNSIGNED NULL,

    CONSTRAINT fk_vehicle_model_make
        FOREIGN KEY (make_id)
        REFERENCES vehicle_make(make_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT chk_vehicle_model_years
        CHECK (
            year_from IS NULL
            OR year_to IS NULL
            OR year_to >= year_from
        ),

    UNIQUE KEY uq_vehicle_model_period (
        make_id,
        model_name,
        year_from,
        year_to
    ),
    KEY idx_vehicle_model_make (make_id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. PART VEHICLE COMPATIBILITY
-- Resolves M:N between spare parts and vehicle models.
-- ============================================================
CREATE TABLE part_vehicle_compatibility (
    compatibility_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    part_id INT UNSIGNED NOT NULL,
    model_id INT UNSIGNED NOT NULL,

    CONSTRAINT fk_compatibility_part
        FOREIGN KEY (part_id)
        REFERENCES spare_part(part_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_compatibility_model
        FOREIGN KEY (model_id)
        REFERENCES vehicle_model(model_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    UNIQUE KEY uq_part_model (part_id, model_id),
    KEY idx_compatibility_model (model_id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. SEARCH LOG
-- Either a registered user OR a guest session owns each log.
-- Filter values are stored as snapshots for reporting.
-- ============================================================
CREATE TABLE search_log (
    search_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(128) NULL,
    search_keyword VARCHAR(255) NULL,
    filter_category VARCHAR(100) NULL,
    filter_brand VARCHAR(100) NULL,
    filter_country VARCHAR(100) NULL,
    filter_vehicle_make VARCHAR(100) NULL,
    filter_vehicle_model VARCHAR(120) NULL,
    filter_size VARCHAR(100) NULL,
    price_min DECIMAL(12,2) NULL,
    price_max DECIMAL(12,2) NULL,
    results_count INT UNSIGNED NOT NULL DEFAULT 0,
    searched_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_search_log_user
        FOREIGN KEY (user_id)
        REFERENCES registered_user(user_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_search_log_guest
        FOREIGN KEY (session_id)
        REFERENCES guest_user(session_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_search_log_owner
        CHECK (
            (user_id IS NOT NULL AND session_id IS NULL)
            OR
            (user_id IS NULL AND session_id IS NOT NULL)
        ),

    CONSTRAINT chk_search_log_price
        CHECK (
            price_min IS NULL
            OR price_max IS NULL
            OR price_max >= price_min
        ),

    KEY idx_search_log_user (user_id),
    KEY idx_search_log_session (session_id),
    KEY idx_search_log_date (searched_at)
) ENGINE=InnoDB;

-- ============================================================
-- 13. STOCK MOVEMENT
-- Admin records all stock changes.
-- ============================================================
CREATE TABLE stock_movement (
    movement_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    part_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    movement_type ENUM('STOCK_IN', 'SALE', 'RETURN', 'ADJUSTMENT') NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id BIGINT UNSIGNED NULL,
    note VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_stock_movement_part
        FOREIGN KEY (part_id)
        REFERENCES spare_part(part_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_stock_movement_admin
        FOREIGN KEY (admin_id)
        REFERENCES admin(admin_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_stock_movement_quantity
        CHECK (quantity > 0),

    KEY idx_stock_movement_part (part_id),
    KEY idx_stock_movement_admin (admin_id),
    KEY idx_stock_movement_date (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 14. CUSTOMER ORDER
-- Only registered users place orders in the agreed ER.
-- ============================================================
CREATE TABLE customer_order (
    order_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    final_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM(
        'PENDING',
        'PAID',
        'PROCESSING',
        'PACKED',
        'SHIPPED',
        'DELIVERED',
        'CANCELLED'
    ) NOT NULL DEFAULT 'PENDING',
    shipping_address VARCHAR(255) NOT NULL,
    tracking_number VARCHAR(100) NULL UNIQUE,
    delivery_date DATETIME NULL,

    CONSTRAINT fk_customer_order_user
        FOREIGN KEY (user_id)
        REFERENCES registered_user(user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_order_amounts
        CHECK (
            total_amount >= 0
            AND discount_amount >= 0
            AND tax_amount >= 0
            AND final_amount >= 0
        ),

    KEY idx_customer_order_user (user_id),
    KEY idx_customer_order_status (status),
    KEY idx_customer_order_date (order_date)
) ENGINE=InnoDB;

-- ============================================================
-- 15. ORDER ITEM
-- Stores a price snapshot for the order.
-- ============================================================
CREATE TABLE order_item (
    order_item_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    part_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,

    CONSTRAINT fk_order_item_order
        FOREIGN KEY (order_id)
        REFERENCES customer_order(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_item_part
        FOREIGN KEY (part_id)
        REFERENCES spare_part(part_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_order_item_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_order_item_prices
        CHECK (unit_price >= 0 AND subtotal >= 0),

    UNIQUE KEY uq_order_part (order_id, part_id),
    KEY idx_order_item_part (part_id)
) ENGINE=InnoDB;

-- ============================================================
-- 16. PAYMENT GATEWAY
-- ============================================================
CREATE TABLE payment_gateway (
    gateway_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gateway_name VARCHAR(100) NOT NULL UNIQUE,
    api_endpoint VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    transaction_fee_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,

    CONSTRAINT chk_gateway_fee
        CHECK (transaction_fee_rate >= 0)
) ENGINE=InnoDB;

-- ============================================================
-- 17. PAYMENT
-- One order has at most one payment row in this final ER.
-- ============================================================
CREATE TABLE payment (
    payment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    gateway_id INT UNSIGNED NOT NULL,
    transaction_id VARCHAR(150) NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('PENDING', 'PAID', 'FAILED', 'CANCELLED', 'REFUNDED') NOT NULL DEFAULT 'PENDING',
    paid_at DATETIME NULL,
    refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    receipt_url VARCHAR(500) NULL,

    CONSTRAINT fk_payment_order
        FOREIGN KEY (order_id)
        REFERENCES customer_order(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_payment_gateway
        FOREIGN KEY (gateway_id)
        REFERENCES payment_gateway(gateway_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_payment_amounts
        CHECK (
            amount >= 0
            AND refund_amount >= 0
            AND refund_amount <= amount
        ),

    KEY idx_payment_gateway (gateway_id),
    KEY idx_payment_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 18. PRODUCT REQUEST
-- Registered user submits; one admin may be assigned to handle.
-- No direct Product Request -> Customer Order relationship.
-- ============================================================
CREATE TABLE product_request (
    request_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NULL,
    part_name VARCHAR(150) NOT NULL,
    part_description TEXT NULL,
    preferred_brand VARCHAR(100) NULL,
    preferred_country VARCHAR(100) NULL,
    size VARCHAR(100) NULL,
    budget_min DECIMAL(12,2) NULL,
    budget_max DECIMAL(12,2) NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('PENDING', 'REVIEWING', 'APPROVED', 'REJECTED', 'FULFILLED') NOT NULL DEFAULT 'PENDING',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    admin_notes TEXT NULL,
    assigned_at DATETIME NULL,

    CONSTRAINT fk_product_request_user
        FOREIGN KEY (user_id)
        REFERENCES registered_user(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_product_request_admin
        FOREIGN KEY (admin_id)
        REFERENCES admin(admin_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_product_request_quantity
        CHECK (quantity > 0),

    CONSTRAINT chk_product_request_budget
        CHECK (
            budget_min IS NULL
            OR budget_max IS NULL
            OR budget_max >= budget_min
        ),

    KEY idx_product_request_user (user_id),
    KEY idx_product_request_admin (admin_id),
    KEY idx_product_request_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- END OF FINAL SCHEMA
-- ============================================================
