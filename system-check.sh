#!/bin/bash

################################################################################
# TRADEXPRO SYSTEM CHECK SCRIPT
# Purpose: Comprehensive environment validation and diagnostics
# Usage: sudo bash system-check.sh
# Output: Color-coded console + /var/log/tradexpro-check.log
################################################################################

set -euo pipefail

# Color Codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Log file
LOG_FILE="/var/log/tradexpro-check.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
REPORT_FILE="/var/log/tradexpro-check-report.txt"

# Counters
PASS_COUNT=0
WARN_COUNT=0
FAIL_COUNT=0

################################################################################
# UTILITY FUNCTIONS
################################################################################

# Initialize log file
init_log() {
    if [ ! -d "$(dirname "$LOG_FILE")" ]; then
        mkdir -p "$(dirname "$LOG_FILE")"
    fi
    echo "================================" > "$LOG_FILE"
    echo "TRADEXPRO SYSTEM CHECK" >> "$LOG_FILE"
    echo "Started: $TIMESTAMP" >> "$LOG_FILE"
    echo "================================" >> "$LOG_FILE"
}

# Logging function
log() {
    echo "$@" >> "$LOG_FILE"
}

# Print with color
print_status() {
    local status=$1
    local message=$2
    local detail=${3:-""}
    
    case "$status" in
        PASS)
            echo -e "${GREEN}✅ PASS${NC} | $message" | tee -a "$LOG_FILE"
            ((PASS_COUNT++))
            ;;
        FAIL)
            echo -e "${RED}❌ FAIL${NC} | $message" | tee -a "$LOG_FILE"
            ((FAIL_COUNT++))
            ;;
        WARN)
            echo -e "${YELLOW}⚠️  WARN${NC} | $message" | tee -a "$LOG_FILE"
            ((WARN_COUNT++))
            ;;
        INFO)
            echo -e "${BLUE}ℹ️  INFO${NC} | $message" | tee -a "$LOG_FILE"
            ;;
        DETAIL)
            echo -e "${CYAN}    └─${NC} $message" | tee -a "$LOG_FILE"
            ;;
    esac
    
    if [ -n "$detail" ]; then
        log "     Detail: $detail"
    fi
}

# Section header
print_section() {
    echo "" | tee -a "$LOG_FILE"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}" | tee -a "$LOG_FILE"
    echo -e "${CYAN}$1${NC}" | tee -a "$LOG_FILE"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}" | tee -a "$LOG_FILE"
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Get version of command
get_version() {
    local cmd=$1
    local flag=${2:-"--version"}
    
    if command_exists "$cmd"; then
        $cmd $flag 2>&1 | head -n1
    else
        echo "Not installed"
    fi
}

# Compare versions
version_ge() {
    [ "$(printf '%s\n' "$1" "$2" | sort -V | head -n1)" = "$2" ]
}

################################################################################
# 1. SYSTEM REQUIREMENTS
################################################################################

check_system_requirements() {
    print_section "1. SYSTEM REQUIREMENTS"
    
    # OS Check
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        print_status "PASS" "OS: $PRETTY_NAME ($VERSION_ID)"
        log "OS_NAME: $PRETTY_NAME, OS_VERSION: $VERSION_ID"
    else
        print_status "FAIL" "Cannot determine OS"
    fi
    
    # CPU Check
    local cpu_cores=$(nproc)
    if [ "$cpu_cores" -ge 2 ]; then
        print_status "PASS" "CPU Cores: $cpu_cores"
        log "CPU_CORES: $cpu_cores"
    else
        print_status "WARN" "CPU Cores: $cpu_cores (Recommended: ≥2)"
    fi
    
    # RAM Check
    local ram_total=$(free -m | awk 'NR==2{print $2}')
    local ram_available=$(free -m | awk 'NR==2{print $7}')
    
    if [ "$ram_total" -ge 2048 ]; then
        print_status "PASS" "RAM: ${ram_total}MB (Available: ${ram_available}MB)"
        log "RAM_TOTAL: ${ram_total}MB, RAM_AVAILABLE: ${ram_available}MB"
    else
        print_status "WARN" "RAM: ${ram_total}MB (Recommended: ≥2GB)"
    fi
    
    # Disk Space Check
    local disk_available=$(df /var/www | awk 'NR==2{print $4}')
    local disk_total=$(df /var/www | awk 'NR==2{print $2}')
    
    if [ "$disk_available" -ge 5242880 ]; then # 5GB in KB
        print_status "PASS" "Disk Space: ${disk_total}KB total (Available: ${disk_available}KB)"
        log "DISK_TOTAL: ${disk_total}KB, DISK_AVAILABLE: ${disk_available}KB"
    else
        print_status "WARN" "Disk Space: Only ${disk_available}KB available (Recommended: ≥5GB)"
    fi
    
    # Uptime
    local uptime=$(uptime -p)
    print_status "INFO" "System Uptime: $uptime"
}

