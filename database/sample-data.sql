-- ============================================================
-- Vehicle Spare Parts Management System
-- Comprehensive sample / demo data
-- Run AFTER schema.sql
-- ============================================================

USE vehicle_spare_parts;

-- ============================================================
-- Countries
-- ============================================================
INSERT IGNORE INTO country (country_name, country_code, import_duty_rate) VALUES
    ('Japan',        'JP', 0.00),
    ('Germany',      'DE', 5.00),
    ('Sri Lanka',    'LK', 0.00),
    ('South Korea',  'KR', 3.50),
    ('Italy',        'IT', 5.00),
    ('United States','US', 4.00),
    ('China',        'CN', 10.00);

-- ============================================================
-- Categories
-- ============================================================
INSERT IGNORE INTO category (parent_category_id, category_name, description) VALUES
    (NULL, 'Brakes',      'Brake system components'),
    (NULL, 'Engine',      'Engine-related spare parts'),
    (NULL, 'Electrical',  'Electrical and ignition components'),
    (NULL, 'Filters',     'Air, oil and fuel filters'),
    (NULL, 'Suspension',  'Suspension and steering parts'),
    (NULL, 'Transmission','Gearbox and transmission parts'),
    (NULL, 'Lighting',    'Lights, bulbs and indicators'),
    (NULL, 'Body',        'Body panels and exterior parts');

-- ============================================================
-- Brands
-- (country_id: JP=1, DE=2, LK=3, KR=4, IT=5, US=6, CN=7)
-- ============================================================
INSERT IGNORE INTO brand (country_id, brand_name, is_authorized) VALUES
    (2, 'Bosch',        TRUE),
    (1, 'Denso',        TRUE),
    (1, 'NGK',          TRUE),
    (5, 'Brembo',       TRUE),
    (2, 'Mann Filter',  TRUE),
    (1, 'Aisin',        TRUE),
    (4, 'Mobis',        FALSE),
    (2, 'Continental',  TRUE),
    (6, 'ACDelco',      TRUE),
    (1, 'Exedy',        TRUE);

-- ============================================================
-- Suppliers
-- ============================================================
INSERT IGNORE INTO supplier (country_id, supplier_name, email, phone, address) VALUES
    (1, 'Japan Auto Parts Co.',    'info@japanauto.jp',      '+81-3-1234-5678', 'Tokyo, Japan'),
    (2, 'German Parts GmbH',       'sales@germanparts.de',   '+49-89-9876-5432', 'Munich, Germany'),
    (3, 'Lanka Auto Traders',      'sales@lankauto.lk',      '+94-11-456-7890', 'Colombo, Sri Lanka'),
    (4, 'Kortek Auto Supplies',    'order@kortek.kr',        '+82-2-5555-1234', 'Seoul, South Korea'),
    (5, 'Italian Brake Systems',   'contact@itbrake.it',     '+39-02-1234-9876', 'Milan, Italy');

-- ============================================================
-- Vehicle Makes
-- ============================================================
INSERT IGNORE INTO vehicle_make (make_name) VALUES
    ('Toyota'),
    ('Honda'),
    ('Nissan'),
    ('Suzuki'),
    ('BMW'),
    ('Hyundai'),
    ('Mazda');

-- ============================================================
-- Vehicle Models
-- (make_id: Toyota=1, Honda=2, Nissan=3, Suzuki=4, BMW=5, Hyundai=6, Mazda=7)
-- ============================================================
INSERT IGNORE INTO vehicle_model (make_id, model_name, year_from, year_to) VALUES
    (1, 'Corolla',    2000, 2023),
    (1, 'Allion',     2001, 2020),
    (1, 'Prius',      2003, 2023),
    (1, 'Land Cruiser',1990, 2023),
    (2, 'Civic',      1995, 2023),
    (2, 'CR-V',       1996, 2023),
    (2, 'Accord',     1998, 2023),
    (3, 'Sunny',      1998, 2018),
    (3, 'X-Trail',    2000, 2023),
    (4, 'Swift',      2005, 2023),
    (4, 'Alto',       2000, 2020),
    (5, '3 Series',   1990, 2023),
    (5, '5 Series',   1990, 2023),
    (6, 'i20',        2008, 2023),
    (7, 'Demio',      1996, 2022);

