# CyberLab Academy - Security Model

## Overview

This document outlines the security measures implemented in the CyberLab Academy cyber range to prevent abuse, escape attacks, and resource exhaustion.

## Security Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            SECURITY LAYERS                                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  Layer 1: Network Security                                                      │
│  ├── Firewall rules                                                             │
│  ├── Network segmentation (VLANs)                                              │
│  ├── Network policies (Kubernetes)                                             │
│  └── Traffic monitoring                                                         │
│                                                                                 │
│  Layer 2: Container Security                                                    │
│  ├── Non-root execution                                                        │
│  ├── Read-only filesystem                                                      │
│  ├── Dropped capabilities                                                      │
│  └── Seccomp profiles                                                          │
│                                                                                 │
│  Layer 3: Resource Security                                                     │
│  ├── CPU limits                                                                │
│  ├── Memory limits                                                             │
│  ├── Disk quotas                                                               │
│  └── Network bandwidth limits                                                  │
│                                                                                 │
│  Layer 4: Application Security                                                  │
│  ├── Authentication & authorization                                            │
│  ├── Input validation                                                          │
│  ├── API rate limiting                                                         │
│  └── Audit logging                                                             │
│                                                                                 │
│  Layer 5: Monitoring & Response                                                 │
│  ├── Real-time monitoring                                                      │
│  ├── Anomaly detection                                                         │
│  ├── Automated response                                                        │
│  └── Incident response                                                         │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## 1. Network Security

### 1.1 Network Isolation

Each lab runs in its own isolated network with no cross-communication:

```yaml
Network Configuration:
  Driver: overlay
  Internal: true  # No external access
  Encryption: enabled
  
Network Policies:
  - Deny all ingress by default
  - Deny all egress by default
  - Allow only lab-specific traffic
  - Allow DNS resolution
```

### 1.2 Firewall Rules

```bash
# Default deny all
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT DROP

# Allow established connections
iptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT
iptables -A OUTPUT -m state --state ESTABLISHED,RELATED -j ACCEPT

# Allow lab internal traffic
iptables -A FORWARD -s 172.17.0.0/12 -d 172.17.0.0/12 -j ACCEPT

# Block lab-to-lab communication
iptables -A FORWARD -s 172.17.0.0/12 -d 172.17.0.0/12 -j DROP

# Allow DNS
iptables -A OUTPUT -p udp --dport 53 -j ACCEPT
iptables -A OUTPUT -p tcp --dport 53 -j ACCEPT
```

### 1.3 Egress Filtering

```yaml
Egress Rules:
  Allowed:
    - DNS (port 53)
    - NTP (port 123)
    - HTTP/HTTPS (for updates only, via proxy)
    
  Blocked:
    - SSH to external hosts
    - SMTP (prevents email abuse)
    - IRC (prevents C2 communication)
    - Custom TCP/UDP ports
```

## 2. Container Security

### 2.1 Container Runtime Security

```yaml
Security Context:
  runAsNonRoot: true
  runAsUser: 1000
  runAsGroup: 1000
  fsGroup: 1000
  
  capabilities:
    drop:
      - ALL
    add:
      - NET_BIND_SERVICE  # Only for port binding
  
  allowPrivilegeEscalation: false
  
  readOnlyRootFilesystem: false  # Some tools need write access
  
  seccompProfile:
    type: RuntimeDefault
    
  appArmorProfile: "docker-default"
```

### 2.2 Container Isolation

```yaml
Isolation Measures:
  - PID namespace isolation
  - Network namespace isolation
  - Mount namespace isolation
  - User namespace isolation (when possible)
  
  Additional:
    - No host network access
    - No privileged mode
    - No Docker socket mounting
    - Limited syscalls via seccomp
```

### 2.3 Image Security

```yaml
Image Security:
  Base Images:
    - Use official, verified images
    - Scan for vulnerabilities (Trivy)
    - Regular updates
    
  Build Process:
    - Multi-stage builds
    - Minimal attack surface
    - No secrets in images
    
  Registry:
    - Private registry only
    - Image signing (Notary)
    - Content trust enabled
```

## 3. Resource Security

### 3.1 Resource Quotas

