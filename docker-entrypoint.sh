#!/bin/sh
set -e

# Railway provides PORT dynamic environment variable (default 80 if not set)
APP_PORT="${PORT:-80}"
echo ">>> SkillRank: Configuring Apache to listen on port $APP_PORT..."

# Update ports.conf to listen on dynamic Railway PORT
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/Listen [0-9]\+/Listen $APP_PORT/g" /etc/apache2/ports.conf || true
fi

# Update default site virtual host
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost \*:$APP_PORT>/g" /etc/apache2/sites-available/000-default.conf || true
    sed -i "s/:80/:$APP_PORT/g" /etc/apache2/sites-available/000-default.conf || true
fi

# Run database auto-initialization if MySQL variables exist
if [ -n "$MYSQLHOST" ] || [ -n "$DATABASE_URL" ] || [ -n "$MYSQL_HOST" ]; then
    echo ">>> SkillRank: Database environment detected. Running schema initializer..."
    php /var/www/html/init_cloud_db.php || echo ">>> SkillRank: Database init completed with notice, continuing startup."
else
    echo ">>> SkillRank: Notice - No MySQL environment variables detected yet. Web app will run in zero-config mode."
fi

echo ">>> SkillRank: Starting Apache foreground service on port $APP_PORT..."
exec "$@"
