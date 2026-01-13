#!/bin/sh
set -e

echo "[entrypoint] starting... APP_ENV=${APP_ENV}"

# permissions symfony
mkdir -p /var/www/html/var/cache /var/www/html/var/log
chown -R www-data:www-data /var/www/html/var
chmod -R 775 /var/www/html/var

mkdir -p /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 775 /var/www/html/public/uploads

# Safety: ensure console exists
if [ ! -f /var/www/html/bin/console ]; then
  echo "[entrypoint] ERROR: bin/console not found (volume mount?)"
  ls -la /var/www/html || true
  exit 1
fi

# uniquement en dev/test
if [ "${APP_ENV}" = "dev" ] || [ "${APP_ENV}" = "test" ]; then
  echo "[entrypoint] waiting for MySQL..."

  # wait up to 90s (90 tries)
  i=0
  until php -r '
    $url = getenv("DATABASE_URL") ?: "";
    $p = parse_url($url);
    $host = $p["host"] ?? "mysql";
    $port = $p["port"] ?? 3306;
    $user = $p["user"] ?? "root";
    $pass = $p["pass"] ?? "";
    $db   = ltrim($p["path"] ?? "/gsm", "/");

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    try {
      $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
      ]);
      $pdo->query("SELECT 1");
      exit(0);
    } catch (Throwable $e) {
      exit(1);
    }
  ' >/dev/null 2>&1
  do
    i=$((i+1))
    if [ "$i" -ge 90 ]; then
      echo "[entrypoint] ERROR: MySQL not ready after 90s"
      echo "[entrypoint] DATABASE_URL=${DATABASE_URL}"
      exit 1
    fi
    sleep 1
  done

  echo "[entrypoint] mysql ready"

  echo "[entrypoint] running migrations..."
  php bin/console doctrine:database:create --if-not-exists --no-interaction
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

  # Seed only if user table exists AND empty (Solution 1: existence check)
  echo "[entrypoint] checking if user exists..."
  USER_EXISTS=$(php bin/console dbal:run-sql "SELECT 1 FROM user LIMIT 1" --no-interaction 2>/dev/null | grep -q 1 && echo "yes" || echo "no")

  echo "[entrypoint] user exists: ${USER_EXISTS}"

  if [ "${USER_EXISTS}" = "no" ]; then
    echo "[entrypoint] running app:user:seed..."
    php bin/console app:user:seed --no-interaction
  else
    echo "[entrypoint] seed skipped (users already exist)"
  fi
fi

echo "[entrypoint] done, launching: $*"
exec "$@"
