#!/bin/bash

################################################################################
# TRADEXPRO DEPLOY & AUTO-SETUP SCRIPT
# Purpose: Idempotent environment setup, build, and deploy for:
#   - /var/www/Tradexpro-AdminPortal  (PHP / Laravel)
#   - /var/www/Tradexpro-UserPortal   (TypeScript / Frontend)
#   - /var/www/Tradexpro-NodeWallet   (Node.js)
# Usage: sudo bash deploy-tradexpro.sh  (run as root)
# Log: /var/log/tradexpro-deploy.log
# Notes: Attempts to auto-fix common issues; skips steps that are already satisfied.
################################################################################

set -o pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

LOGFILE="/var/log/tradexpro-deploy.log"
TIMESTAMP=$(date -u +"%Y-%m-%dT%H:%M:%SZ")
DOMAIN="goldvninvest.online"

ADMIN_DIR="/var/www/Tradexpro-AdminPortal"
USER_DIR="/var/www/Tradexpro-UserPortal"
NODE_DIR="/var/www/Tradexpro-NodeWallet"

# Ensure log directory
mkdir -p "$(dirname "$LOGFILE")"
exec > >(tee -a "$LOGFILE") 2>&1

echo "[$TIMESTAMP] Starting Tradexpro deploy script" 

# Helpers
info(){ echo -e "${BLUE}[INFO]${NC} $*"; }
ok(){ echo -e "${GREEN}[OK]${NC} $*"; }
warn(){ echo -e "${YELLOW}[WARN]${NC} $*"; }
err(){ echo -e "${RED}[ERROR]${NC} $*"; }

command_exists(){ command -v "$1" >/dev/null 2>&1; }

run_or_warn(){
  # run command, capture status, print and return status
  echo "+ $*"
  if eval "$@"; then
    ok "Succeeded: $*"
    return 0
  else
    warn "Failed: $*"
    return 1
  fi
}

require_root(){
  if [ "$EUID" -ne 0 ]; then
    err "This script must be run as root. Use: sudo bash $0"
    exit 1
  fi
}

ensure_git(){
  if ! command_exists git; then
    err "git is not installed. Attempting install (apt)..."
    if command_exists apt-get; then
      apt-get update && apt-get install -y git || { err "Failed to install git"; return 1; }
      ok "git installed"
    else
      warn "Package manager apt-get not available. Please install git manually."
      return 1
    fi
  fi
}

ensure_php_composer(){
  if ! command_exists php; then
    err "PHP not found. Please install PHP (>=7.4) with required extensions."
    return 1
  fi
  if ! command_exists composer; then
    info "Composer not found. Installing composer locally..."
    EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig 2>/dev/null || true)"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" || { err "composer download failed"; return 1; }
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1 || { err "composer setup failed"; rm -f composer-setup.php; return 1; }
    rm -f composer-setup.php
    ok "composer installed to /usr/local/bin/composer"
  fi
}

ensure_node_npm(){
  if ! command_exists node || ! command_exists npm; then
    warn "Node.js/npm not found. Attempting to detect package manager and install Node.js (14+ suggested)."
    if command_exists apt-get; then
      # Attempt to install Node.js from nodesource (safe attempt)
      curl -fsSL https://deb.nodesource.com/setup_16.x | bash - || { warn "Node setup script failed"; }
      apt-get install -y nodejs || { err "Failed to install nodejs via apt"; }
    else
      warn "apt-get not present; please install Node.js/npm manually"
    fi
  fi
  if command_exists npm; then
    ok "npm: $(npm --version)"
  fi
}

ensure_pm2(){
  if ! command_exists pm2; then
    info "pm2 not found. Installing pm2 globally via npm..."
    if command_exists npm; then
      npm install -g pm2 || { warn "Failed to install pm2 globally"; return 1; }
      ok "pm2 installed"
    else
      warn "npm is not available to install pm2"
      return 1
    fi
  else
    ok "pm2: $(pm2 --version)"
  fi
}

# Generate a secure random string
randstr(){
  head -c 24 /dev/urandom | base64 | tr -dc 'a-zA-Z0-9' | head -c "$1"
}

# Idempotent Git update
git_pull_repo(){
  local path="$1"
  if [ ! -d "$path" ]; then
    err "Path not found: $path"
    return 1
  fi
  if [ ! -d "$path/.git" ]; then
    warn "No .git in $path — skipping git pull"
    return 0
  fi
  pushd "$path" >/dev/null || return 1
  git fetch --all --prune || warn "git fetch failed in $path"
  local branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")
  git pull origin "$branch" --ff-only || warn "git pull failed or needs manual merge in $path"
  popd >/dev/null || return 1
}

# Deploy functions per repo

