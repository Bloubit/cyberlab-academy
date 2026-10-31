#!/bin/bash
# CyberLab Academy - Deployment Script
# Usage: ./deploy.sh [environment]

set -e

# Configuration
ENVIRONMENT=${1:-development}
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
    
    # Check Kubernetes (if deploying to k8s)
    if [ "$ENVIRONMENT" == "production" ]; then
        if ! command -v kubectl &> /dev/null; then
            log_error "kubectl is not installed"
            exit 1
        fi
        
        if ! command -v terraform &> /dev/null; then
            log_error "terraform is not installed"
            exit 1
        fi
    fi
    
    log_success "Prerequisites check passed"
}

deploy_docker_swarm() {
    log_info "Deploying to Docker Swarm..."
    
    cd "$PROJECT_ROOT/infrastructure/docker"
    
    # Initialize Swarm if not already initialized
    if ! docker info --format '{{.Swarm.LocalNodeState}}' | grep -q 'active'; then
        log_info "Initializing Docker Swarm..."
        docker swarm init
    fi
    
    # Deploy stack
    docker stack deploy -c docker-compose.yml cyberlab
    
    log_success "Docker Swarm deployment complete"
}

deploy_kubernetes() {
    log_info "Deploying to Kubernetes..."
    
    cd "$PROJECT_ROOT/infrastructure/kubernetes"
    
    # Apply base configurations
    kubectl apply -f base/
    
    # Apply application deployments
    kubectl apply -f apps/
    
    # Wait for deployments to be ready
    kubectl wait --for=condition=available deployment/lab-controller -n cyberlab --timeout=300s
    kubectl wait --for=condition=available deployment/postgresql -n cyberlab --timeout=300s
    kubectl wait --for=condition=available deployment/redis -n cyberlab --timeout=300s
    
    log_success "Kubernetes deployment complete"
}

deploy_terraform() {
    log_info "Deploying infrastructure with Terraform..."
    
    cd "$PROJECT_ROOT/infrastructure/terraform"
    
    # Initialize Terraform
    terraform init
    
    # Plan deployment
    terraform plan -var="environment=$ENVIRONMENT" -out=tfplan
    
    # Apply deployment
    terraform apply tfplan
    
    log_success "Terraform deployment complete"
}

deploy_monitoring() {
    log_info "Deploying monitoring stack..."
    
    cd "$PROJECT_ROOT/infrastructure/docker"
    
    # Deploy Prometheus
    docker service create \
        --name cyberlab-prometheus \
        --network management \
        --mount type=bind,source="$(pwd)/monitoring/prometheus",target=/etc/prometheus \
        prom/prometheus:latest
    
    # Deploy Grafana
    docker service create \
        --name cyberlab-grafana \
        --network management \
        --publish 3000:3000 \
        -e GF_SECURITY_ADMIN_PASSWORD=admin \
        grafana/grafana:latest
    
    log_success "Monitoring deployment complete"
}

deploy_application() {
    log_info "Deploying application..."
    
    cd "$PROJECT_ROOT/backend"
    
    # Build and deploy backend
    docker build -t cyberlab-backend:latest .
    docker service create \
        --name cyberlab-backend \
        --network management \
        --replicas 3 \
        cyberlab-backend:latest
    
    # Deploy frontend
    cd "$PROJECT_ROOT/frontend"
    docker build -t cyberlab-frontend:latest .
    docker service create \
        --name cyberlab-frontend \
        --network management \
        --publish 80:3000 \
        cyberlab-frontend:latest
    
    log_success "Application deployment complete"
}

show_status() {
    log_info "Deployment Status:"
    echo ""
    
    if [ "$ENVIRONMENT" == "production" ]; then
        kubectl get pods -n cyberlab
        kubectl get services -n cyberlab
    else
        docker stack services cyberlab
    fi
    
    echo ""
    log_info "Monitoring URLs:"
    echo "  - Grafana: http://localhost:3000"
    echo "  - Prometheus: http://localhost:9090"
    echo ""
    log_info "Application URLs:"
    echo "  - Frontend: http://localhost"
    echo "  - API: http://localhost/api"
}

# Main
main() {
    echo "=========================================="
    echo "   CyberLab Academy - Deployment"
    echo "=========================================="
    echo "   Environment: $ENVIRONMENT"
    echo "=========================================="
    echo ""
    
    check_prerequisites
    
    case "$ENVIRONMENT" in
        development|staging)
            deploy_docker_swarm
            deploy_monitoring
            deploy_application
            ;;
        production)
            deploy_terraform
            deploy_kubernetes
            deploy_monitoring
            deploy_application
            ;;
        *)
            log_error "Unknown environment: $ENVIRONMENT"
            exit 1
            ;;
    esac
    
    show_status
    
    log_success "Deployment complete!"
}

main "$@"