################################################################################
# 2. BASIC TOOLS
################################################################################

check_basic_tools() {
    print_section "2. VERIFY BASIC TOOLS"
    
    local tools=("git" "curl" "wget" "unzip" "tar" "grep" "sed" "awk")
    
    for tool in "${tools[@]}"; do
        if command_exists "$tool"; then
            local version=$(get_version "$tool")
            print_status "PASS" "$tool: $version"
            log "${tool}_VERSION: $version"
        else
            print_status "FAIL" "$tool: Not installed"
            log "${tool}_STATUS: NOT_INSTALLED"
        fi
    done
    
    # Check if user is root
    if [ "$EUID" -eq 0 ]; then
        print_status "PASS" "Running as root (required for some checks)"
        log "ROOT_ACCESS: true"
    else
        print_status "WARN" "Not running as root (some checks may fail)"
        log "ROOT_ACCESS: false"
    fi
}

################################################################################
# 3. PHP & LARAVEL
################################################################################

check_php_laravel() {
    print_section "3. PHP & LARAVEL ENVIRONMENT"
    
    # PHP Check
    if command_exists "php"; then
        local php_version=$(php -v | head -n1 | awk '{print $2}')
        if version_ge "$php_version" "7.4"; then
            print_status "PASS" "PHP Version: $php_version"
            log "PHP_VERSION: $php_version"
        else
            print_status "FAIL" "PHP Version: $php_version (Minimum: 7.4)"
        fi
    else
        print_status "FAIL" "PHP: Not installed"
        log "PHP_STATUS: NOT_INSTALLED"
    fi
    
    # PHP Extensions Check
    local required_extensions=("pdo" "mysql" "mbstring" "tokenizer" "json" "curl" "openssl" "bcmath" "gd")
    for ext in "${required_extensions[@]}"; do
        if php -m | grep -qi "$ext"; then
            print_status "PASS" "PHP Extension: $ext"
            log "PHP_EXT_${ext^^}: installed"
        else
            print_status "WARN" "PHP Extension: $ext (Not loaded)"
            log "PHP_EXT_${ext^^}: not_installed"
        fi
    done
    
    # Composer Check
    if command_exists "composer"; then
        local composer_version=$(composer --version | awk '{print $3}')
        print_status "PASS" "Composer: $composer_version"
        log "COMPOSER_VERSION: $composer_version"
    else
        print_status "FAIL" "Composer: Not installed"
        log "COMPOSER_STATUS: NOT_INSTALLED"
    fi
    
    # MySQL/MariaDB Check
    if command_exists "mysql"; then
        local db_version=$(mysql --version)
        print_status "PASS" "MySQL Client: $db_version"
        log "MYSQL_CLIENT: $db_version"
    else
        print_status "WARN" "MySQL Client: Not installed (check if DB server is accessible)"
    fi
    
    # Laravel Check in AdminPortal
    if [ -f "/var/www/Tradexpro-AdminPortal/artisan" ]; then
        print_status "PASS" "Laravel Artisan: Found in AdminPortal"
        log "LARAVEL_ARTISAN: found"
    else
        print_status "WARN" "Laravel Artisan: Not found in AdminPortal"
        log "LARAVEL_ARTISAN: not_found"
    fi
}

################################################################################
# 4. NODE.JS & NPM
################################################################################

