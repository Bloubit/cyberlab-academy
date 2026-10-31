# CyberLab Academy - Cyber Range Infrastructure

## Executive Summary

This document outlines the complete infrastructure design for CyberLab Academy's cyber range system, enabling isolated, temporary, and secure virtual laboratory environments for cybersecurity training.

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              STUDENT INTERACTION                                │
│                                                                                 │
│   Student Browser ──→ API Gateway ──→ Lab Controller ──→ Resource Manager      │
│                                                     │                          │
│                                                     ▼                          │
│                                              ┌─────────────┐                   │
│                                              │   Database   │                   │
│                                              │  PostgreSQL  │                   │
│                                              └─────────────┘                   │
└─────────────────────────────────────────────────────────────────────────────────┘
                                              │
                                              ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            ORCHESTRATION LAYER                                  │
│                                                                                 │
│   ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                     │
│   │   Docker     │    │ Kubernetes   │    │   Proxmox    │                     │
│   │   Swarm      │    │   Cluster    │    │   VE         │                     │
│   └──────────────┘    └──────────────┘    └──────────────┘                     │
│         │                   │                    │                             │
│         ▼                   ▼                    ▼                             │
│   ┌──────────────┐    ┌──────────────┐    ┌──────────────┐                     │
│   │  Containers  │    │    Pods      │    │    VMs       │                     │
│   └──────────────┘    └──────────────┘    └──────────────┘                     │
└─────────────────────────────────────────────────────────────────────────────────┘
                                              │
                                              ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                          INFRASTRUCTURE LAYER                                   │
│                                                                                 │
│   ┌─────────────────────────────────────────────────────────────────────────┐  │
│   │                        PHYSICAL / CLOUD                                 │  │
│   │   Servers: CPU 64+ cores, RAM 512GB+, Storage 10TB+ SSD               │  │
│   │   Network: 10Gbps, VLANs, Firewall                                     │  │
│   │   Cloud: AWS/GCP/Azure for burst capacity                              │  │
│   └─────────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## System Components

### 1. Lab Controller (Central Brain)

**Purpose**: Manages all lab lifecycle operations

**Responsibilities**:
- Lab creation and destruction
- Resource allocation
- User session management
- Flag validation
- Time limit enforcement
- Usage monitoring

**Technology**: Laravel API + Redis Queue Workers

### 2. Machine Templates (Pre-built Images)

**Linux Templates**:
- Ubuntu 22.04 LTS (minimal)
- Kali Linux (security tools)
- Parrot OS (pentesting)
- Alpine Linux (lightweight)

**Windows Templates**:
- Windows Server 2022
- Windows 10 (with security tools)

**Application Templates**:
- DVWA (Damn Vulnerable Web App)
- WebGoat
- Metasploitable
- Custom vulnerable apps

### 3. Deployment System

**Orchestration**:
- Docker Swarm (simple deployments)
- Kubernetes (production scale)
- Proxmox (full VM isolation)

**Automation**:
- Terraform (infrastructure provisioning)
- Ansible (configuration management)

### 4. Security Model

**Isolation**:
- Network segmentation
- Container sandboxing
- Resource limits
- No outbound internet (unless required)

**Prevention**:
- Container escape protection
- Resource exhaustion limits
- Abuse detection
- Automatic termination

## Deployment Modes

### Mode 1: Docker Swarm (Development/Small Scale)

```
┌─────────────────────────────────────────┐
│           Docker Swarm Cluster           │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐ │
│  │ Manager │  │ Worker  │  │ Worker  │ │
│  │  Node   │  │  Node 1 │  │  Node 2 │ │
│  └─────────┘  └─────────┘  └─────────┘ │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │         Overlay Network         │   │
│  │  ┌─────┐ ┌─────┐ ┌─────┐       │   │
│  │  │Lab1 │ │Lab2 │ │Lab3 │ ...   │   │
│  │  └─────┘ └─────┘ └─────┘       │   │
│  └─────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

**Use Case**: Up to 100 concurrent labs

### Mode 2: Kubernetes (Production Scale)

```
┌─────────────────────────────────────────────────────────────┐
│                    Kubernetes Cluster                        │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐ │
│  │                   Control Plane                       │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐              │ │
│  │  │  API    │  │Scheduler│  │Controller│              │ │
│  │  │ Server  │  │         │  │ Manager  │              │ │
│  │  └─────────┘  └─────────┘  └─────────┘              │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐ │
│  │                    Worker Nodes                        │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐              │ │
│  │  │ Node 1  │  │ Node 2  │  │ Node N  │              │ │
│  │  │ ┌─┐┌─┐ │  │ ┌─┐┌─┐ │  │ ┌─┐┌─┐ │              │ │
│  │  │ │P││P│ │  │ │P││P│ │  │ │P││P│ │              │ │
│  │  │ └─┘└─┘ │  │ └─┘└─┘ │  │ └─┘└─┘ │              │ │
│  │  └─────────┘  └─────────┘  └─────────┘              │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