```yaml
Per-Lab Limits:
  CPU:
    Request: 0.5 cores
    Limit: 2 cores
    Hard Limit: 4 cores
    
  Memory:
    Request: 512 MB
    Limit: 2 GB
    Hard Limit: 4 GB
    
  Storage:
    Request: 10 GB
    Limit: 20 GB
    Hard Limit: 50 GB
    
  Network:
    Bandwidth: 100 Mbps
    Connections: 1000
    
  Time:
    Default: 4 hours
    Maximum: 24 hours
    
  Processes:
    Max PIDs: 512
    Max Threads: 1024
```

### 3.2 Resource Monitoring

```python
# Resource monitoring script
async def monitor_resources(container_id: str):
    """Monitor container resources"""
    container = docker_client.containers.get(container_id)
    
    # Get stats
    stats = container.stats(stream=False)
    
    # Calculate usage
    cpu_usage = calculate_cpu_usage(stats)
    memory_usage = calculate_memory_usage(stats)
    network_usage = calculate_network_usage(stats)
    
    # Check limits
    if cpu_usage > 90:
        alert_high_cpu(container_id, cpu_usage)
        
    if memory_usage > 90:
        alert_high_memory(container_id, memory_usage)
        
    # Store metrics
    store_metrics(container_id, {
        'cpu': cpu_usage,
        'memory': memory_usage,
        'network': network_usage,
        'timestamp': datetime.utcnow()
    })
```

### 3.3 Abuse Prevention

```yaml
Abuse Detection:
  CPU Abuse:
    - Sustained high CPU usage (>80% for >5 min)
    - Mining software detection
    - Response: Alert + throttle
    
  Memory Abuse:
    - Memory leak detection
    - OOM kill monitoring
    - Response: Alert + restart
    
  Network Abuse:
    - Port scanning detection
    - DDoS attempt detection
    - C2 communication detection
    - Response: Alert + block
    
  Storage Abuse:
    - Disk fill detection
    - Large file creation
    - Response: Alert + cleanup
```

## 4. Application Security

### 4.1 Authentication

```yaml
Authentication:
  Method: JWT + API Keys
  
  JWT Tokens:
    - Algorithm: RS256
    - Expiry: 1 hour
    - Refresh: 7 days
    
  API Keys:
    - Random generation
    - Scoped permissions
    - Rate limiting
    
  MFA:
    - TOTP (Google Authenticator)
    - Backup codes
    - Hardware keys (optional)
```

### 4.2 Authorization

```yaml
Authorization:
  Roles:
    - Student:
        - Start/stop own labs
        - Submit flags
        - View own progress
        
    - Instructor:
        - Create labs
        - View student progress
        - Grade submissions
        
    - Admin:
        - Full access
        - User management
        - System configuration
        
  Permissions:
    - Lab.start
    - Lab.stop
    - Lab.reset
    - Flag.submit
    - Certificate.view
    - Course.enroll
```

### 4.3 Input Validation

```python
# Input validation example
from pydantic import BaseModel, validator

class LabRequest(BaseModel):
    lab_id: str
    machine_image: str
    
    @validator('lab_id')
    def validate_lab_id(cls, v):
        if not re.match(r'^lab-[a-f0-9]{8}$', v):
            raise ValueError('Invalid lab ID format')
        return v
        
    @validator('machine_image')
    def validate_machine_image(cls, v):
        allowed_images = get_allowed_images()
        if v not in allowed_images:
            raise ValueError('Image not allowed')
        return v
```

### 4.4 Rate Limiting

```yaml
Rate Limits:
  Global:
    - 100 requests/minute per IP
    - 1000 requests/hour per user
    
  Lab Operations:
    - 10 lab starts/hour
    - 5 lab stops/hour
    - 3 lab resets/hour
    
  Flag Submission:
    - 20 submissions/hour
    - 5 consecutive failures trigger cooldown
    
  API Endpoints:
    - /api/labs: 10 req/min
    - /api/flags: 20 req/hour
    - /api/auth: 5 req/min
```

## 5. Monitoring & Response

### 5.1 Real-time Monitoring