-- ============================================================
-- Spare Parts
-- ============================================================
INSERT IGNORE INTO spare_part
    (category_id, brand_id, supplier_id, part_name, part_number, oem_number,
     description, price, size, stock_qty, min_stock_level, reorder_level, status)
VALUES
    -- Brakes (cat 1)
    (1, 4, 5, 'Brembo Front Brake Pad Set',    'BP-BRM-001', '04465-12490',
     'High-performance front brake pads compatible with multiple Toyota models.',
     4500.00, 'Standard', 35, 5, 10, 'ACTIVE'),

    (1, 4, 5, 'Brembo Rear Brake Pad Set',     'BP-BRM-002', '04466-02070',
     'Rear brake pads, reliable stopping power.',
     3800.00, 'Standard', 20, 5, 8, 'ACTIVE'),

    (1, 1, 2, 'Bosch Brake Disc Front',        'BD-BSH-001', '0986478036',
     'Vented front brake disc for compact cars.',
     6200.00, '256mm', 15, 3, 5, 'ACTIVE'),

    -- Engine (cat 2)
    (2, 2, 1, 'Denso Timing Belt',             'TB-DNS-001', '130C10140',
     'OEM-quality timing belt for Toyota and Honda engines.',
     3200.00, 'K24A', 25, 5, 8, 'ACTIVE'),

    (2, 6, 1, 'Aisin Water Pump',              'WP-AIS-001', '161000H010',
     'Direct-fit OEM water pump for Toyota engines.',
     8900.00, '1ZZ-FE', 10, 2, 4, 'ACTIVE'),

    -- Electrical / Ignition (cat 3)
    (3, 3, 1, 'NGK Spark Plug (Single)',       'SP-NGK-001', 'BCPR6EW',
     'Copper core spark plug, wide compatibility.',
     450.00, '14mm', 150, 20, 40, 'ACTIVE'),

    (3, 3, 1, 'NGK Iridium Spark Plug',        'SP-NGK-002', 'ILFR6T11',
     'Long-life iridium tipped spark plug for improved fuel efficiency.',
     1200.00, '14mm', 80, 10, 20, 'ACTIVE'),

    (3, 1, 2, 'Bosch Alternator',              'AL-BSH-001', '0120489371',
     'Remanufactured alternator, 14V 70A output.',
     18500.00, '70A', 8, 2, 3, 'ACTIVE'),

    -- Filters (cat 4)
    (4, 5, 2, 'Mann Oil Filter',               'OF-MAN-001', '1109.Y4',
     'High-efficiency spin-on oil filter, traps contaminants down to 20 microns.',
     850.00, 'M20x1.5', 90, 15, 25, 'ACTIVE'),

    (4, 5, 2, 'Mann Air Filter',               'AF-MAN-001', 'C28136',
     'Panel air filter for Toyota Corolla and Allion.',
     1100.00, '230x185mm', 60, 10, 15, 'ACTIVE'),

    (4, 2, 1, 'Denso Fuel Filter',             'FF-DNS-001', '23300-59115',
     'Inline fuel filter for multi-port injection engines.',
     1400.00, 'Inline', 40, 8, 12, 'ACTIVE'),

    -- Suspension (cat 5)
    (5, 1, 2, 'Bosch Front Shock Absorber',    'SA-BSH-001', '0986435003',
     'Gas-pressurised front shock absorber, direct fit.',
     7500.00, 'Standard', 12, 2, 4, 'ACTIVE'),

    (5, 6, 1, 'Aisin Rear Shock Absorber',     'SA-AIS-002', '48531-20451',
     'Twin-tube rear shock absorber for Toyota Corolla.',
     6800.00, 'Standard', 10, 2, 4, 'ACTIVE'),

    -- Lighting (cat 7)
    (7, 1, 2, 'Bosch H4 Halogen Bulb (pair)',  'BL-BSH-001', '1987302049',
     'H4 P43t 60/55W halogen headlamp bulb, pack of two.',
     1350.00, 'H4', 120, 20, 30, 'ACTIVE'),

    (7, 3, 1, 'NGK T10 Side Indicator Bulb',   'BL-NGK-003', 'T10-5W',
     'Festoon T10 parking/indicator bulb.',
     250.00, 'T10', 200, 30, 50, 'ACTIVE');

