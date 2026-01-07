# Laravel API Routing Troubleshooting

## Issue: 404 on `/api/wedding-pages`

### Possible Causes

1. **Route requires authentication** - The `/wedding-pages` route is protected by `auth:sanctum` middleware
2. **Laravel routes not registered** - Routes might not be cached or registered properly
3. **.htaccess routing issue** - Apache might not be routing requests correctly
4. **Wrong base path** - The API might be at a different path on the server

## Testing Steps

### 1. Test Health Check Endpoint

First, test if the API is accessible at all:

```bash
curl https://honeydew-bee-627367.hostingersite.com/api/health
```

Expected response:

```json
{
    "status": "ok",
    "message": "API is working",
    "timestamp": "2024-..."
}
```

### 2. Test Public Endpoint

Test a public endpoint (doesn't require auth):

```bash
curl -X POST https://honeydew-bee-627367.hostingersite.com/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### 3. Test Protected Endpoint (with auth)

The `/wedding-pages` endpoint requires authentication. You need to:

1. First login to get a session cookie:

```bash
curl -X POST https://honeydew-bee-627367.hostingersite.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"your@email.com","password":"yourpassword"}' \
  -c cookies.txt
```

2. Then use the cookie to access protected routes:

```bash
curl https://honeydew-bee-627367.hostingersite.com/api/wedding-pages \
  -b cookies.txt
```

## Common Issues and Solutions

### Issue 1: Routes Not Cached

On the server, run:

```bash
cd domains/lumopages.com/public_html/api
php artisan route:clear
php artisan route:cache
php artisan config:clear
php artisan config:cache
```

### Issue 2: .htaccess Not Working

Check that:

1. `.htaccess` file exists in `public/` directory
2. Apache `mod_rewrite` is enabled
3. The document root points to `public/` directory

### Issue 3: Wrong Document Root

On Hostinger, make sure:

-   Document root is set to `public_html/api/public` (not `public_html/api`)
-   Or adjust `.htaccess` in root if document root is `public_html/api`

### Issue 4: Route Not Found (404)

If you get 404 even for `/api/health`:

1. Check Laravel is installed:

```bash
php artisan --version
```

2. Check routes are registered:

```bash
php artisan route:list
```

3. Check if the path is correct:
    - If Laravel is in `public_html/api/`, the URL should be `https://domain.com/api/...`
    - If Laravel is in `public_html/`, the URL should be `https://domain.com/api/...`

### Issue 5: Authentication Required

The `/wedding-pages` route requires authentication. You'll get 401 (Unauthorized) or 404 if:

-   Not logged in
-   Session expired
-   Cookie not sent

Make sure to:

1. Login first via `/api/login`
2. Include session cookie in subsequent requests
3. Use `withCredentials: true` in frontend axios config

## Quick Fixes

### Clear All Caches

```bash
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Rebuild Route Cache

```bash
php artisan route:cache
php artisan config:cache
```

### Check Route Registration

```bash
php artisan route:list | grep wedding-pages
```

## Expected Behavior

-   **Without auth**: Should return 401 Unauthorized (not 404)
-   **With auth**: Should return 200 with wedding pages array
-   **Wrong path**: Returns 404

If you're getting 404 for a route that should exist, it's likely a routing configuration issue.