**Use Case**: 100-1000+ concurrent labs

### Mode 3: Proxmox + VMs (Maximum Isolation)

```
┌─────────────────────────────────────────────────────────────┐
│                    Proxmox VE Cluster                        │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐ │
│  │                   Proxmox Nodes                        │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐              │ │
│  │  │ Node 1  │  │ Node 2  │  │ Node 3  │              │ │
│  │  │ ┌─────┐ │  │ ┌─────┐ │  │ ┌─────┐ │              │ │
│  │  │ │ VM1 │ │  │ │ VM3 │ │  │ │ VM5 │ │              │ │
│  │  │ │ VM2 │ │  │ │ VM4 │ │  │ │ VM6 │ │              │ │
│  │  │ └─────┘ │  │ └─────┘ │  │ └─────┘ │              │ │
│  │  └─────────┘  └─────────┘  └─────────┘              │ │
│  └───────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌───────────────────────────────────────────────────────┐ │
│  │                   Storage (Ceph/ZFS)                   │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

**Use Case**: High-security labs requiring full VM isolation

## Lab Types Supported

### 1. Single Machine Labs
- One isolated VM/container
- Student connects via SSH/RDP
- Flag hidden on machine

### 2. Multi-Machine Labs
- Multiple VMs/containers
- Network topology simulation
- Requires lateral movement

### 3. Web Application Labs
- Vulnerable web apps
- Browser-based access
- WAF/firewall bypass scenarios

### 4. Network Labs
- Virtual routers/switches
- VLAN segmentation
- Network attack scenarios

## Resource Allocation

### Per-Lab Limits

| Resource | Default | Maximum | Premium |
|----------|---------|---------|---------|
| CPU Cores | 2 | 8 | 16 |
| RAM | 2 GB | 8 GB | 16 GB |
| Disk | 20 GB | 100 GB | 200 GB |
| Network | 100 Mbps | 1 Gbps | 10 Gbps |
| Time | 4 hours | 8 hours | 24 hours |
| Concurrent | 1 | 3 | 10 |

### Cluster Capacity Planning

| Concurrent Labs | CPU Cores | RAM | Storage | Bandwidth |
|-----------------|-----------|-----|---------|-----------|
| 50 | 128 | 256 GB | 5 TB | 10 Gbps |
| 100 | 256 | 512 GB | 10 TB | 20 Gbps |
| 500 | 1024 | 2 TB | 50 TB | 40 Gbps |
| 1000+ | 2048+ | 4 TB+ | 100 TB+ | 100 Gbps+ |

## Network Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        INTERNET                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     FIREWALL (pfSense)                           │
│  - DDoS Protection                                               │
│  - Rate Limiting                                                 │
│  - IP Filtering                                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DMZ NETWORK                                 │
│  - API Gateway                                                   │
│  - Load Balancer                                                 │
│  - Web Servers                                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                   MANAGEMENT NETWORK                             │
│  - Lab Controller                                                │
│  - Database                                                      │
│  - Monitoring                                                    │
│  - Storage                                                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      LAB NETWORKS (VLANs)                        │
│                                                                  │
│  VLAN 100: Lab Environment 1                                    │
│  VLAN 101: Lab Environment 2                                    │
│  VLAN 102: Lab Environment 3                                    │
│  ...                                                             │
│  VLAN 199: Lab Environment 100                                  │
│                                                                  │
│  Each VLAN is isolated with no cross-communication              │
└─────────────────────────────────────────────────────────────────┘
```

## Security Measures

### 1. Container Security

```yaml
Security Context:
  runAsNonRoot: true
  readOnlyRootFilesystem: true
  allowPrivilegeEscalation: false
  capabilities:
    drop:
      - ALL
    add:
      - NET_BIND_SERVICE
  seccompProfile:
    type: RuntimeDefault
```

### 2. Network Security

- **Network Policies**: Deny all ingress/egress by default
- **Service Mesh**: Istio for mTLS and traffic management
- **Egress Firewall**: Block unauthorized outbound traffic
- **DNS Filtering**: Prevent C2 communication

### 3. Resource Quotas

```yaml
Resource Quotas:
  CPU: Max 4 cores per pod
  Memory: Max 8 GB per pod
  Storage: Max 50 GB per PVC
  Pods: Max 5 per namespace
  Services: Max 3 per namespace
```

