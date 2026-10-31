#!/bin/bash
# CyberLab Academy - Backup Script
# Usage: ./backup.sh [database|storage|all]

set -e

# Configuration
BACKUP_TYPE=${1:-all}
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
S3_BUCKET="cyberlab-backups"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

backup_database() {
    log_info "Starting database backup..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR/database"
    
    # Get database credentials from environment
    DB_HOST=${DB_HOST:-postgres}
    DB_PORT=${DB_PORT:-5432}
    DB_NAME=${DB_NAME:-cyberlab}
    DB_USER=${DB_USER:-cyberlab}
    
    # Full backup
    log_info "Creating full database backup..."
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
        -Fc -Z 9 > "$BACKUP_DIR/database/full_backup_${DATE}.dump"
    
    # Schema only backup
    log_info "Creating schema backup..."
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
        --schema-only > "$BACKUP_DIR/database/schema_${DATE}.sql"
    
    # Data only backup
    log_info "Creating data backup..."
    pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
        --data-only > "$BACKUP_DIR/database/data_${DATE}.sql"
    
    # WAL archiving (if configured)
    log_info "Archiving WAL files..."
    pg_archivecleanup /var/lib/postgresql/data/pg_wal 000000010000000000000001 2>/dev/null || true
    
    # Compress backups
    log_info "Compressing backups..."
    gzip "$BACKUP_DIR/database/schema_${DATE}.sql"
    gzip "$BACKUP_DIR/database/data_${DATE}.sql"
    
    # Upload to S3
    log_info "Uploading to S3..."
    aws s3 cp "$BACKUP_DIR/database/full_backup_${DATE}.dump" \
        "s3://$S3_BUCKET/database/full_backup_${DATE}.dump" \
        --storage-class STANDARD_IA
    
    aws s3 cp "$BACKUP_DIR/database/schema_${DATE}.sql.gz" \
        "s3://$S3_BUCKET/database/schema_${DATE}.sql.gz"
    
    aws s3 cp "$BACKUP_DIR/database/data_${DATE}.sql.gz" \
        "s3://$S3_BUCKET/database/data_${DATE}.sql.gz"
    
    # Cleanup local backups older than 7 days
    find "$BACKUP_DIR/database" -type f -mtime +7 -delete
    
    # Cleanup S3 backups older than 30 days
    aws s3 ls "s3://$S3_BUCKET/database/" | while read -r line; do
        createDate=$(echo "$line" | awk '{print $1" "$2}')
        createDate=$(date -d "$createDate" +%s 2>/dev/null || echo "0")
        olderDate=$(date -d "30 days ago" +%s)
        if [[ $createDate -lt $olderDate ]]; then
            fileName=$(echo "$line" | awk '{print $4}')
            if [[ -n "$fileName" ]]; then
                aws s3 rm "s3://$S3_BUCKET/database/$fileName"
            fi
        fi
    done
    
    log_success "Database backup completed"
}

backup_storage() {
    log_info "Starting storage backup..."
    
    # Create backup directory
    mkdir -p "$BACKUP_DIR/storage"
    
    # Backup S3 buckets
    for bucket in cyberlab-frontend cyberlab-media; do
        log_info "Syncing bucket: $bucket"
        aws s3 sync "s3://$bucket" "$BACKUP_DIR/storage/$bucket" --dryrun 2>&1 | \
        while read -r line; do
            echo "Would sync: $line"
        done
    done
    
    # Create inventory of objects
    log_info "Creating object inventory..."
    aws s3api list-objects-v2 --bucket cyberlab-media --max-items 1000 | \
        jq -r '.Contents[] | .Key + "," + .Size + "," + .LastModified' > \
        "$BACKUP_DIR/storage/media_inventory_${DATE}.csv"
    
    log_success "Storage backup completed"
}

