USE vehicle_spare_parts;

-- Optional starter data. No default admin password is inserted here.

INSERT INTO country (country_name, country_code, import_duty_rate)
VALUES
    ('Japan', 'JP', 0.00),
    ('Germany', 'DE', 0.00),
    ('Sri Lanka', 'LK', 0.00);

INSERT INTO category (parent_category_id, category_name, description)
VALUES
    (NULL, 'Brakes', 'Brake system spare parts'),
    (NULL, 'Engine', 'Engine-related spare parts'),
    (NULL, 'Electrical', 'Electrical spare parts');

INSERT INTO vehicle_make (make_name)
VALUES ('Toyota'), ('Honda'), ('Nissan');

INSERT INTO payment_gateway (gateway_name, api_endpoint, is_active, transaction_fee_rate)
VALUES ('PayHere', NULL, TRUE, 0.00);
