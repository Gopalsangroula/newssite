# Running Libang on a small server

Readers get stored copies of the public pages, answered before Laravel starts, so the server only builds a page a few times a minute however many people are reading. Admin pages work as normal. These steps keep it that way.

`php artisan site:check` shows which steps are still to do on a server.

## 1. Settings in `.env`

Change these on the live server. Everything else stays as it is.

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://libang.org.np
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=7
CACHE_STORE=file
```

`APP_DEBUG=false` matters most. With it on, an error page shows file paths, database queries and other internal details to whoever triggers it.

## 2. Uploading the files

Do not upload `.env`, `node_modules`, `storage/` or `public/storage` over the live site. The live `storage/app/public` holds the real uploads.

## 3. Commands, in this order

First time only:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan migrate --path=database/migrations/cleanup --force
php artisan storage:link
php artisan images:thumbs
php artisan optimize
php artisan site:refresh
php artisan site:check
```

- The `cleanup` line permanently removes the genealogy and visitor tables. It saves the genealogy records to `storage/backups` first.
- `images:thumbs` makes the smaller picture copies. It takes a few minutes and does not touch the originals. If the server has no PHP GD, the site keeps using the originals.
- `--no-dev` matters: it leaves development tools off the live server.

After every later update:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan site:refresh
```

If you change `.env`, run `php artisan optimize` again. Otherwise the old values stay in use.

## 4. Cron job

The site runs its own tasks about once a minute while it has visitors, so this is optional. Add it for scheduled posts to go out on time during quiet hours.

```bash
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

## 5. Cloudflare

These give the biggest gain, because most readers then never reach the server. All of it works on the free plan.

**Cache the pages.** Caching > Cache Rules > Create rule.

Expression:

```
(http.host eq "libang.org.np")
and not starts_with(http.request.uri.path, "/admin")
and not starts_with(http.request.uri.path, "/dashboard")
and not starts_with(http.request.uri.path, "/login")
and not starts_with(http.request.uri.path, "/settings")
and not starts_with(http.request.uri.path, "/livewire")
and not starts_with(http.request.uri.path, "/search")
and not http.cookie contains "-session"
```

Then set: Cache eligibility: Eligible for cache. Edge TTL: Use cache-control header if present. Browser TTL: Respect origin.

The site tells Cloudflare to keep a page for 60 seconds, and to keep showing its last copy if this server goes down. So a saved post can take up to two minutes to reach readers, and a story taken down can stay visible for up to two minutes. To remove one at once, use Caching > Purge Cache.

**Also turn on:**

- SSL/TLS: Full (strict) if the server has a certificate, and Always Use HTTPS
- Security: Bot Fight Mode
- Security > WAF: a rate limiting rule on `/login`
- Caching: Always Online

**During an attack:** turn on Under Attack Mode.

**If you control the server's firewall:** allow ports 80 and 443 only from [Cloudflare's addresses](https://www.cloudflare.com/ips/). Otherwise someone who finds the server's real address can skip everything above.

## 6. PHP settings

OPcache is the most important one. It makes a built page several times faster.

```
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=20000
opcache.revalidate_freq=60
expose_php=Off
memory_limit=256M
```

Each PHP worker building pages uses about 30 MB. A stored page uses about 2 MB. On 2 GB of RAM, 15 to 20 workers is plenty.

## 7. Database settings

For a VPS with 2 GB of RAM:

```
innodb_buffer_pool_size=512M
max_connections=50
```

On 1 GB, use 256M. Add a 1 GB swap file if the server has none, so that a memory spike slows the site down instead of stopping MySQL.

## 8. When something looks wrong

| What you see | What to do |
| --- | --- |
| A page shows old content | `php artisan site:refresh` |
| You need to check that the site is healthy | Open `/up`. Point an uptime monitor at it: it fails if the database is down |
| The database is down | Stored pages keep showing, and pages never stored show a friendly error |
| You want to see errors | `storage/logs/`, one file per day, kept for 7 days |
| Pictures look large after an update | `php artisan images:thumbs` |
