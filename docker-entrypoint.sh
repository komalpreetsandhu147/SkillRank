#!/bin/sh
set -e

# Configure Apache port dynamically if PORT is provided by Railway
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on PORT: $PORT"
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

# Run auto-migration if MySQL host is provided
if [ -n "$MYSQLHOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running SkillRank database initialization..."
    php /var/www/html/init_cloud_db.php || true
fi

exec "$@"
