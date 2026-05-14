#!/bin/bash
set -e

# Create .env file from environment variables if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file from environment variables..."
    
    cat > /var/www/html/.env << EOF
DB_HOST=${DB_HOST:-localhost}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-db_toko_1}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}
EOF
    
    # Ensure proper permissions for non-root user
    chmod 644 /var/www/html/.env
    
    echo ".env file created successfully"
fi

# Verify that Apache log directory is writable
if [ ! -w /var/log/apache2 ]; then
    echo "Warning: /var/log/apache2 is not writable by current user"
fi

# Execute the main command
exec "$@"