check_nodejs_npm() {
    print_section "4. NODE.JS & NPM ENVIRONMENT"
    
    # Node.js Check
    if command_exists "node"; then
        local node_version=$(node --version)
        print_status "PASS" "Node.js: $node_version"
        log "NODE_VERSION: $node_version"
    else
        print_status "FAIL" "Node.js: Not installed"
        log "NODE_STATUS: NOT_INSTALLED"
    fi
    
    # NPM Check
    if command_exists "npm"; then
        local npm_version=$(npm --version)
        print_status "PASS" "NPM: $npm_version"
        log "NPM_VERSION: $npm_version"
    else
        print_status "FAIL" "NPM: Not installed"
        log "NPM_STATUS: NOT_INSTALLED"
    fi
    
    # Yarn Check (Optional)
    if command_exists "yarn"; then
        local yarn_version=$(yarn --version)
        print_status "PASS" "Yarn: $yarn_version (Optional)"
        log "YARN_VERSION: $yarn_version"
    else
        print_status "INFO" "Yarn: Not installed (Optional)"
        log "YARN_STATUS: not_installed"
    fi
    
    # TypeScript Check (Global)
    if command_exists "tsc"; then
        local tsc_version=$(tsc --version)
        print_status "PASS" "TypeScript (Global): $tsc_version"
        log "TYPESCRIPT_GLOBAL: $tsc_version"
    else
        print_status "INFO" "TypeScript (Global): Not installed (Check local installation)"
        log "TYPESCRIPT_GLOBAL: not_installed"
    fi
}

################################################################################
# 5. NETWORKING
################################################################################

check_networking() {
    print_section "5. NETWORKING & CONNECTIVITY"
    
    # Internet Check
    if ping -c 1 8.8.8.8 >/dev/null 2>&1; then
        print_status "PASS" "Internet Connectivity: OK"
        log "INTERNET_STATUS: connected"
    else
        print_status "WARN" "Internet Connectivity: No response from 8.8.8.8"
        log "INTERNET_STATUS: failed"
    fi
    
    # Domain Resolution
    local domain="goldvninvest.online"
    if host "$domain" >/dev/null 2>&1; then
        local ip=$(host "$domain" | grep -oP '\d+\.\d+\.\d+\.\d+' | head -1)
        print_status "PASS" "Domain Resolution: $domain → $ip"
        log "DOMAIN_${domain}: $ip"
    else
        print_status "WARN" "Domain Resolution: $domain (Cannot resolve)"
        log "DOMAIN_${domain}: resolution_failed"
    fi
    
    # Port Availability Check
    local ports=(80 443 3000 3306 8080)
    for port in "${ports[@]}"; do
        if netstat -tlnp 2>/dev/null | grep -q ":$port "; then
            print_status "INFO" "Port $port: In use"
            log "PORT_${port}: in_use"
        else
            print_status "PASS" "Port $port: Available"
            log "PORT_${port}: available"
        fi
    done
}

################################################################################
# 6. ENVIRONMENT FILES
################################################################################

check_environment_files() {
    print_section "6. ENVIRONMENT FILES"
    
    local repos=("Tradexpro-AdminPortal" "Tradexpro-UserPortal" "Tradexpro-NodeWallet")
    
    for repo in "${repos[@]}"; do
        local repo_path="/var/www/$repo"
        
        if [ -d "$repo_path" ]; then
            print_status "INFO" "Repository: $repo"
            log "REPO_PATH_${repo}: $repo_path"
            
            # Check .env file
            if [ -f "$repo_path/.env" ]; then
                print_status "PASS" "  ├─ .env file exists"
                log "ENV_FILE_${repo}: exists"
                
                # Check key variables
                if grep -q "APP_KEY=" "$repo_path/.env"; then
                    print_status "PASS" "  ├─ APP_KEY configured"
                else
                    print_status "WARN" "  ├─ APP_KEY not configured"
                fi
            else
                if [ -f "$repo_path/.env.example" ]; then
                    print_status "WARN" "  ├─ .env file missing (Found .env.example)"
                    log "ENV_FILE_${repo}: missing_but_example_exists"
                else
                    print_status "FAIL" "  ├─ .env file missing"
                    log "ENV_FILE_${repo}: missing"
                fi
            fi
            
            # Check .env.example
            if [ -f "$repo_path/.env.example" ]; then
                print_status "PASS" "  └─ .env.example exists"
                log "ENV_EXAMPLE_${repo}: exists"
            else
                print_status "WARN" "  └─ .env.example not found"
                log "ENV_EXAMPLE_${repo}: not_found"
            fi
        else
            print_status "FAIL" "Repository path not found: $repo_path"
            log "REPO_${repo}: path_not_found"
        fi
    done
}