-- ============================================================
-- Part-Vehicle Compatibility
-- part_id lookup order matches INSERT order above (1-15)
-- model_id: Corolla=1, Allion=2, Prius=3, LandCruiser=4,
--           Civic=5, CR-V=6, Accord=7, Sunny=8, X-Trail=9,
--           Swift=10, Alto=11, 3Series=12, 5Series=13, i20=14, Demio=15
-- ============================================================

-- Brembo Front Pad (part 1) → Corolla, Allion, Civic, Accord
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BP-BRM-001'
  AND vm.model_name IN ('Corolla','Allion','Civic','Accord');

-- Brembo Rear Pad (part 2) → Corolla, Allion
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BP-BRM-002'
  AND vm.model_name IN ('Corolla','Allion','Prius');

-- Bosch Brake Disc (part 3) → Corolla, Civic, Mazda Demio
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BD-BSH-001'
  AND vm.model_name IN ('Corolla','Civic','Demio');

-- Denso Timing Belt → Corolla, Allion, Civic, CR-V
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'TB-DNS-001'
  AND vm.model_name IN ('Corolla','Allion','Civic','CR-V');

-- Aisin Water Pump → Corolla, Allion, Prius
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'WP-AIS-001'
  AND vm.model_name IN ('Corolla','Allion','Prius');

-- NGK Spark Plugs → all Toyota, Honda, Nissan, Suzuki
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SP-NGK-001'
  AND vm.model_name IN ('Corolla','Allion','Prius','Civic','CR-V','Accord','Sunny','X-Trail','Swift','Alto','Demio');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SP-NGK-002'
  AND vm.model_name IN ('Corolla','Allion','Prius','Civic','CR-V','Accord','Sunny','Swift');

-- Bosch Alternator → Corolla, Civic, 3 Series
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'AL-BSH-001'
  AND vm.model_name IN ('Corolla','Civic','3 Series');

-- Mann Oil Filter → Corolla, Allion, Sunny, i20
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'OF-MAN-001'
  AND vm.model_name IN ('Corolla','Allion','Sunny','i20');

-- Mann Air Filter → Corolla, Allion
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'AF-MAN-001'
  AND vm.model_name IN ('Corolla','Allion');

-- Denso Fuel Filter → Corolla, Civic, X-Trail
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'FF-DNS-001'
  AND vm.model_name IN ('Corolla','Civic','X-Trail');

-- Bosch Front Shock → Corolla, Civic, Sunny
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SA-BSH-001'
  AND vm.model_name IN ('Corolla','Civic','Sunny');

-- Aisin Rear Shock → Corolla, Allion
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SA-AIS-002'
  AND vm.model_name IN ('Corolla','Allion');

-- H4 Halogen Bulb → most models
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BL-BSH-001'
  AND vm.model_name IN ('Corolla','Allion','Prius','Civic','Accord','Sunny','X-Trail','Swift','Alto','Demio');

-- T10 Bulb → all models
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BL-NGK-003';

-- ============================================================
-- PayHere gateway (already seeded in minimal sample-data, use IGNORE)
-- ============================================================
INSERT IGNORE INTO payment_gateway (gateway_name, api_endpoint, is_active, transaction_fee_rate)
VALUES ('PayHere', 'https://sandbox.payhere.lk/pay/checkout', TRUE, 2.00);

-- ============================================================
-- END OF SAMPLE DATA
-- ============================================================

