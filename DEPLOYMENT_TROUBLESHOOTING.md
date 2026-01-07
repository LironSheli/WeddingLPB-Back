# Hostinger Deployment Troubleshooting

## Issue: "Command unexpectedly terminated without error message"

This error typically occurs when `composer install` fails due to:

-   Memory limits
-   Timeout issues
-   PHP version incompatibility
-   Missing PHP extensions

## Solutions

### 1. Commit composer.lock Changes

First, make sure `composer.lock` is committed:

```bash
git add composer.lock
git commit -m "Update composer.lock"
git push origin main
```

### 2. Check Hostinger PHP Version

Hostinger needs PHP 8.2+ for Laravel 12. Check your PHP version in Hostinger control panel:

-   Go to **Advanced** → **PHP Configuration**
-   Ensure PHP 8.2 or higher is selected

### 3. Increase Memory Limits

If Hostinger allows custom `.htaccess` or `php.ini`, add:

```ini
memory_limit = 512M
max_execution_time = 600
```

### 4. Manual Deployment via SSH

If auto-deployment fails, use the `deploy.sh` script:

```bash
./deploy.sh
```

Or manually via SSH:

```bash
ssh -p 65002 u798450678@153.92.220.217
cd domains/lumopages.com/public_html/api
git pull origin main
php -d memory_limit=512M composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
```

### 5. Check Required PHP Extensions

Ensure these extensions are enabled on Hostinger:

-   `php-mbstring`
-   `php-xml`
-   `php-curl`
-   `php-zip`
-   `php-gd`
-   `php-mysql`

### 6. Alternative: Pre-install Dependencies

If composer install keeps failing, you can:

1. Install dependencies locally
2. Commit the `vendor` directory (not recommended, but works)
3. Or use a CI/CD pipeline to build and deploy

### 7. Contact Hostinger Support

If issues persist, contact Hostinger support with:

-   PHP version
-   Composer version
-   Error logs from `storage/logs/laravel.log`
-   Memory limit settings

## Quick Fix Commands

```bash
# Via SSH
cd domains/lumopages.com/public_html/api
git pull origin main
php -d memory_limit=512M composer install --no-dev --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

## Prevention

1. Always commit `composer.lock` after dependency changes
2. Test deployment locally first
3. Use `--no-dev` flag in production
4. Monitor Hostinger resource limits