```yaml
Monitoring Stack:
  Prometheus:
    - Container metrics
    - Network metrics
    - Application metrics
    
  Grafana:
    - Real-time dashboards
    - Alert visualization
    - Performance tracking
    
  Falco:
    - Runtime security monitoring
    - System call detection
    - Anomaly detection
    
  ELK Stack:
    - Log aggregation
    - Security events
    - Audit trail
```

### 5.2 Anomaly Detection

```python
# Anomaly detection rules
ANOMALY_RULES = {
    'container_escape_attempt': {
        'description': 'Potential container escape',
        'severity': 'critical',
        'indicators': [
            'unusual_syscalls',
            'capability_escalation',
            'namespace_access'
        ],
        'response': 'immediate_kill'
    },
    
    'resource_exhaustion': {
        'description': 'Resource exhaustion attempt',
        'severity': 'high',
        'indicators': [
            'cpu_sustained_high',
            'memory_sustained_high',
            'disk_fill_attempt'
        ],
        'response': 'throttle_and_alert'
    },
    
    'network_abuse': {
        'description': 'Network abuse detected',
        'severity': 'high',
        'indicators': [
            'port_scanning',
            'ddos_attempt',
            'c2_communication'
        ],
        'response': 'block_and_alert'
    }
}
```

### 5.3 Incident Response

```yaml
Incident Response Procedures:
  Level 1 (Low):
    - Log event
    - Continue monitoring
    - No immediate action
    
  Level 2 (Medium):
    - Alert operator
    - Increase monitoring
    - Prepare for escalation
    
  Level 3 (High):
    - Alert security team
    - Isolate affected lab
    - Begin investigation
    
  Level 4 (Critical):
    - Immediate isolation
    - Kill container
    - Forensic analysis
    - User notification
```

### 5.4 Forensics

```yaml
Forensic Capabilities:
  Evidence Collection:
    - Container snapshots
    - Network captures
    - System logs
    - User activity logs
    
  Analysis Tools:
    - Volatility (memory analysis)
    - Wireshark (network analysis)
    - Autopsy (disk analysis)
    
  Reporting:
    - Automated reports
    - Timeline analysis
    - IOC extraction
```

## 6. Compliance

### 6.1 Security Standards

```yaml
Compliance:
  Standards:
    - OWASP Top 10
    - NIST Cybersecurity Framework
    - CIS Benchmarks
    
  Auditing:
    - Regular security audits
    - Penetration testing
    - Vulnerability scanning
    
  Documentation:
    - Security policies
    - Incident response plans
    - User agreements
```

### 6.2 Data Protection

```yaml
Data Protection:
  Encryption:
    - At rest: AES-256
    - In transit: TLS 1.3
    - Key management: AWS KMS
    
  Privacy:
    - Data minimization
    - Purpose limitation
    - Storage limitation
    
  Retention:
    - Logs: 90 days
    - User data: Until deletion
    - Lab data: 30 days after completion
```

## 7. Security Checklist

### Pre-Deployment
- [ ] Security audit completed
- [ ] Vulnerability scan passed
- [ ] Penetration test completed
- [ ] Security policies documented
- [ ] Incident response plan tested

### Runtime
- [ ] Monitoring active
- [ ] Alerts configured
- [ ] Logs being collected
- [ ] Backups running
- [ ] Security updates applied

### Post-Incident
- [ ] Incident documented
- [ ] Root cause analyzed
- [ ] Remediation implemented
- [ ] Lessons learned documented
- [ ] Security controls updated

## 8. Contact

For security issues or vulnerabilities:
- Email: security@cyberlab.academy
- PGP Key: [Available on request]
- Bug Bounty: [Program details]

## Appendix

### A. Security Tools

| Tool | Purpose |
|------|---------|
| Trivy | Container vulnerability scanning |
| Falco | Runtime security monitoring |
| Vault | Secrets management |
| Istio | Service mesh security |
| OPA | Policy enforcement |

### B. Security Metrics

| Metric | Target |
|--------|--------|
| Mean Time to Detect | < 5 minutes |
| Mean Time to Respond | < 15 minutes |
| Container Escape Attempts | 0 |
| Successful Escapes | 0 |
| Resource Abuse Incidents | < 1% of labs |
