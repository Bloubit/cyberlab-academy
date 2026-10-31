#!/bin/bash
# CyberLab Academy - Deployment Script
# Usage: ./deploy.sh [environment] [action]

set -e

# Configuration
ENVIRONMENT=${1:-development}
ACTION=${2:-deploy}
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

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

check_prerequisites() {
    log_info "Checking prerequisites..."
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed"
        exit 1
    fi
    
    # Check Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose is not installed"
        exit 1
    fi
    
    # Check kubectl (for staging/production)
    if [ "$ENVIRONMENT" != "development" ]; then
        if ! command -v kubectl &> /dev/null; then
            log_error "kubectl is not installed"
            exit 1
        fi
        
        if ! command -v helm &> /dev/null; then
            log_error "Helm is not installed"
            exit 1
        fi
    fi
    
    # Check terraform (for infrastructure)
    if [ "$ENVIRONMENT" == "production" ]; then
        if ! command -v terraform &> /dev/null; then
            log_error "Terraform is not installed"
            exit 1
        fi
    fi
    
    log_success "Prerequisites check passed"
}

deploy_development() {
    log_info "Deploying to development environment..."
    
    cd "$PROJECT_ROOT"
    
    # Build and start containers
    docker-compose -f devops/docker/docker-compose.yml up -d --build
    
    # Wait for services
    log_info "Waiting for services to start..."
    sleep 10
    
    # Run migrations
    docker-compose -f devops/docker/docker-compose.yml exec backend php artisan migrate
    
    # Seed database
    docker-compose -f devops/docker/docker-compose.yml exec backend php artisan db:seed
    
    log_success "Development environment deployed"
    
    # Show status
    docker-compose -f devops/docker/docker-compose.yml ps
}

deploy_staging() {
    log_info "Deploying to staging environment..."
    
    cd "$PROJECT_ROOT"
    
    # Configure kubectl
    aws eks update-kubeconfig --name cyberlab-staging --region us-east-1
    
    # Apply namespace
    kubectl apply -f devops/kubernetes/base/namespaces.yml
    
    # Apply network policies
    kubectl apply -f devops/kubernetes/base/network-policies.yml -n cyberlab-staging
    
    # Deploy applications
    kubectl apply -f devops/kubernetes/apps/ -n cyberlab-staging
    
    # Wait for rollout
    kubectl rollout status deployment/frontend -n cyberlab-staging --timeout=300s
    kubectl rollout status deployment/backend -n cyberlab-staging --timeout=300s
    kubectl rollout status deployment/worker -n cyberlab-staging --timeout=300s
    
    log_success "Staging environment deployed"
    
    # Show status
    kubectl get pods -n cyberlab-staging
}

deploy_production() {
    log_info "Deploying to production environment..."
    
    cd "$PROJECT_ROOT"
    
    # Configure kubectl
    aws eks update-kubeconfig --name cyberlab-production --region us-east-1
    
    # Apply namespace
    kubectl apply -f devops/kubernetes/base/namespaces.yml
    
    # Apply network policies
    kubectl apply -f devops/kubernetes/base/network-policies.yml -n cyberlab-production
    
    # Deploy applications
    kubectl apply -f devops/kubernetes/apps/ -n cyberlab-production
    
    # Wait for rollout
    kubectl rollout status deployment/frontend -n cyberlab-production --timeout=300s
    kubectl rollout status deployment/backend -n cyberlab-production --timeout=300s
    kubectl rollout status deployment/worker -n cyberlab-production --timeout=300s
    
    log_success "Production environment deployed"
    
    # Show status
    kubectl get pods -n cyberlab-production
}

deploy_monitoring() {
    log_info "Deploying monitoring stack..."
    
    cd "$PROJECT_ROOT"
    
    # Add Helm repos
    helm repo add prometheus-community https://prometheus-community.github.io/helm-charts
    helm repo add grafana https://grafana.github.io/helm-charts
    helm repo update
    
    # Deploy Prometheus
    helm upgrade --install prometheus prometheus-community/prometheus \
        --namespace monitoring \
        -f devops/monitoring/prometheus/values.yml \
        --wait
    
    # Deploy Grafana
    helm upgrade --install grafana grafana/grafana \
        --namespace monitoring \
        -f devops/monitoring/grafana/values.yml \
        --wait
    
    # Deploy AlertManager
    helm upgrade --install alertmanager prometheus-community/alertmanager \
        --namespace monitoring \
        -f devops/monitoring/alertmanager/values.yml \
        --wait
    
    log_success "Monitoring stack deployed"
    
    # Show status
    kubectl get pods -n monitoring
}

teardown() {
    log_warning "Tearing down environment..."
    
    if [ "$ENVIRONMENT" == "development" ]; then
        cd "$PROJECT_ROOT"
        docker-compose -f devops/docker/docker-compose.yml down -v
    elif [ "$ENVIRONMENT" == "staging" ]; then
        kubectl delete namespace cyberlab-staging --ignore-not-found
    elif [ "$ENVIRONMENT" == "production" ]; then
        log_error "Cannot teardown production environment automatically"
        exit 1
    fi
    
    log_success "Environment torn down"
}

show_status() {
    log_info "Environment Status:"
    echo ""
    
    if [ "$ENVIRONMENT" == "development" ]; then
        cd "$PROJECT_ROOT"
        docker-compose -f devops/docker/docker-compose.yml ps
    elif [ "$ENVIRONMENT" == "staging" ]; then
        kubectl get pods -n cyberlab-staging
        echo ""
        kubectl get services -n cyberlab-staging
    elif [ "$ENVIRONMENT" == "production" ]; then
        kubectl get pods -n cyberlab-production
        echo ""
        kubectl get services -n cyberlab-production
    fi
}

# Main
main() {
    echo "=========================================="
    echo "   CyberLab Academy - Deployment"
    echo "=========================================="
    echo "   Environment: $ENVIRONMENT"
    echo "   Action: $ACTION"
    echo "=========================================="
    echo ""
    
    check_prerequisites
    
    case "$ACTION" in
        deploy)
            case "$ENVIRONMENT" in
                development)
                    deploy_development
                    ;;
                staging)
                    deploy_staging
                    ;;
                production)
                    deploy_production
                    ;;
                monitoring)
                    deploy_monitoring
                    ;;
                *)
                    log_error "Unknown environment: $ENVIRONMENT"
                    exit 1
                    ;;
            esac
            ;;
        teardown)
            teardown
            ;;
        status)
            show_status
            ;;
        *)
            log_error "Unknown action: $ACTION"
            echo "Usage: ./deploy.sh [development|staging|production] [deploy|teardown|status]"
            exit 1
            ;;
    esac
    
    log_success "Operation complete!"
}

main "$@"
