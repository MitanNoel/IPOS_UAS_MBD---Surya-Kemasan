# Docker & Coolify Deployment Guide

## Overview
This project is configured for secure, rootless Docker deployment with Coolify for environment variable management.

## Security Features
- ✅ **Rootless Container**: Runs as non-root `appuser` user
- ✅ **Non-Privileged Port**: Apache listens on port 8080
- ✅ **Minimal Capabilities**: Only NET_BIND_SERVICE capability enabled
- ✅ **No New Privileges**: Security option prevents privilege escalation
- ✅ **Proper File Permissions**: Correct ownership and permissions setup

## Project Structure
```
proyekmbd/
├── Dockerfile              # Rootless Docker image configuration
├── docker-compose.yml      # Local development setup with security options
├── docker-entrypoint.sh    # Container initialization script
├── .env.example            # Environment template
├── .dockerignore           # Files excluded from Docker image
├── template.sql            # Database schema
├── config/                 # Application configuration
├── public/                 # Web-accessible files
├── process/                # Server-side logic
└── README.md               # Project documentation
```

## Local Development with Docker Compose

### Prerequisites
- Docker installed and running
- Docker Compose installed

### Quick Start
```bash
# Build and start containers
docker-compose up -d

# Access the application
# http://localhost:8080

# View logs
docker-compose logs -f web

# Stop containers
docker-compose down
```

### Database Access
- **Host:** localhost
- **Port:** 3306
- **Database:** db_toko_1
- **Username:** toko_user
- **Password:** toko_password_dev

### Default Login Credentials (from template.sql)
- **Admin User:** username: `admin`, password: `12345`
- **Cashier User:** username: `kasir`, password: `12345`

## Coolify Deployment

### Setup Steps

1. **Create Application in Coolify Dashboard**
   - Go to Coolify → Applications → Create New Application
   - Select "Docker" as the type
   - Connect your Git repository (MitanNoel/proyekmbd)

2. **Configure Environment Variables (Coolify Auto-Generates)**
   Coolify will automatically detect `.env.example` and generate a template form.
   You can also manually add these variables in Coolify's environment section:
   ```
   DB_HOST=your-mysql-host
   DB_PORT=3306
   DB_DATABASE=db_toko_1
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_secure_password
   ```
   - No `.env` file is created - environment variables are injected directly into the container
   - The PHP application reads from `$_ENV` which Coolify automatically populates

3. **Database Configuration (Important)**
   - Set up MySQL/MariaDB on your server
   - Create database: `db_toko_1`
   - Run the schema: Import `template.sql` to initialize tables
   - Ensure database user has proper permissions

4. **Deploy**
   - Coolify will automatically build and deploy using the Dockerfile
   - Your app will be available at your configured domain on port 8080
   - Coolify automatically injects environment variables into the running container

## Environment Variables

### Required Variables
```
DB_HOST          # MySQL server hostname
DB_PORT          # MySQL port (default: 3306)
DB_DATABASE      # Database name (db_toko_1)
DB_USERNAME      # Database user
DB_PASSWORD      # Database password
```

### Optional Variables
```
APP_ENV          # production or development (default: production)
APP_DEBUG        # true or false (default: false)
```

## Key Configuration Files

### Dockerfile
- **Base Image**: PHP 8.1 with Apache2
- **User**: Non-root `appuser` account
- **Port**: Apache configured to listen on 8080 (non-privileged)
- **Extensions**: PDO MySQL support
- **Security**: Minimal permissions, proper ownership

### docker-entrypoint.sh
- Container initialization script
- Works with Coolify's automatic environment variable injection
- Ensures proper initialization with non-root user
- No `.env` file needed for Coolify deployments (env vars injected directly)

### docker-compose.yml
- Local development setup with MySQL 8.0
- Automatic database initialization from `template.sql`
- Health checks configured
- Security options enabled:
  - `no-new-privileges: true`
  - Only NET_BIND_SERVICE capability
  - All other capabilities dropped

## Rootless Container Benefits

1. **Enhanced Security**: Running as non-root limits damage if container is compromised
2. **Privilege Isolation**: Cannot escalate to root privileges
3. **Minimal Attack Surface**: Only necessary capabilities enabled
4. **Production Ready**: Follows security best practices