backup_kubernetes() {
    log_info "Starting Kubernetes backup..."
    
    mkdir -p "$BACKUP_DIR/kubernetes"
    
    # Backup deployments
    kubectl get deployments --all-namespaces -o yaml > \
        "$BACKUP_DIR/kubernetes/deployments_${DATE}.yaml"
    
    # Backup services
    kubectl get services --all-namespaces -o yaml > \
        "$BACKUP_DIR/kubernetes/services_${DATE}.yaml"
    
    # Backup configmaps
    kubectl get configmaps --all-namespaces -o yaml > \
        "$BACKUP_DIR/kubernetes/configmaps_${DATE}.yaml"
    
    # Backup secrets (encrypted)
    kubectl get secrets --all-namespaces -o yaml | \
        kubeconform -ignore-missing-schemas > \
        "$BACKUP_DIR/kubernetes/secrets_${DATE}.yaml"
    
    # Backup ingress
    kubectl get ingress --all-namespaces -o yaml > \
        "$BACKUP_DIR/kubernetes/ingress_${DATE}.yaml"
    
    # Backup persistent volumes
    kubectl get pv -o yaml > \
        "$BACKUP_DIR/kubernetes/pv_${DATE}.yaml"
    
    kubectl get pvc --all-namespaces -o yaml > \
        "$BACKUP_DIR/kubernetes/pvc_${DATE}.yaml"
    
    # Compress
    tar -czf "$BACKUP_DIR/kubernetes/kubernetes_backup_${DATE}.tar.gz" \
        -C "$BACKUP_DIR/kubernetes" .
    
    # Upload to S3
    aws s3 cp "$BACKUP_DIR/kubernetes/kubernetes_backup_${DATE}.tar.gz" \
        "s3://$S3_BUCKET/kubernetes/kubernetes_backup_${DATE}.tar.gz"
    
    # Cleanup
    rm -rf "$BACKUP_DIR/kubernetes"
    
    log_success "Kubernetes backup completed"
}

backup_configuration() {
    log_info "Starting configuration backup..."
    
    mkdir -p "$BACKUP_DIR/configuration"
    
    # Backup Terraform state
    log_info "Backing up Terraform state..."
    aws s3 sync s3://cyberlab-terraform-state "$BACKUP_DIR/configuration/terraform/"
    
    # Backup Kubernetes manifests
    cp -r devops/kubernetes/ "$BACKUP_DIR/configuration/kubernetes/"
    
    # Backup Docker Compose files
    cp devops/docker/docker-compose.yml "$BACKUP_DIR/configuration/"
    
    # Backup monitoring configs
    cp -r devops/monitoring/ "$BACKUP_DIR/configuration/monitoring/"
    
    # Compress
    tar -czf "$BACKUP_DIR/configuration/config_backup_${DATE}.tar.gz" \
        -C "$BACKUP_DIR/configuration" .
    
    # Upload to S3
    aws s3 cp "$BACKUP_DIR/configuration/config_backup_${DATE}.tar.gz" \
        "s3://$S3_BUCKET/configuration/config_backup_${DATE}.tar.gz"
    
    # Cleanup
    rm -rf "$BACKUP_DIR/configuration"
    
    log_success "Configuration backup completed"
}

restore_database() {
    local backup_file=$1
    
    if [ -z "$backup_file" ]; then
        log_error "Please provide backup file"
        exit 1
    fi
    
    log_info "Restoring database from: $backup_file"
    
    # Download from S3 if needed
    if [[ "$backup_file" == s3://* ]]; then
        aws s3 cp "$backup_file" "/tmp/restore_backup.dump"
        backup_file="/tmp/restore_backup.dump"
    fi
    
    # Stop application
    kubectl scale deployment/backend --replicas=0 -n cyberlab-production
    kubectl scale deployment/worker --replicas=0 -n cyberlab-production
    
    # Restore database
    pg_restore -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
        --clean --if-exists "$backup_file"
    
    # Start application
    kubectl scale deployment/backend --replicas=3 -n cyberlab-production
    kubectl scale deployment/worker --replicas=2 -n cyberlab-production
    
    log_success "Database restore completed"
}

verify_backup() {
    local backup_file=$1
    
    log_info "Verifying backup: $backup_file"
    
    # Download if S3
    if [[ "$backup_file" == s3://* ]]; then
        aws s3 cp "$backup_file" "/tmp/verify_backup.dump"
        backup_file="/tmp/verify_backup.dump"
    fi
    
    # Verify dump file
    pg_restore --list "$backup_file" > /dev/null 2>&1
    
    if [ $? -eq 0 ]; then
        log_success "Backup verification passed"
        return 0
    else
        log_error "Backup verification failed"
        return 1
    fi
}

# Main
main() {
    echo "=========================================="
    echo "   CyberLab Academy - Backup"
    echo "=========================================="
    echo "   Type: $BACKUP_TYPE"
    echo "   Date: $DATE"
    echo "=========================================="
    echo ""
    
    case "$BACKUP_TYPE" in
        database)
            backup_database
            ;;
        storage)
            backup_storage
            ;;
        kubernetes)
            backup_kubernetes
            ;;
        configuration)
            backup_configuration
            ;;
        all)
            backup_database
            backup_storage
            backup_kubernetes
            backup_configuration
            ;;
        restore)
            restore_database "$2"
            ;;
        verify)
            verify_backup "$2"
            ;;
        *)
            log_error "Unknown backup type: $BACKUP_TYPE"
            echo "Usage: ./backup.sh [database|storage|kubernetes|configuration|all|restore|verify]"
            exit 1
            ;;
    esac
    
    log_success "Backup operation complete!"
}

main "$@"
