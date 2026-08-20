# Vehicle Spare Parts Management System

Framework-free full-stack university project using:

- HTML5
- CSS3
- Vanilla JavaScript
- PHP 8+
- MySQL 8+
- Git / GitHub
- GitHub Actions CI

## Branch strategy

- `main` - final stable / production-ready code
- `develop` - integration branch
- `feature/m1-core-admin`
- `feature/m2-store-search`
- `feature/m3-customer-orders`
- `feature/m4-inventory-supplier`

## Local setup

1. Clone the repository.
2. Create `config/config.local.php` from `config/config.example.php`.
3. Import `database/schema.sql` into MySQL.
4. Update your own local database credentials in `config/config.local.php`.
5. Run the project through Apache/PHP (XAMPP, Laragon, LAMP, etc.).

## Important

Never commit real database passwords, FTP credentials, or payment secrets.