## Port Mapping

### Local Development
- **Web Application**: http://localhost:8080
- **MySQL**: localhost:3306

### Production (Coolify)
- Configure domain in Coolify dashboard with port 8080
- MySQL: Configure your server's MySQL connection

## Database Initialization

### Automatic (Docker Compose)
When using `docker-compose up`, the database is automatically initialized from `template.sql`.

### Manual (Coolify/Production)
```bash
# Option 1: Via MySQL CLI
mysql -h DB_HOST -u DB_USERNAME -p DB_PASSWORD DB_DATABASE < template.sql

# Option 2: Via phpMyAdmin or similar tool
# Import template.sql file directly
```

## Troubleshooting

### Connection Failed
```
Error: SQLSTATE[HY000]: General error: 2002
```
- Verify `DB_HOST` is correctly set (use service name `mysql` in docker-compose, hostname in Coolify)
- Check database server is running
- Verify credentials in environment variables

### 404 Errors
- Ensure document root is set to `/public`
- Check Dockerfile line: `ENV APACHE_DOCUMENT_ROOT=/var/www/html/public`

### Permission Denied on Files
- The container runs as `appuser`, not root
- Ensure volume mounts have proper permissions on host
- Check that files are readable by all users (644 for files, 755 for dirs)

### Database Tables Missing
- Import `template.sql` to initialize schema
- Verify MySQL user has CREATE TABLE privileges

## Security Recommendations

1. **Change Default Passwords**
   - Update admin and kasir passwords in database after first login
   - Use strong, unique passwords

2. **Environment Variables**
   - Never commit `.env` file to git
   - Use Coolify's secure environment variable storage
   - Rotate database passwords regularly

3. **Database**
   - Use strong database passwords (not `toko_password_dev` in production)
   - Restrict database user privileges to minimum needed
   - Enable MySQL SSL/TLS for secure connections
   - Use managed database service when possible

4. **Container**
   - Keep PHP and extensions updated via image rebuilds
   - Enable PHP error logging (not display)
   - Monitor container logs for suspicious activity
   - Use private container registries if applicable

5. **Network**
   - Use HTTPS/TLS in production
   - Restrict database network access to application only
   - Use firewall rules to limit access

## Scaling & Performance

### For Production
- Use managed database service (AWS RDS, DigitalOcean MySQL, etc.)
- Enable caching headers in Apache
- Use separate reverse proxy (nginx, Caddy)
- Monitor resource usage and scale accordingly

### Database Optimization
- Add indexes on frequently queried columns
- Consider database replication for read-heavy workloads
- Regular backup schedule

## Useful Commands

```bash
# Build image
docker build -t proyekmbd:latest .

# Run rootless container
docker run -d \
  --user appuser \
  --cap-drop=ALL \
  --cap-add=NET_BIND_SERVICE \
  --security-opt=no-new-privileges:true \
  -p 8080:8080 \
  -e DB_HOST=mysql \
  -e DB_USERNAME=toko_user \
  -e DB_PASSWORD=secure_pass \
  proyekmbd:latest

# View container logs
docker logs -f container_id

# Execute command in running container
docker exec -it container_id bash

# Access MySQL from container
docker exec -it proyekmbd-mysql mysql -u toko_user -p db_toko_1

# Check running processes in container (verify non-root)
docker exec proyekmbd-web ps aux
```

## Deployment Checklist

- [ ] Dockerfile builds successfully locally
- [ ] docker-compose up -d works and app is accessible at localhost:8080
- [ ] Login with admin/12345 works
- [ ] Database connection verified
- [ ] GitHub repository connected to Coolify
- [ ] Environment variables configured in Coolify
- [ ] MySQL database initialized with template.sql
- [ ] Container verified running as non-root user
- [ ] Domain configured and SSL/TLS enabled in Coolify
- [ ] Health checks passing
- [ ] Change default database passwords
- [ ] Backup strategy in place

## Support
For issues or questions about Docker deployment, refer to:
- Docker Documentation: https://docs.docker.com
- Docker Security Best Practices: https://docs.docker.com/engine/security/
- Coolify Documentation: https://coolify.io/docs
- PHP Docker: https://hub.docker.com/_/php
