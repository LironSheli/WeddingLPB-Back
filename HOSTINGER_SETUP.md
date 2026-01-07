# Hostinger Setup Guide

## Document Root Configuration

For Laravel to work correctly on Hostinger, you have two options:

### Option 1: Set Document Root to `public/` (Recommended)

1. In Hostinger control panel, set the document root to:

    ```
    domains/lumopages.com/public_html/api/public
    ```

2. Then you can delete or ignore the root `.htaccess` file

3. URLs will be: `https://honeydew-bee-627367.hostingersite.com/api/health`

### Option 2: Keep Document Root at Laravel Root

1. Keep document root at:

    ```
    domains/lumopages.com/public_html/api
    ```

2. The root `.htaccess` will route requests to `public/index.php`

3. URLs will be: `https://honeydew-bee-627367.hostingersite.com/api/health`

## Current Setup

Based on your `deploy.sh`, the Laravel app is deployed to:

```
domains/lumopages.com/public_html/api
```

## Testing

After deployment, test these endpoints:

1. **Health Check** (no auth required):

    ```
    https://honeydew-bee-627367.hostingersite.com/api/health
    ```

2. **Login** (public):

    ```
    POST https://honeydew-bee-627367.hostingersite.com/api/login
    ```

3. **Wedding Pages** (requires auth):
    ```
    GET https://honeydew-bee-627367.hostingersite.com/api/wedding-pages
    ```

## Troubleshooting 404 Errors

If you get 404 errors:

1. **Check document root**:

    - Should be `public_html/api/public` (Option 1) OR
    - Should be `public_html/api` with root `.htaccess` routing to `public/` (Option 2)

2. **Check .htaccess**:

    - Root `.htaccess` should route to `public/index.php`
    - `public/.htaccess` should route to `index.php`

3. **Clear Laravel caches**:

    ```bash
    php artisan route:clear
    php artisan config:clear
    php artisan cache:clear
    ```

4. **Check route registration**:

    ```bash
    php artisan route:list | grep api
    ```

5. **Verify mod_rewrite is enabled**:
    - Check in Hostinger control panel
    - Or create a test `.htaccess` with `RewriteEngine On`

## File Permissions

Make sure these directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

## Environment Variables

Ensure `.env` file exists and has correct values:

-   `APP_URL=https://honeydew-bee-627367.hostingersite.com`
-   `APP_ENV=production`
-   Database credentials
-   All API keys