-- ============================================================
-- Apply Realistic Images
-- ============================================================
UPDATE spare_part SET image_url = '/assets/images/products/brembo_brake_pad.png' WHERE part_id IN (1, 2);
UPDATE spare_part SET image_url = '/assets/images/products/denso_timing_belt.png' WHERE part_id = 4;
UPDATE spare_part SET image_url = '/assets/images/products/ngk_spark_plug.png' WHERE part_id IN (6, 7);
UPDATE spare_part SET image_url = '/assets/images/products/mann_oil_filter.png' WHERE part_id = 9;
UPDATE spare_part SET image_url = '/assets/images/products/bosch_shock_absorber.png' WHERE part_id = 12;
UPDATE spare_part SET image_url = '/assets/images/products/bosch_h4_bulb.png' WHERE part_id = 14;

-- ============================================================
-- Additional Parts (Batch 2)
-- ============================================================

INSERT IGNORE INTO spare_part
    (category_id, brand_id, supplier_id, part_name, part_number, oem_number,
     description, price, size, stock_qty, min_stock_level, reorder_level, status, image_url)
VALUES
    -- Brakes (cat 1)
    (1, 4, 5, 'Brembo Brake Rotor Disc', 'BD-BRM-002', '0986479111',
     'High-performance vented brake rotor for enhanced stopping power.',
     7500.00, '280mm', 18, 4, 8, 'ACTIVE', '/assets/images/products/brembo_brake_rotor.png'),

    -- Engine (cat 2)
    (2, 6, 1, 'Aisin Engine Mount', 'EM-AIS-001', '12361-16290',
     'Durable rubber engine mount to reduce vibration.',
     4200.00, 'Standard', 14, 3, 5, 'ACTIVE', '/assets/images/products/aisin_engine_mount.png'),

    -- Electrical / Ignition (cat 3)
    (3, 9, 2, 'ACDelco Gold Car Battery', 'BAT-ACD-001', '34AGM',
     'Reliable 12V automotive battery with high cranking amps.',
     25000.00, '12V 65Ah', 22, 5, 10, 'ACTIVE', '/assets/images/products/acdelco_car_battery.png'),

    -- Filters (cat 4)
    (4, 5, 2, 'Mann Cabin Air Filter', 'CF-MAN-001', 'CUK 19 004',
     'Activated carbon cabin filter for clean interior air.',
     2100.00, 'Standard', 45, 10, 15, 'ACTIVE', '/assets/images/products/mann_cabin_filter.png'),

    -- Suspension (cat 5)
    (5, 7, 4, 'Mobis Lower Control Arm', 'CA-MBS-001', '54500-2K000',
     'Genuine front lower control arm assembly with bushings.',
     12500.00, 'Front Left', 8, 2, 4, 'ACTIVE', '/assets/images/products/mobis_control_arm.png'),

    -- Transmission (cat 6)
    (6, 10, 1, 'Exedy Clutch Kit', 'CK-EXD-001', 'HCK2043',
     'Complete clutch kit including disc and pressure plate.',
     18000.00, '200mm', 12, 2, 4, 'ACTIVE', '/assets/images/products/exedy_clutch_kit.png'),

    -- Lighting (cat 7)
    (7, 1, 1, 'Denso LED Headlight Bulb', 'LED-DNS-001', '0986AL1513',
     'Ultra-bright white LED headlight bulb upgrade.',
     6800.00, 'H4 LED', 35, 8, 12, 'ACTIVE', NULL),

    -- Body (cat 8)
    (8, 7, 4, 'Mobis Front Bumper Cover', 'FB-MBS-001', '86511-1R000',
     'Primed front bumper fascia ready for painting.',
     28000.00, 'Standard', 5, 1, 2, 'ACTIVE', NULL);

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BD-BRM-002' AND vm.model_name IN ('Civic', 'Corolla', 'Accord');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'EM-AIS-001' AND vm.model_name IN ('Corolla', 'Allion');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BAT-ACD-001' AND vm.model_name IN ('Corolla', 'Civic', '3 Series', 'Swift');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'CF-MAN-001' AND vm.model_name IN ('Corolla', 'Allion', 'Prius');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'CA-MBS-001' AND vm.model_name IN ('i20');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'CK-EXD-001' AND vm.model_name IN ('Civic', 'Corolla', 'Demio');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'LED-DNS-001' AND vm.model_name IN ('Corolla', 'Prius', 'Swift', 'X-Trail');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id
FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'FB-MBS-001' AND vm.model_name IN ('i20');
