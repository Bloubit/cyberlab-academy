# CyberLab Academy - Infrastructure

Complete cyber range infrastructure for isolated cybersecurity labs.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    STUDENT INTERACTION                       │
│   Browser ──→ API ──→ Lab Controller ──→ Resource Manager   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    ORCHESTRATION LAYER                       │
│   Docker Swarm / Kubernetes / Proxmox                       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    INFRASTRUCTURE                            │
│   Servers / Network / Storage / Monitoring                  │
└─────────────────────────────────────────────────────────────┘
```

## Components

### Docker Swarm (Development)
- Simple deployment
- Up to 100 concurrent labs
- Easy management

### Kubernetes (Production)
- Auto-scaling
- High availability
- 100-1000+ concurrent labs

### Proxmox (Maximum Security)
- Full VM isolation
- Network segmentation
- Enterprise features

## Quick Start

### Docker Swarm

```bash
# Initialize swarm
docker swarm init

# Deploy stack
cd infrastructure/docker
docker stack deploy -c docker-compose.yml cyberlab

# Check status
docker stack services cyberlab
```

### Kubernetes

```bash
# Apply configurations
cd infrastructure/kubernetes
kubectl apply -f base/
kubectl apply -f apps/

# Check status
kubectl get pods -n cyberlab
```

### Terraform

```bash
# Initialize
cd infrastructure/terraform
terraform init

# Plan
terraform plan -var="environment=production"

# Apply
terraform apply
```

## File Structure

```
infrastructure/
├── docker/
│   ├── templates/          # Machine templates
│   │   ├── linux-base/     # Base Linux image
│   │   ├── kali/           # Kali Linux
│   │   └── web-dvwa/       # DVWA vulnerable app
│   ├── configs/            # Configuration files
│   ├── scripts/            # Utility scripts
│   └── docker-compose.yml  # Swarm deployment
│
├── kubernetes/
│   ├── base/               # Base configurations
│   ├── overlays/           # Environment overlays
│   ├── apps/               # Application deployments
│   └── monitoring/         # Monitoring stack
│
├── terraform/
│   ├── modules/            # Reusable modules
│   └── environments/       # Environment configs
│       ├── dev/
│       ├── staging/
│       └── prod/
│
├── ansible/
│   ├── roles/              # Configuration roles
│   ├── playbooks/          # Deployment playbooks
│   ├── templates/          # Configuration templates
│   └── vars/               # Variables
│
├── monitoring/
│   ├── prometheus/         # Prometheus config
│   ├── grafana/            # Grafana dashboards
│   └── alertmanager/       # Alert configuration
│
├── scripts/                # Utility scripts
└── docs/                   # Documentation
```

## Lab Types

### Single Machine
- One isolated VM/container
- SSH/RDP access
- Flag hidden on machine

### Multi-Machine
- Multiple VMs/containers
- Network topology
- Lateral movement

### Web Application
- Vulnerable web apps
- Browser access
- WAF bypass scenarios

### Network
- Virtual routers/switches
- VLAN segmentation
- Network attacks

## Resource Limits

| Resource | Default | Maximum |
|----------|---------|---------|
| CPU | 2 cores | 8 cores |
| RAM | 2 GB | 16 GB |
| Disk | 20 GB | 100 GB |
| Time | 4 hours | 24 hours |

## Security Features

- Container isolation
- Network segmentation
- Resource limits
- No outbound internet
- Abuse detection
- Automatic termination

## Monitoring

- Prometheus metrics
- Grafana dashboards
- Alert rules
- Real-time monitoring

## Deployment

```bash
# Development
./scripts/deploy.sh development

# Staging
./scripts/deploy.sh staging

# Production
./scripts/deploy.sh production
```

## Documentation

- [Infrastructure Design](docs/INFRASTRUCTURE-DESIGN.md)
- [Security Model](docs/SECURITY-MODEL.md)

## License

Proprietary - CyberLab Academy