################################################################################
# 7. GIT REPOSITORIES
################################################################################

check_git_repos() {
    print_section "7. GIT REPOSITORIES"
    
    local repos=("Tradexpro-AdminPortal" "Tradexpro-UserPortal" "Tradexpro-NodeWallet")
    
    for repo in "${repos[@]}"; do
        local repo_path="/var/www/$repo"
        
        if [ -d "$repo_path" ]; then
            print_status "INFO" "Checking: $repo"
            log "CHECKING_REPO: $repo"
            
            # Check git status
            if [ -d "$repo_path/.git" ]; then
                print_status "PASS" "  ├─ Git repository initialized"
                log "GIT_INIT_${repo}: true"
                
                # Get remote URL
                local remote_url=$(cd "$repo_path" && git config --get remote.origin.url 2>/dev/null || echo "Not configured")
                print_status "DETAIL" "Remote: $remote_url"
                log "GIT_REMOTE_${repo}: $remote_url"
                
                # Get current branch
                local current_branch=$(cd "$repo_path" && git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "Unknown")
                print_status "DETAIL" "Branch: $current_branch"
                log "GIT_BRANCH_${repo}: $current_branch"
                
                # Get latest commit
                local last_commit=$(cd "$repo_path" && git log -1 --pretty=format:"%h - %an - %s" 2>/dev/null || echo "Unknown")
                print_status "DETAIL" "Last commit: $last_commit"
                log "GIT_LAST_COMMIT_${repo}: $last_commit"
            else
                print_status "WARN" "  └─ Not a git repository"
                log "GIT_INIT_${repo}: false"
            fi
        else
            print_status "FAIL" "Repository not found: $repo_path"
        fi
    done
}

################################################################################
# 8. SSL CERTIFICATES
################################################################################

check_ssl_certificates() {
    print_section "8. SSL CERTIFICATES"
    
    local domain="goldvninvest.online"
    local cert_path="/etc/letsencrypt/live/$domain/fullchain.pem"
    
    if [ -f "$cert_path" ]; then
        print_status "PASS" "SSL Certificate: Found"
        log "SSL_CERT_PATH: $cert_path"
        
        # Get expiration date
        local expiry=$(openssl x509 -in "$cert_path" -noout -enddate | cut -d= -f2)
        print_status "DETAIL" "Expiry: $expiry"
        log "SSL_EXPIRY: $expiry"
        
        # Check if expired
        local expiry_epoch=$(date -d "$expiry" +%s)
        local now_epoch=$(date +%s)
        local days_left=$(( ($expiry_epoch - $now_epoch) / 86400 ))
        
        if [ "$days_left" -gt 30 ]; then
            print_status "PASS" "Certificate valid for $days_left more days"
            log "SSL_DAYS_LEFT: $days_left"
        elif [ "$days_left" -gt 0 ]; then
            print_status "WARN" "Certificate expires in $days_left days"
            log "SSL_DAYS_LEFT: $days_left"
        else
            print_status "FAIL" "Certificate expired $((0 - days_left)) days ago"
            log "SSL_DAYS_LEFT: $days_left"
        fi
    else
        print_status "WARN" "SSL Certificate: Not found at $cert_path"
        log "SSL_CERT_PATH: not_found"
    fi
    
    # Check nginx/apache SSL configuration
    if [ -f "/etc/nginx/sites-enabled/$domain" ] || [ -f "/etc/nginx/sites-enabled/$domain.conf" ]; then
        print_status "PASS" "Nginx SSL Configuration: Found"
        log "NGINX_SSL: configured"
    elif [ -f "/etc/apache2/sites-enabled/$domain.conf" ]; then
        print_status "PASS" "Apache SSL Configuration: Found"
        log "APACHE_SSL: configured"
    else
        print_status "WARN" "Web server SSL configuration: Not found"
        log "WEB_SERVER_SSL: not_found"
    fi
}

