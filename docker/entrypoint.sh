#!/bin/bash
set -e

cd /var/www/html

# ── Generate APP_KEY jika belum ada ──────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "⚠️  APP_KEY tidak ditemukan di environment. Generate dulu via Easypanel."
fi

# ── Optimisasi Laravel ────────────────────────────────────────────────────────
echo "🔧 Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── Storage link ─────────────────────────────────────────────────────────────
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ── Jalankan migrasi (opsional – aktifkan jika database sudah siap) ──────────
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "🗄️  Running migrations..."
    php artisan migrate --force
fi

# ── Mulai Supervisor (Nginx + PHP-FPM) ───────────────────────────────────────
echo "🚀 Starting services..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
