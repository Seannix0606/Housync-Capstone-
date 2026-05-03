# Railway/Heroku: this file overrides nixpacks [start] if present — do NOT run optimize:clear here
# (CACHE_STORE=database would hit MySQL before host/URL is valid → connection refused).
web: sh -c 'php scripts/migrate.php && php artisan route:clear && rm -rf public/storage && php artisan storage:link && php -d variables_order=EGPCS -S 0.0.0.0:${PORT:-8080} -t public server.php'