################################################################################
# 9. DEPENDENCIES
################################################################################

check_dependencies() {
    print_section "9. DEPENDENCIES & LOCK FILES"
    
    local repos=("Tradexpro-AdminPortal" "Tradexpro-UserPortal" "Tradexpro-NodeWallet")
    
    for repo in "${repos[@]}"; do
        local repo_path="/var/www/$repo"
        
        if [ -d "$repo_path" ]; then
            print_status "INFO" "$repo:"
            log "CHECKING_DEPS: $repo"
            
            # Check composer.lock (PHP projects)
            if [ -f "$repo_path/composer.lock" ]; then
                print_status "PASS" "  ├─ composer.lock found"
                local lock_modified=$(date -r "$repo_path/composer.lock" '+%Y-%m-%d %H:%M:%S')
                print_status "DETAIL" "Modified: $lock_modified"
                log "COMPOSER_LOCK_${repo}: exists ($lock_modified)"
            elif [ -f "$repo_path/composer.json" ]; then
                print_status "WARN" "  ├─ composer.json found but no composer.lock"
                log "COMPOSER_LOCK_${repo}: missing"
            fi
            
            # Check package-lock.json (Node projects)
            if [ -f "$repo_path/package-lock.json" ]; then
                print_status "PASS" "  ├─ package-lock.json found"
                local lock_modified=$(date -r "$repo_path/package-lock.json" '+%Y-%m-%d %H:%M:%S')
                print_status "DETAIL" "Modified: $lock_modified"
                log "PACKAGE_LOCK_${repo}: exists ($lock_modified)"
            fi
            
            # Check package.json (Node projects)
            if [ -f "$repo_path/package.json" ]; then
                print_status "PASS" "  ├─ package.json found"
                log "PACKAGE_JSON_${repo}: exists"
            fi
            
            # Check yarn.lock
            if [ -f "$repo_path/yarn.lock" ]; then
                print_status "INFO" "  ├─ yarn.lock found"
                log "YARN_LOCK_${repo}: exists"
            fi
            
            # Check node_modules
            if [ -d "$repo_path/node_modules" ]; then
                local modules_count=$(find "$repo_path/node_modules" -maxdepth 1 -type d | wc -l)
                print_status "PASS" "  ├─ node_modules found ($modules_count packages)"
                log "NODE_MODULES_${repo}: exists ($modules_count)"
            elif [ -f "$repo_path/package.json" ]; then
                print_status "WARN" "  ├─ node_modules not found (Run 'npm install')"
                log "NODE_MODULES_${repo}: missing"
            fi
            
            # Check vendor (PHP)
            if [ -d "$repo_path/vendor" ]; then
                print_status "PASS" "  └─ vendor directory found"
                log "VENDOR_DIR_${repo}: exists"
            elif [ -f "$repo_path/composer.json" ]; then
                print_status "WARN" "  └─ vendor not found (Run 'composer install')"
                log "VENDOR_DIR_${repo}: missing"
            fi
        fi
    done
}

################################################################################
# 10. DOCKER
################################################################################

check_docker() {
    print_section "10. DOCKER & CONTAINERIZATION"
    
    # Docker Check
    if command_exists "docker"; then
        local docker_version=$(docker --version)
        print_status "PASS" "Docker: $docker_version"
        log "DOCKER_VERSION: $docker_version"
    else
        print_status "INFO" "Docker: Not installed (Optional)"
        log "DOCKER_STATUS: not_installed"
    fi
    
    # Docker Compose Check
    if command_exists "docker-compose"; then
        local docker_compose_version=$(docker-compose --version)
        print_status "PASS" "Docker Compose: $docker_compose_version"
        log "DOCKER_COMPOSE_VERSION: $docker_compose_version"
    else
        print_status "INFO" "Docker Compose: Not installed (Optional)"
        log "DOCKER_COMPOSE_STATUS: not_installed"
    fi
    
    # Check for docker-compose.yml files
    local repos=("Tradexpro-AdminPortal" "Tradexpro-UserPortal" "Tradexpro-NodeWallet")
    for repo in "${repos[@]}"; do
        local repo_path="/var/www/$repo"
        if [ -f "$repo_path/docker-compose.yml" ]; then
            print_status "PASS" "$repo: docker-compose.yml found"
            log "DOCKER_COMPOSE_${repo}: exists"
        fi
    done
}

