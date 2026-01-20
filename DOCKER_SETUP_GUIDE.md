# Docker Setup Guide for Laravel

## 📋 Prerequisites

- Docker & Docker Compose installed
- Laravel project ready
- `.env` file configured

## 🚀 Quick Start

### 1. Initialize Environment

```bash
# Copy environment file
cp .env.example .env

# Generate app key (if not already done)
docker compose exec app php artisan key:generate

# Create database
docker compose exec app php artisan migrate

# Create symbolic link for storage
docker compose exec app php artisan storage:link
```

### 2. Start Services

```bash
# Build and start all services
docker compose up -d

# View logs
docker compose logs -f app

# Check service status
docker compose ps
```

### 3. Verify Setup

```bash
# Run health check
docker compose exec app /var/www/html/docker/laravel-healthcheck.sh

# Check app is running
curl http://localhost:80/health
```

## 📁 Project Structure

```
project/
├── docker/
│   ├── nginx.conf
│   ├── supervisord.conf
│   ├── php.ini
│   ├── scheduler.sh
│   ├── laravel-healthcheck.sh
│   ├── mysql/
│   │   └── my.cnf
│   └── redis/
│       └── redis.conf
├── Dockerfile
├── docker-compose.yml
├── .env
├── .dockerignore
└── ...
```

## 🔧 Common Commands

### Database Operations

```bash
# Run migrations
docker compose exec app php artisan migrate

# Seed database
docker compose exec app php artisan db:seed

# Fresh migration
docker compose exec app php artisan migrate:fresh

# Check migration status
docker compose exec app php artisan migrate:status
```

### Queue Operations

```bash
# Process queue jobs
docker compose exec queue php artisan queue:work

# Monitor queue
docker compose exec app php artisan queue:monitor

# Failed jobs
docker compose exec app php artisan queue:failed

# Retry failed jobs
docker compose exec app php artisan queue:retry all

# Clear all jobs
docker compose exec app php artisan queue:flush
```

### Cache & Configuration

```bash
# Clear all caches
docker compose exec app php artisan cache:clear

# Clear config cache
docker compose exec app php artisan config:clear

# Clear route cache
docker compose exec app php artisan route:clear

# Clear view cache
docker compose exec app php artisan view:clear

# Optimize for production
docker compose exec app php artisan optimize
```

### Logs

```bash
# View app logs
docker compose exec app tail -f storage/logs/laravel.log

# View health check logs
docker compose exec app tail -f storage/logs/healthcheck.log

# View scheduler logs
docker compose logs -f scheduler

# View queue logs
docker compose logs -f queue

# View MySQL logs
docker compose logs -f mysql
```

### Database Access

```bash
# MySQL CLI
docker compose exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}

# Redis CLI
docker compose exec redis redis-cli

# Monitor Redis
docker compose exec redis redis-cli MONITOR
```

## 🔐 Security Best Practices

### Environment Variables

- Never commit `.env` to version control
- Use strong passwords for DB and Redis
- Rotate secrets regularly
- Use `.env.production` for production

### Network Security

```yaml
# Only expose essential ports
ports:
    - "127.0.0.1:${APP_PORT}:80" # Bind to localhost only
```

### Database Backups

```bash
# Backup database
docker compose exec mysql mysqldump -u${DB_USERNAME} -p${DB_PASSWORD} \
  ${DB_DATABASE} > backup.sql

# Restore database
docker compose exec -T mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} \
  ${DB_DATABASE} < backup.sql
```

## 📊 Monitoring

### Health Checks

All services have health checks. Monitor with:

```bash
docker compose ps  # Check service health
```

### Performance Monitoring

```bash
# CPU and memory usage
docker stats

# Service logs
docker compose logs --tail=100 app

# Slow query log
docker compose exec mysql tail -f /var/log/mysql/slow-query.log
```

## 🔄 Scaling

### Multiple Queue Workers

Update `docker-compose.yml`:

```yaml
queue:
    deploy:
        replicas: 3 # Run 3 queue workers
```

Then restart:

```bash
docker compose up -d --scale queue=3
```

## 🧹 Cleanup

### Stop Services

```bash
# Stop all services
docker compose down

# Stop and remove volumes
docker compose down -v

# Remove unused images
docker image prune -a
```

### Restart

```bash
# Rebuild and restart
docker compose up -d --build

# Restart specific service
docker compose restart app
```

## 🐛 Troubleshooting

### Services Won't Start

```bash
# Check logs
docker compose logs app
docker compose logs mysql

# Verify health
docker compose ps

# Inspect container
docker compose exec app sh
```

### Database Connection Error

```bash
# Verify MySQL is healthy
docker compose logs mysql

# Check credentials in .env
docker compose exec app php artisan db:monitor

# Test connection
docker compose exec mysql mysql -u${DB_USERNAME} -p${DB_PASSWORD} -h mysql
```

### Permission Issues

```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache

# Verify ownership
docker compose exec app ls -la storage/
```

### Out of Memory

```bash
# Increase resource limits in docker-compose.yml
# Restart services
docker compose down
docker compose up -d
```

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Docker Documentation](https://docs.docker.com)
- [MySQL Documentation](https://dev.mysql.com/doc)
- [Redis Documentation](https://redis.io/documentation)

---

**Last Updated:** 2026-01-20
**Version:** 1.0.0