### 4. Monitoring & Alerting

- **Prometheus**: Metrics collection
- **Grafana**: Visualization
- **Alertmanager**: Alert routing
- **Falco**: Runtime security monitoring

## Deployment Workflow

### 1. Lab Creation Flow

```
Student Request
      │
      ▼
┌─────────────┐
│   API       │──→ Validate Request
│   Gateway   │
└─────────────┘
      │
      ▼
┌─────────────┐
│    Lab      │──→ Check Resources
│  Controller │──→ Select Template
└─────────────┘──→ Create VLAN
      │
      ▼
┌─────────────┐
│  Resource   │──→ Provision VM/Container
│  Manager    │──→ Configure Network
└─────────────┘──→ Install Software
      │
      ▼
┌─────────────┐
│   Deploy    │──→ Start Machine
│   Service   │──→ Generate Credentials
└─────────────┘──→ Return Connection Info
      │
      ▼
┌─────────────┐
│  Student    │──→ SSH/RDP Access
│  Connects   │
└─────────────┘
```

### 2. Lab Termination Flow

```
Time Limit Reached / Student Stops Lab
      │
      ▼
┌─────────────┐
│    Lab      │──→ Notify Student
│  Controller │
└─────────────┘
      │
      ▼
┌─────────────┐
│   Grace     │──→ 5 minute warning
│   Period    │──→ Auto-save state
└─────────────┘
      │
      ▼
┌─────────────┐
│  Cleanup    │──→ Stop Machine
│  Service    │──→ Remove VLAN
└─────────────┘──→ Delete Storage
      │
      ▼
┌─────────────┐
│  Resource   │──→ Release Resources
│  Release    │──→ Update Metrics
└─────────────┘
```

## Monitoring Dashboard

### Metrics Tracked

**Infrastructure**:
- CPU/Memory/Disk usage per node
- Network bandwidth utilization
- Container/Pod count
- Active connections

**Labs**:
- Active labs count
- Labs starting/running/stopped
- Average lab duration
- Resource consumption per lab

**Security**:
- Failed login attempts
- Unusual network activity
- Container escape attempts
- Resource exhaustion events

**Business**:
- Total labs deployed
- User engagement metrics
- Revenue per lab type
- Customer satisfaction

## Disaster Recovery

### Backup Strategy

| Component | Backup Type | Frequency | Retention |
|-----------|-------------|-----------|-----------|
| Database | Full + WAL | Daily | 30 days |
| Lab Templates | Snapshot | Weekly | 90 days |
| User Data | Incremental | Hourly | 7 days |
| Config | Version Control | Real-time | Forever |

### Recovery Procedures

1. **Database Failure**: Restore from WAL backup (< 5 min RTO)
2. **Node Failure**: Live migration to healthy node (< 1 min)
3. **Cluster Failure**: Failover to secondary cluster (< 5 min)
4. **Data Center**: DNS failover to DR site (< 15 min)

## Cost Optimization

### Strategies

1. **Spot Instances**: Use for non-critical workloads (60-70% savings)
2. **Auto-scaling**: Scale down during low usage
3. **Resource Sharing**: Share resources between labs
4. **Compression**: Compress inactive lab states
5. **Tiered Storage**: Move old labs to cold storage

### Cost Estimation (1000 concurrent labs)

| Component | Monthly Cost |
|-----------|--------------|
| Compute (64 cores) | $5,000 |
| Memory (256 GB) | $2,000 |
| Storage (10 TB SSD) | $1,500 |
| Network (10 Gbps) | $1,000 |
| Software Licenses | $500 |
| **Total** | **$10,000** |

## Next Steps

1. **Phase 1**: Docker Swarm setup (1-2 weeks)
2. **Phase 2**: Kubernetes migration (2-4 weeks)
3. **Phase 3**: Proxmox integration (2-3 weeks)
4. **Phase 4**: Monitoring & optimization (1-2 weeks)
5. **Phase 5**: Production deployment (1 week)

## Appendix

### A. Technology Versions

- Docker: 24.0+
- Kubernetes: 1.28+
- Proxmox VE: 8.0+
- Terraform: 1.6+
- Ansible: 2.16+

### B. Network Ports

| Service | Port | Protocol |
|---------|------|----------|
| SSH | 22 | TCP |
| RDP | 3389 | TCP |
| HTTP | 80 | TCP |
| HTTPS | 443 | TCP |
| MySQL | 3306 | TCP |
| PostgreSQL | 5432 | TCP |
| Redis | 6379 | TCP |
| Prometheus | 9090 | TCP |
| Grafana | 3000 | TCP |
