# AnsarEats — Agent instructions

## Stack
- **Laravel 12** (PHP ^8.2) — backend
- **Filament v5** — admin panel (`app/Filament/`)
- **Tailwind CSS v4** + `@tailwindcss/vite` plugin — styling (not standalone CLI)
- **Alpine.js** — frontend interactivity (`resources/js/`)
- **Alpine Global Stores** (`$store.darkMode`) — dark mode state
- **Laravel Reverb** — WebSocket broadcasting (port 8080, dev-only)
- **Redis** — session, cache, queue (client: `phpredis`)
- **Laravel Socialite** — Google/Facebook OAuth
- **VAPID** (`minishlink/web-push`) — push notifications

## Developer commands
```bash
composer dev              # runs 4 concurrent processes: serve, queue:listen, reverb:start, npm run dev
composer test             # runs `config:clear` then `php artisan test`
php artisan test          # runs PHPUnit
phpunit                   # direct PHPUnit (reads phpunit.xml)
npm run build             # vite build
npm run dev               # vite dev server
php artisan reverb:start  # start WebSocket server (port 8080)
php artisan queue:listen  # process jobs locally
php artisan migrate --seed
php artisan storage:link
```

## Testing
- Config (from `phpunit.xml`): SQLite `:memory:`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`, `CACHE_STORE=array`
- All feature tests use `RefreshDatabase`
- Cart is session-based (`session('cart')`), not DB — tests inject via `$this->withSession(...)`
- Run single test: `php artisan test --filter=CartPricingTest` or `phpunit tests/Feature/CartPricingTest.php`

## Architecture
- **No API SPA** — session-based auth, Blade + Alpine.js frontend, minimal API routes (`routes/api.php` has only login + tickets stub)
- **Cart**: stored in session (guest + auth), keyed `"menuItemId||price"`
- **Owner dashboard** under `/owner/*` prefix, separate controller namespace `Owner\`
- **Performance** (`config/performance.php`): custom cache TTLs (home, browse, search, restaurants) and polling intervals for real-time order status
- **Real-time**: `OrderUpdated` event broadcast on `order.{orderId}` and `restaurant.{id}.orders` channels
- **Push notifications**: `SendPushNotification` job, VAPID keys via `php artisan generate:vapid-keys`

## Conventions
- **No shadows on buttons** (`.cursorrules`): never use `shadow-sm`, `shadow-md`, etc. on buttons
- **Dark mode**: hybrid CSS variables + Tailwind `dark:` class, controlled via Alpine store (`$store.darkMode`)
- **EditorConfig**: 4-space indent (PHP/JS), 2-space (YAML), LF line endings
- **Models**: `app/Models/` — Restaurant, MenuItem, MenuCategory, Order, OrderItem, User, Promotion, Rating, UserLocation, PushSubscription, etc.
- **Code style**: Laravel Pint (`laravel/pint` in require-dev)