################################################################################
# 11. BUILD READINESS
################################################################################

check_build_readiness() {
    print_section "11. BUILD READINESS"
    
    # AdminPortal (Laravel)
    if [ -d "/var/www/Tradexpro-AdminPortal" ]; then
        print_status "INFO" "Tradexpro-AdminPortal (Laravel):"
        log "CHECKING_BUILD: Tradexpro-AdminPortal"
        
        # artisan file
        if [ -f "/var/www/Tradexpro-AdminPortal/artisan" ]; then
            print_status "PASS" "  ├─ artisan file exists"
            log "LARAVEL_ARTISAN: exists"
        else
            print_status "FAIL" "  ├─ artisan file missing"
            log "LARAVEL_ARTISAN: missing"
        fi
        
        # config directory
        if [ -d "/var/www/Tradexpro-AdminPortal/config" ]; then
            print_status "PASS" "  ├─ config directory exists"
            log "LARAVEL_CONFIG: exists"
        else
            print_status "WARN" "  ├─ config directory missing"
            log "LARAVEL_CONFIG: missing"
        fi
        
        # database directory
        if [ -d "/var/www/Tradexpro-AdminPortal/database" ]; then
            print_status "PASS" "  └─ database directory exists"
            log "LARAVEL_DATABASE: exists"
        else
            print_status "WARN" "  └─ database directory missing"
            log "LARAVEL_DATABASE: missing"
        fi
    fi
    
    # UserPortal (TypeScript/React)
    if [ -d "/var/www/Tradexpro-UserPortal" ]; then
        print_status "INFO" "Tradexpro-UserPortal (TypeScript):"
        log "CHECKING_BUILD: Tradexpro-UserPortal"
        
        # tsconfig.json
        if [ -f "/var/www/Tradexpro-UserPortal/tsconfig.json" ]; then
            print_status "PASS" "  ├─ tsconfig.json exists"
            log "TYPESCRIPT_CONFIG: exists"
        else
            print_status "WARN" "  ├─ tsconfig.json missing"
            log "TYPESCRIPT_CONFIG: missing"
        fi
        
        # package.json
        if [ -f "/var/www/Tradexpro-UserPortal/package.json" ]; then
            print_status "PASS" "  ├─ package.json exists"
            
            # Check for build script
            if grep -q '"build"' "/var/www/Tradexpro-UserPortal/package.json"; then
                print_status "PASS" "  ├─ build script defined"
                log "NPM_BUILD_SCRIPT: exists"
            else
                print_status "WARN" "  ├─ build script not defined"
                log "NPM_BUILD_SCRIPT: missing"
            fi
        else
            print_status "FAIL" "  ├─ package.json missing"
            log "PACKAGE_JSON: missing"
        fi
        
        # src directory
        if [ -d "/var/www/Tradexpro-UserPortal/src" ]; then
            print_status "PASS" "  └─ src directory exists"
            log "SRC_DIR: exists"
        else
            print_status "WARN" "  └─ src directory missing"
            log "SRC_DIR: missing"
        fi
    fi
    
    # NodeWallet (Node.js)
    if [ -d "/var/www/Tradexpro-NodeWallet" ]; then
        print_status "INFO" "Tradexpro-NodeWallet (Node.js):"
        log "CHECKING_BUILD: Tradexpro-NodeWallet"
        
        # package.json
        if [ -f "/var/www/Tradexpro-NodeWallet/package.json" ]; then
            print_status "PASS" "  ├─ package.json exists"
            
            # Check for start script
            if grep -q '"start"' "/var/www/Tradexpro-NodeWallet/package.json"; then
                print_status "PASS" "  ├─ start script defined"
                log "NODE_START_SCRIPT: exists"
            else
                print_status "WARN" "  ├─ start script not defined"
                log "NODE_START_SCRIPT: missing"
            fi
        else
            print_status "FAIL" "  ├─ package.json missing"
            log "PACKAGE_JSON: missing"
        fi
        
        # index.js or server.js
        if [ -f "/var/www/Tradexpro-NodeWallet/index.js" ] || [ -f "/var/www/Tradexpro-NodeWallet/server.js" ]; then
            print_status "PASS" "  └─ Entry point file exists"
            log "NODE_ENTRY: exists"
        else
            print_status "WARN" "  └─ Entry point file not found"
            log "NODE_ENTRY: missing"
        fi
    fi
}

