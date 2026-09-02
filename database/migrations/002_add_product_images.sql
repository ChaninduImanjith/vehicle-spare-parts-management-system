-- ============================================================
-- Migration 002: Assign product images to all spare parts
-- Run this after sample-data.sql
-- ============================================================

USE vehicle_spare_parts;

-- Parts from Batch 1 (missing images)
UPDATE spare_part SET image_url = '/assets/images/products/bosch_brake_disc.png'
    WHERE part_number = 'BD-BSH-001';

UPDATE spare_part SET image_url = '/assets/images/products/aisin_water_pump.png'
    WHERE part_number = 'WP-AIS-001';

UPDATE spare_part SET image_url = '/assets/images/products/bosch_alternator.png'
    WHERE part_number = 'AL-BSH-001';

UPDATE spare_part SET image_url = '/assets/images/products/mann_air_filter.png'
    WHERE part_number = 'AF-MAN-001';

UPDATE spare_part SET image_url = '/assets/images/products/denso_fuel_filter.png'
    WHERE part_number = 'FF-DNS-001';

UPDATE spare_part SET image_url = '/assets/images/products/aisin_rear_shock.png'
    WHERE part_number = 'SA-AIS-002';

-- Parts from Batch 2 (missing images)
UPDATE spare_part SET image_url = '/assets/images/products/denso_led_headlight.jpg'
    WHERE part_number = 'LED-DNS-001';

UPDATE spare_part SET image_url = '/assets/images/products/mobis_front_bumper.jpg'
    WHERE part_number = 'FB-MBS-001';

UPDATE spare_part SET image_url = '/assets/images/products/ngk_t10_bulb.jpg'
    WHERE part_number = 'BL-NGK-003';

-- ============================================================
-- Additional Parts Batch 3 – more products per category
-- ============================================================
INSERT IGNORE INTO spare_part
    (category_id, brand_id, supplier_id, part_name, part_number, oem_number,
     description, price, size, stock_qty, min_stock_level, reorder_level, status, image_url)
