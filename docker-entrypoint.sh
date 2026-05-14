#!/bin/bash
set -e

# For Coolify: Environment variables are injected directly into the container
# The PHP application reads from $_ENV which is automatically populated
# from the injected environment variables, no .env file needed

# For local development: If .env exists, it will be loaded by the PHP application
# If you need to create one locally, it can be done manually

# Verify that Apache log directory is writable
if [ ! -w /var/log/apache2 ]; then
    echo "Warning: /var/log/apache2 is not writable by current user"
fi

# Execute the main command
exec "$@"