################################################################################
# GENERATE REPORT
################################################################################

generate_report() {
    print_section "12. SUMMARY REPORT"
    
    local total=$((PASS_COUNT + WARN_COUNT + FAIL_COUNT))
    local success_rate=$((PASS_COUNT * 100 / total))
    
    echo "" | tee -a "$LOG_FILE"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}" | tee -a "$LOG_FILE"
    echo -e "${CYAN}SYSTEM CHECK SUMMARY${NC}" | tee -a "$LOG_FILE"
    echo -e "${CYAN}═══════════════════════════════════════════════════════════${NC}" | tee -a "$LOG_FILE"
    echo "" | tee -a "$LOG_FILE"
    echo -e "${GREEN}✅ PASSED:${NC} $PASS_COUNT" | tee -a "$LOG_FILE"
    echo -e "${YELLOW}⚠️  WARNINGS:${NC} $WARN_COUNT" | tee -a "$LOG_FILE"
    echo -e "${RED}❌ FAILURES:${NC} $FAIL_COUNT" | tee -a "$LOG_FILE"
    echo -e "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" | tee -a "$LOG_FILE"
    echo -e "Total Checks: $total" | tee -a "$LOG_FILE"
    echo -e "Success Rate: ${success_rate}%" | tee -a "$LOG_FILE"
    echo "" | tee -a "$LOG_FILE"
    
    # Recommendations
    if [ "$FAIL_COUNT" -gt 0 ]; then
        echo -e "${RED}CRITICAL ISSUES FOUND - IMMEDIATE ACTION REQUIRED:${NC}" | tee -a "$LOG_FILE"
        echo "Please review the failures above and address them before proceeding." | tee -a "$LOG_FILE"
    elif [ "$WARN_COUNT" -gt 0 ]; then
        echo -e "${YELLOW}WARNINGS DETECTED - REVIEW BEFORE PROCEEDING:${NC}" | tee -a "$LOG_FILE"
        echo "Some optional components are missing. Verify if they're needed." | tee -a "$LOG_FILE"
    else
        echo -e "${GREEN}✅ ALL SYSTEMS READY FOR DEPLOYMENT${NC}" | tee -a "$LOG_FILE"
    fi
    
    echo "" | tee -a "$LOG_FILE"
    echo "Report Generated: $TIMESTAMP" | tee -a "$LOG_FILE"
    echo "Log File: $LOG_FILE" | tee -a "$LOG_FILE"
    echo "═══════════════════════════════════════════════════════════" | tee -a "$LOG_FILE"
}

################################################################################
# MAIN EXECUTION
################################################################################

main() {
    echo -e "${CYAN}"
    echo "╔══════════════════════════════════════════════════════════╗"
    echo "║     TRADEXPRO SYSTEM CHECK & VALIDATION SCRIPT           ║"
    echo "║     Version: 1.0                                         ║"
    echo "║     Target: goldvninvest.online                          ║"
    echo "╚══════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    
    init_log
    
    check_system_requirements
    check_basic_tools
    check_php_laravel
    check_nodejs_npm
    check_networking
    check_environment_files
    check_git_repos
    check_ssl_certificates
    check_dependencies
    check_docker
    check_build_readiness
    
    generate_report
    
    echo ""
    echo -e "${BLUE}📋 Full details saved to: $LOG_FILE${NC}"
    echo ""
}

# Run main function
main "$@"
