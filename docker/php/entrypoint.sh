#!/bin/sh
set -e

echo "[entrypoint] starting... APP_ENV=${APP_ENV}"

# Permissions Symfony
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

mkdir -p /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 775 /var/www/html/public/uploads

# Safety
if [ ! -f /var/www/html/bin/console ]; then
  echo "[entrypoint] ERROR: bin/console not found"
  exit 1
fi

# Infra only
if [ "${APP_ENV}" = "dev" ] || [ "${APP_ENV}" = "test" ]; then
  echo "[entrypoint] waiting for MySQL..."

  i=0
  until php -r '
    $url = getenv("DATABASE_URL");
    $p = parse_url($url);
    $dsn = sprintf(
      "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
      $p["host"] ?? "mysql",
      $p["port"] ?? 3306,
      ltrim($p["path"] ?? "/gsm", "/")
    );
    try {
      new PDO($dsn, $p["user"] ?? "root", $p["pass"] ?? "");
      exit(0);
    } catch (Throwable $e) {
      exit(1);
    }
  ' >/dev/null 2>&1
  do
    i=$((i+1))
    [ "$i" -ge 60 ] && echo "[entrypoint] MySQL timeout" && exit 1
    sleep 1
  done

  echo "[entrypoint] mysql ready"

  # DB infra
  php bin/console doctrine:database:create --if-not-exists -n
  php bin/console doctrine:migrations:migrate -n --allow-no-migration
fi

echo "[entrypoint] done"
exec "$@"