VALUES
    -- Brakes (cat 1)
    (1, 1, 2, 'Bosch Rear Brake Disc', 'BD-BSH-002', '0986478044',
     'Solid rear brake disc, direct fit for compact sedans.',
     5200.00, '260mm', 14, 3, 6, 'ACTIVE', '/assets/images/products/bosch_brake_disc.png'),

    (1, 4, 5, 'Brembo Caliper Repair Kit', 'CK-BRM-001', '04478-12070',
     'Complete front brake caliper seal kit.',
     2100.00, 'Standard', 30, 5, 10, 'ACTIVE', '/assets/images/products/brembo_brake_pad.png'),

    -- Engine (cat 2)
    (2, 2, 1, 'Denso Radiator Hose Upper', 'RH-DNS-001', '16571-20040',
     'Upper silicone coolant hose for Toyota engines.',
     1800.00, 'Standard', 22, 5, 8, 'ACTIVE', '/assets/images/products/denso_timing_belt.png'),

    (2, 6, 1, 'Aisin Thermostat', 'TH-AIS-001', '90916-03136',
     '82°C thermostat with housing gasket for Toyota 1ZZ/2ZZ engines.',
     2400.00, '82°C', 18, 4, 7, 'ACTIVE', '/assets/images/products/aisin_water_pump.png'),

    -- Electrical (cat 3)
    (3, 3, 1, 'NGK Iridium IX Spark Plug', 'SP-NGK-003', 'TR5IX',
     'Double iridium fine wire spark plug for maximum performance.',
     1800.00, '14mm', 60, 10, 20, 'ACTIVE', '/assets/images/products/ngk_spark_plug.png'),

    (3, 1, 2, 'Bosch Starter Motor', 'SM-BSH-001', '0001107407',
     'Direct replacement starter motor, 12V 1.1kW.',
     14500.00, '12V', 6, 1, 3, 'ACTIVE', '/assets/images/products/bosch_alternator.png'),

    -- Filters (cat 4)
    (4, 2, 1, 'Denso Oil Filter Premium', 'OF-DNS-001', '90915-YZZE1',
     'Toyota-spec spin-on oil filter, extended service interval.',
     950.00, 'M20x1.5', 75, 15, 25, 'ACTIVE', '/assets/images/products/mann_oil_filter.png'),

    (4, 5, 2, 'Mann Fuel Filter Inline', 'FF-MAN-001', 'WK 842/1',
     'High-flow inline fuel filter for diesel and petrol engines.',
     1650.00, 'Inline', 38, 8, 12, 'ACTIVE', '/assets/images/products/denso_fuel_filter.png'),

    -- Suspension (cat 5)
    (5, 8, 2, 'Continental Stabilizer Link', 'SL-CON-001', '68043793AA',
     'Front anti-roll bar stabilizer end link.',
     3200.00, 'Front', 16, 4, 6, 'ACTIVE', '/assets/images/products/mobis_control_arm.png'),

    (5, 1, 2, 'Bosch Rear Shock Absorber', 'SA-BSH-002', '0986435004',
     'Gas-pressurised rear shock absorber.',
     7200.00, 'Standard', 10, 2, 4, 'ACTIVE', '/assets/images/products/bosch_shock_absorber.png'),

    -- Transmission (cat 6)
    (6, 10, 1, 'Exedy Clutch Disc', 'CD-EXD-001', 'HCD2043',
     'High-quality clutch friction disc, 200mm.',
     8500.00, '200mm', 15, 3, 5, 'ACTIVE', '/assets/images/products/exedy_clutch_kit.png'),

    (6, 6, 1, 'Aisin Gearbox Oil Seal', 'OS-AIS-001', '90311-40024',
     'OEM-spec output shaft gearbox oil seal.',
     650.00, '40x55x8', 50, 10, 15, 'ACTIVE', '/assets/images/products/aisin_engine_mount.png'),

    -- Lighting (cat 7)
    (7, 3, 1, 'NGK H7 Halogen Bulb Pair', 'BL-NGK-004', '1987301002',
     'H7 PX26d 55W halogen headlamp bulb, pack of two.',
     1450.00, 'H7', 100, 20, 30, 'ACTIVE', '/assets/images/products/bosch_h4_bulb.png'),

    (7, 1, 2, 'Bosch LED Interior Light', 'IL-BSH-001', '1987302601',
     'W5W T10 LED interior and map light bulb.',
     800.00, 'T10', 150, 25, 40, 'ACTIVE', '/assets/images/products/ngk_t10_bulb.jpg'),

    -- Body (cat 8)
    (8, 7, 4, 'Mobis Rear Bumper Cover', 'RB-MBS-001', '86621-1R000',
     'Primed rear bumper fascia for Hyundai i20.',
     24500.00, 'Standard', 4, 1, 2, 'ACTIVE', '/assets/images/products/mobis_front_bumper.jpg'),

    (8, 7, 4, 'Mobis Hood Insulator Pad', 'HI-MBS-001', '811241R000',
     'Heat and noise insulation pad for engine hood.',
     3800.00, 'Standard', 8, 2, 3, 'ACTIVE', '/assets/images/products/aisin_engine_mount.png');

-- ============================================================
-- Compatibility for Batch 3 Parts
-- ============================================================
INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BD-BSH-002' AND vm.model_name IN ('Corolla','Allion','Civic');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'CK-BRM-001' AND vm.model_name IN ('Corolla','Civic','Accord');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'RH-DNS-001' AND vm.model_name IN ('Corolla','Allion','Prius');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'TH-AIS-001' AND vm.model_name IN ('Corolla','Allion','Prius','Land Cruiser');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SP-NGK-003' AND vm.model_name IN ('Corolla','Allion','Prius','Civic','CR-V','Swift');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SM-BSH-001' AND vm.model_name IN ('Corolla','Civic','3 Series');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'OF-DNS-001' AND vm.model_name IN ('Corolla','Allion','Prius','Land Cruiser');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'FF-MAN-001' AND vm.model_name IN ('Corolla','Civic','X-Trail','3 Series');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SL-CON-001' AND vm.model_name IN ('Corolla','Civic','Sunny');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'SA-BSH-002' AND vm.model_name IN ('Corolla','Allion','Civic','Demio');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'CD-EXD-001' AND vm.model_name IN ('Civic','Corolla','Demio','Sunny');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'OS-AIS-001' AND vm.model_name IN ('Corolla','Allion','Prius');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'BL-NGK-004' AND vm.model_name IN ('3 Series','5 Series','Corolla','Civic');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'IL-BSH-001';

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'RB-MBS-001' AND vm.model_name IN ('i20');

INSERT IGNORE INTO part_vehicle_compatibility (part_id, model_id)
SELECT sp.part_id, vm.model_id FROM spare_part sp, vehicle_model vm
WHERE sp.part_number = 'HI-MBS-001' AND vm.model_name IN ('i20','Corolla','Civic');

-- ============================================================
-- END OF MIGRATION 002
-- ============================================================
