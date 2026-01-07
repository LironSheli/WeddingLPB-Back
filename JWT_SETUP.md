# JWT Authentication Setup

This project uses JWT (JSON Web Tokens) for authentication via `tymon/jwt-auth` package.

## Installation Steps

### 1. Install Dependencies

```bash
cd backend/WeddingLPB-Back
composer install
```

This will install `tymon/jwt-auth` package.

### 2. Register JWT Service Provider (Laravel 11)

In Laravel 11, service providers need to be manually registered. The JWT service provider has been added to `bootstrap/providers.php`:

```php
Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
```

**Note**: This is already configured in this project. If you're setting up from scratch, make sure to add this line.

### 3. Publish JWT Configuration

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

**Note**: The `config/jwt.php` file has been created manually in this project. If you get an error about the JWT driver not being defined, make sure this file exists.

### 4. Generate JWT Secret Key

```bash
php artisan jwt:secret
```

This will generate a secret key and add it to your `.env` file as `JWT_SECRET`.

### 6. Clear Configuration Cache

After setting up JWT, always clear the config cache:

```bash
php artisan config:clear
php artisan config:cache
```

This ensures Laravel recognizes the JWT driver and commands.

### 5. Run Migrations

```bash
php artisan migrate
```

## Configuration

### Environment Variables

Add to your `.env` file:

```env
JWT_SECRET=your-generated-secret-key
JWT_TTL=60  # Token lifetime in minutes (default: 60)
JWT_REFRESH_TTL=20160  # Refresh token lifetime in minutes (default: 20160 = 2 weeks)
```

### Auth Configuration

The `config/auth.php` has been configured with:

-   `api` guard using `jwt` driver
-   `users` provider

### User Model

The `User` model implements `JWTSubject` interface with:

-   `getJWTIdentifier()` - Returns user ID
-   `getJWTCustomClaims()` - Returns custom claims (currently empty)

## API Endpoints

### Authentication

-   `POST /api/register` - Register new user

    -   Returns: `{ user, token, token_type: 'bearer' }`

-   `POST /api/login` - Login

    -   Returns: `{ user, token, token_type: 'bearer' }`

-   `POST /api/logout` - Logout (invalidates token)

    -   Requires: `Authorization: Bearer {token}` header

-   `GET /api/user` - Get current user

    -   Requires: `Authorization: Bearer {token}` header

-   `POST /api/refresh` - Refresh JWT token
    -   Requires: `Authorization: Bearer {token}` header
    -   Returns: `{ token, token_type: 'bearer' }`

### Protected Routes

All routes in the `auth:api` middleware group require a valid JWT token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

## Frontend Integration

The frontend automatically:

1. Stores JWT token in `localStorage` after login/register
2. Adds token to `Authorization` header for all API requests
3. Refreshes token automatically on 401 errors
4. Clears token on logout

### Token Storage

Tokens are stored in browser's `localStorage`:

-   Key: `token`
-   Value: JWT token string

### Automatic Token Refresh

If a request returns 401 (Unauthorized), the frontend will:

1. Attempt to refresh the token using `/api/refresh`
2. Retry the original request with the new token
3. If refresh fails, redirect to login page

## Deployment

### On Server

After deploying, run:

```bash
cd domains/lumopages.com/public_html/api
php artisan jwt:secret  # Only if JWT_SECRET is not set
php artisan config:clear
php artisan config:cache
```

### Environment Variables

Make sure `JWT_SECRET` is set in production `.env` file.

## Security Notes

1. **Token Storage**: Tokens are stored in `localStorage` which is accessible to JavaScript. For higher security, consider using httpOnly cookies (requires backend changes).

2. **Token Expiration**: Default token lifetime is 60 minutes. Adjust `JWT_TTL` in `.env` as needed.

3. **HTTPS**: Always use HTTPS in production to protect tokens in transit.

4. **Token Refresh**: Implemented automatically on the frontend. Tokens are refreshed before expiration.

## Troubleshooting

### "There are no commands defined in the 'jwt' namespace"

This error means the JWT service provider isn't registered. Fix it by:

1. **Check service provider is registered**:

    - Open `bootstrap/providers.php`
    - Ensure `Tymon\JWTAuth\Providers\LaravelServiceProvider::class` is in the array
    - This is already configured in this project

2. **Clear config cache** (important!):

    ```bash
    php artisan config:clear
    ```

3. **Run composer dump-autoload**:

    ```bash
    composer dump-autoload
    ```

4. **Try JWT command again**:
    ```bash
    php artisan jwt:secret
    ```

### "Auth driver [jwt] for guard [api] is not defined"

This error means Laravel doesn't recognize the JWT driver. Fix it by:

1. **Ensure package is installed**:

    ```bash
    composer install
    ```

2. **Verify service provider is registered** in `bootstrap/providers.php`

3. **Publish JWT config** (if not already done):

    ```bash
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    ```

4. **Clear and rebuild config cache**:

    ```bash
    php artisan config:clear
    php artisan config:cache
    ```

5. **Verify config/jwt.php exists**:

    - The file should be at `config/jwt.php`
    - If missing, it's been created in this project - make sure it's deployed

6. **Check JWT_SECRET is set**:

    ```bash
    php artisan jwt:secret
    ```

7. **Restart PHP-FPM** (if using):
    ```bash
    sudo service php8.2-fpm restart
    # or
    sudo systemctl restart php-fpm
    ```

### "JWT Secret not set"

Run: `php artisan jwt:secret`

### "Token could not be parsed"

-   Check token is being sent in Authorization header
-   Verify token hasn't expired
-   Ensure JWT_SECRET matches between environments

### "Token has expired"

-   Frontend should automatically refresh, but if not:
    -   Call `/api/refresh` endpoint
    -   Or re-login to get a new token

### "User not found"

-   Token is valid but user was deleted
-   Clear token and re-login
