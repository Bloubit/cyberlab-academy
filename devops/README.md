# CyberLab Academy - DevOps Infrastructure

Complete production-grade DevOps infrastructure for cybersecurity education platform.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        INTERNET                                          │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    CLOUDFLARE CDN + WAF                                   │
│  DDoS Protection | WAF | CDN | SSL/TLS                                  │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    AWS APPLICATION LOAD BALANCER                         │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                    AWS EKS KUBERNETES CLUSTER                            │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐      │
│  │ Frontend    │ │ Backend     │ │ Worker      │ │ Lab         │      │
│  │ Pods        │ │ Pods        │ │ Pods        │ │ Pods        │      │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘      │
└─────────────────────────────────────────────────────────────────────────┘
                                      │
                    ┌─────────────────┼─────────────────┐
                    ▼                 ▼                 ▼
┌──────────────────────┐ ┌──────────────────────┐ ┌──────────────────────┐
│ PostgreSQL Cluster   │ │ Redis Cluster        │ │ S3 Storage          │
│ Primary + Replicas   │ │ Primary + Replicas   │ │ Multiple Buckets    │
└──────────────────────┘ └──────────────────────┘ └──────────────────────┘
```

## Quick Start

### Development

```bash
# Clone repository
git clone https://github.com/cyberlab/academy.git
cd academy

# Start development environment
./devops/scripts/deploy.sh development deploy

# Access services
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# Adminer: http://localhost:8080
# Redis Commander: http://localhost:8081
```

### Staging

```bash
# Deploy to staging
./devops/scripts/deploy.sh staging deploy

# Check status
./devops/scripts/deploy.sh staging status
```

### Production

```bash
# Deploy to production
./devops/scripts/deploy.sh production deploy

# Check status
./devops/scripts/deploy.sh production status
```

## Project Structure

```
devops/
├── docker/
│   ├── frontend/           # Frontend Dockerfile
│   ├── backend/            # Backend Dockerfile
│   ├── nginx/              # Nginx configuration
│   ├── worker/             # Worker Dockerfile
│   └── docker-compose.yml  # Development compose
│
├── kubernetes/
│   ├── base/               # Base configurations
│   ├── apps/               # Application deployments
│   ├── monitoring/         # Monitoring stack
│   └── overlays/           # Environment overlays
│       ├── dev/
│       ├── staging/
│       └── prod/
│
├── terraform/
│   ├── modules/            # Reusable modules
│   │   ├── vpc/
│   │   ├── eks/
│   │   ├── rds/
│   │   ├── redis/
│   │   └── s3/
│   └── environments/       # Environment configs
│       ├── dev/
│       ├── staging/
│       └── prod/
│
├── ansible/
│   ├── roles/              # Configuration roles
│   ├── playbooks/          # Deployment playbooks
│   └── templates/          # Configuration templates
│
├── ci-cd/
│   └── .github/workflows/  # GitHub Actions
│       ├── ci.yml
│       ├── deploy-staging.yml
│       └── deploy-production.yml
│
├── monitoring/
│   ├── prometheus/         # Prometheus configuration
│   ├── grafana/            # Grafana dashboards
│   ├── alertmanager/       # Alert configuration
│   └── elk/                # ELK stack
│
├── scripts/
│   ├── deploy.sh           # Deployment script
│   ├── backup.sh           # Backup script
│   └── restore.sh          # Restore script
│
└── docs/
    ├── ARCHITECTURE.md     # Architecture documentation
    ├── DEPLOYMENT.md       # Deployment guide
    ├── SECURITY.md         # Security documentation
    └── DISASTER-RECOVERY.md # DR plan
```

## Environments

### Development
- Single replica
- Debug mode enabled
- Hot reload
- Local databases

### Staging
- 2 replicas per service
- Production-like configuration
- Autoscaling enabled
- Managed databases

### Production
- 3+ replicas per service
- High availability
- Multi-AZ deployment
- Automated backups
- Full monitoring

## Components

### Frontend (Next.js)
- Port: 3000
- Replicas: 3-10
- CPU: 100m-500m
- Memory: 128Mi-512Mi

### Backend (Laravel)
- Port: 8000
- Replicas: 3-15
- CPU: 200m-1000m
- Memory: 256Mi-1Gi

### Worker (Queue)
- Replicas: 2-10
- CPU: 200m-500m
- Memory: 256Mi-512Mi

### PostgreSQL
- Primary + 2 Replicas
- Multi-AZ (production)
- Automated backups
- Encryption at rest

### Redis
- Primary + 2 Replicas
- Cluster mode
- Encryption in transit
- Persistence enabled

## Monitoring

### Metrics
- Prometheus for collection
- Grafana for visualization
- Custom dashboards

### Logging
- Fluentd for collection
- Elasticsearch for storage
- Kibana for visualization

### Alerting
- AlertManager
- Slack integration
- PagerDuty integration
- Email notifications

## Security

### Network
- VPC isolation
- Private subnets
- Security groups
- Network policies

### Application
- HTTPS everywhere
- Security headers
- Rate limiting
- Input validation

### Data
- Encryption at rest
- Encryption in transit
- Key management (KMS)
- Secrets management

## Backup & Recovery

### RPO (Recovery Point Objective)
- Database: 1 hour
- Storage: Real-time replication
- Configuration: Daily

### RTO (Recovery Time Objective)
- Database failover: < 1 minute
- Application restart: < 5 minutes
- Full recovery: < 15 minutes

### Backup Schedule
- Database: Daily full, hourly incremental
- Storage: Cross-region replication
- Kubernetes: Daily manifests

## CI/CD Pipeline

### Stages
1. Code Review (PR)
2. Linting & Type Checking
3. Unit Tests
4. Security Scanning
5. Docker Build
6. Deploy to Staging
7. Integration Tests
8. Deploy to Production

### Tools
- GitHub Actions
- Docker Buildx
- Trivy (security)
- Snyk (dependencies)
- GitLeaks (secrets)

## Scaling

### Horizontal Pod Autoscaling
- CPU threshold: 70%
- Memory threshold: 80%
- Scale up: Immediate
- Scale down: 5 minutes stabilization

### Cluster Autoscaling
- Min nodes: 3
- Max nodes: 20
- Scale-up threshold: 80% utilization

## Cost Optimization

### Strategies
- Spot instances for non-critical workloads
- Reserved instances for base load
- Auto-scaling for variable load
- S3 Intelligent-Tiering

### Estimated Monthly Cost
- Development: ~$200
- Staging: ~$800
- Production: ~$2,500

## Troubleshooting

### Common Issues

#### Pod not starting
```bash
kubectl describe pod <pod-name> -n cyberlab-production
kubectl logs <pod-name> -n cyberlab-production
```

#### Database connection issues
```bash
kubectl exec -it <backend-pod> -n cyberlab-production -- php artisan tinker
```

#### High latency
```bash
# Check pod resources
kubectl top pods -n cyberlab-production

# Check database
kubectl exec -it <postgres-pod> -- psql -U cyberlab -c "SELECT * FROM pg_stat_activity;"
```

## Support

- Documentation: docs/
- Issues: GitHub Issues
- Slack: #devops channel
- On-call: PagerDuty rotation

## License

Proprietary - CyberLab Academy
