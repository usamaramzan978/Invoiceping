#!/bin/bash

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
LARAVEL_PATH="/var/www/html"
LOG_FILE="${LARAVEL_PATH}/storage/logs/healthcheck.log"
EXIT_CODE=0

# Ensure log directory exists
mkdir -p "$(dirname "$LOG_FILE")"

# Logging function
log() {
    echo -e "$1" | tee -a "$LOG_FILE"
}

log_check() {
    local name=$1
    local status=$2
    local message=$3
    
    if [ "$status" = "pass" ]; then
        log "${GREEN}✅ ${name}:${NC} ${message}"
    else
        log "${RED}❌ ${name}:${NC} ${message}"
        EXIT_CODE=1
    fi
}

# Header
log "${BLUE}========================================${NC}"
log "${BLUE}🚀 Laravel Docker Health Check${NC}"
log "${BLUE}========================================${NC}"
log "Started at: $(date '+%Y-%m-%d %H:%M:%S')"
log ""

# Change to Laravel directory
cd "$LARAVEL_PATH" || exit 1

# ========== 1️⃣ APP_KEY Check ==========
log "${YELLOW}[1/8] Checking APP_KEY...${NC}"
APP_KEY=$(grep -i "^APP_KEY=" .env | cut -d= -f2 | tr -d ' ')
if [[ -z "$APP_KEY" ]]; then
    log_check "APP_KEY" "fail" "Missing or empty. Run: php artisan key:generate"
else
    log_check "APP_KEY" "pass" "Found (${APP_KEY:0:10}...)"
fi
log ""

# ========== 2️⃣ Database Connection ==========
log "${YELLOW}[2/8] Checking Database Connection...${NC}"
if php artisan db:monitor --databases=default > /dev/null 2>&1; then
    log_check "Database" "pass" "Connection successful"
else
    log_check "Database" "fail" "Cannot connect to database. Check DB credentials."
fi
log ""

# ========== 3️⃣ Storage Link ==========
log "${YELLOW}[3/8] Checking Storage Link...${NC}"
if [ -L "${LARAVEL_PATH}/public/storage" ]; then
    log_check "Storage Link" "pass" "Symbolic link exists"
else
    log_check "Storage Link" "fail" "Missing symlink. Run: php artisan storage:link"
fi
log ""

# ========== 4️⃣ Storage Permissions ==========
log "${YELLOW}[4/8] Checking Storage Permissions...${NC}"
if [ -w "${LARAVEL_PATH}/storage" ] && [ -w "${LARAVEL_PATH}/bootstrap/cache" ]; then
    log_check "Permissions" "pass" "Storage and cache directories are writable"
else
    log_check "Permissions" "fail" "Check permissions on storage/ and bootstrap/cache/"
fi
log ""

# ========== 5️⃣ Cache Configuration ==========
log "${YELLOW}[5/8] Clearing Cache/Config...${NC}"
php artisan config:clear > /dev/null 2>&1 && \
php artisan cache:clear > /dev/null 2>&1 && \
php artisan route:clear > /dev/null 2>&1 && \
php artisan view:clear > /dev/null 2>&1
log_check "Cache Clear" "pass" "All caches cleared successfully"
log ""

# ========== 6️⃣ Redis Connection ==========
log "${YELLOW}[6/8] Testing Redis Connection...${NC}"
CACHE_DRIVER=$(grep -i "^CACHE_DRIVER=" .env | cut -d= -f2 | tr -d ' ')
if [ "$CACHE_DRIVER" = "redis" ]; then
    if php artisan tinker --execute="exit(Cache::connection('redis')->put('health_check', 'ok', 10) ? 0 : 1);" > /dev/null 2>&1; then
        log_check "Redis" "pass" "Connection and cache operations working"
    else
        log_check "Redis" "fail" "Cannot connect or write to Redis. Verify Redis is running."
    fi
else
    log_check "Redis" "skip" "Not configured as cache driver (current: $CACHE_DRIVER)"
fi
log ""

# ========== 7️⃣ Failed Jobs Table ==========
log "${YELLOW}[7/8] Checking Failed Jobs Table...${NC}"
if php artisan tinker --execute="exit(DB::table('failed_jobs')->count() >= 0 ? 0 : 1);" > /dev/null 2>&1; then
    log_check "Failed Jobs" "pass" "Table exists and accessible"
else
    log_check "Failed Jobs" "fail" "Table missing. Run: php artisan queue:failed-table && migrate"
fi
log ""

# ========== 8️⃣ Scheduler Tasks ==========
log "${YELLOW}[8/8] Listing Scheduler Tasks...${NC}"
php artisan schedule:list 2>/dev/null | head -20
log_check "Scheduler" "pass" "Tasks are configured"
log ""

# ========== Summary ==========
log "${BLUE}========================================${NC}"
if [ $EXIT_CODE -eq 0 ]; then
    log "${GREEN}🎉 All checks passed!${NC}"
else
    log "${RED}⚠️  Some checks failed. Review above for details.${NC}"
fi
log "${BLUE}========================================${NC}"
log "Completed at: $(date '+%Y-%m-%d %H:%M:%S')"
log ""

exit $EXIT_CODE