deploy_admin(){
  echo "\n=== Deploying Admin (Laravel) @ $ADMIN_DIR ==="
  if [ ! -d "$ADMIN_DIR" ]; then
    err "Admin directory missing: $ADMIN_DIR"
    return 1
  fi

  git_pull_repo "$ADMIN_DIR"

  ensure_php_composer || warn "PHP/Composer check failed"

  # .env
  if [ ! -f "$ADMIN_DIR/.env" ]; then
    if [ -f "$ADMIN_DIR/.env.example" ]; then
      info "Creating .env from .env.example"
      cp "$ADMIN_DIR/.env.example" "$ADMIN_DIR/.env"
      # Try to populate DB credentials placeholders with generated values if empty
      sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$(randstr 16)/" "$ADMIN_DIR/.env" || true
      ok ".env created"
    else
      warn ".env and .env.example both missing — skipping env creation"
    fi
  else
    ok ".env exists"
  fi

  # APP_KEY
  pushd "$ADMIN_DIR" >/dev/null
  if grep -q "APP_KEY=" .env && grep -q "APP_KEY=\s*$" .env; then
    info "APP_KEY empty — generating"
    if command_exists php && [ -f artisan ]; then
      php artisan key:generate --force || warn "artisan key:generate failed"
    else
      # fallback: set random base64
      sed -i "s/APP_KEY=.*/APP_KEY=base64:$(randstr 32)/" .env || true
    fi
  else
    ok "APP_KEY present or will be generated by artisan"
  fi

  # Composer install
  if [ -f composer.json ]; then
    if [ -d vendor ]; then
      ok "Composer vendor directory present"
    else
      info "Running composer install --no-dev --prefer-dist"
      composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader || warn "composer install failed"
    fi
  fi

  # Permissions
  info "Setting permissions (storage & bootstrap/cache)"
  chown -R www-data:www-data "$ADMIN_DIR/storage" "$ADMIN_DIR/bootstrap/cache" 2>/dev/null || warn "chown failed"
  chmod -R ug+rwx "$ADMIN_DIR/storage" "$ADMIN_DIR/bootstrap/cache" 2>/dev/null || warn "chmod failed"

  # Migrations & cache
  if command_exists php && [ -f artisan ]; then
    info "Running artisan migrate and optimizing"
    php artisan migrate --force || warn "artisan migrate failed"
    php artisan config:cache || warn "config:cache failed"
    php artisan route:cache || warn "route:cache failed"
  else
    warn "artisan not available — skipping migrations"
  fi

  popd >/dev/null
}


deploy_user(){
  echo "\n=== Deploying User Portal (Frontend) @ $USER_DIR ==="
  if [ ! -d "$USER_DIR" ]; then
    err "User directory missing: $USER_DIR"
    return 1
  fi

  git_pull_repo "$USER_DIR"

  ensure_node_npm || warn "Node/npm check failed"

  pushd "$USER_DIR" >/dev/null

  # .env
  if [ ! -f .env ]; then
    if [ -f .env.example ]; then
      cp .env.example .env
      ok ".env created from example"
    else
      warn ".env not found for UserPortal"
    fi
  else
    ok ".env exists"
  fi

  # Install deps
  if [ -f package-lock.json ] || [ -f package.json ]; then
    if [ -d node_modules ]; then
      ok "node_modules present"
    else
      info "Installing npm dependencies"
      npm ci --only=production || npm install || warn "npm install failed"
    fi
  fi

  # Build
  if grep -q '"build"' package.json 2>/dev/null; then
    info "Running npm run build"
    npm run build || warn "Build failed"
  else
    warn "No build script defined in package.json"
  fi

  # Ownership
  chown -R www-data:www-data "$USER_DIR" 2>/dev/null || warn "Failed to chown user portal files"

  popd >/dev/null
}


deploy_node(){
  echo "\n=== Deploying NodeWallet (Node.js) @ $NODE_DIR ==="
  if [ ! -d "$NODE_DIR" ]; then
    err "NodeWallet directory missing: $NODE_DIR"
    return 1
  fi

  git_pull_repo "$NODE_DIR"
  ensure_node_npm || warn "Node/npm check failed"
  ensure_pm2 || warn "pm2 check/install failed"

  pushd "$NODE_DIR" >/dev/null

  if [ ! -f package.json ]; then
    warn "package.json missing in NodeWallet"
  else
    if [ -d node_modules ]; then
      ok "node_modules present"
    else
      info "Installing NodeWallet dependencies"
      npm ci || npm install || warn "npm install failed for NodeWallet"
    fi

    # Start or restart with pm2
    local app_name="tradexpro-nodewallet"
    if pm2 list | grep -q "$app_name"; then
      info "Restarting pm2 process: $app_name"
      pm2 restart "$app_name" || pm2 start npm --name "$app_name" -- start || warn "pm2 restart/start failed"
    else
      info "Starting NodeWallet via pm2"
      pm2 start npm --name "$app_name" -- start || warn "pm2 start failed"
    fi

    # Persist pm2
    pm2 save || warn "pm2 save failed"
    pm2 startup systemd -u root --hp /root >/dev/null 2>&1 || true
  fi

  chown -R www-data:www-data "$NODE_DIR" 2>/dev/null || warn "chown failed for NodeWallet"

  popd >/dev/null
}

# SSL setup/renew helper
check_ssl(){
  echo "\n=== SSL Check for $DOMAIN ==="
  if [ -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" ]; then
    ok "Certificate found for $DOMAIN"
    expiry=$(openssl x509 -in "/etc/letsencrypt/live/$DOMAIN/fullchain.pem" -noout -enddate | cut -d= -f2)
    info "Expiry: $expiry"
  else
    warn "No certificate found for $DOMAIN"
    if command_exists certbot; then
      info "Attempting certbot certonly (webroot) for $DOMAIN"
      if [ -d /var/www/html ]; then
        certbot certonly --webroot -w /var/www/html -d "$DOMAIN" --non-interactive --agree-tos -m admin@$DOMAIN || warn "certbot failed to obtain cert"
      else
        warn "No common webroot found; cannot run certbot automatically"
      fi
    else
      warn "certbot not installed; please install certbot to manage SSL"
    fi
  fi
}

# Main
require_root
ensure_git

# Run deployments
deploy_admin
deploy_user
deploy_node

# Networking/SSL
check_ssl

# Final reminders and summary
echo "\n=== FINISHED ==="
info "Deployment log: $LOGFILE"
info "Review warnings/errors above and check services (nginx, php-fpm, mysql, pm2)"
info "To view the log: sudo tail -n 200 $LOGFILE"

exit 0
