# Academy 13: Cloud Security

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Professional
**Prerequisites:** Academy 2, 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 7

## Course Description

Comprehensive cloud security course covering AWS, Azure, and GCP security. Includes cloud architecture, identity management, data protection, and cloud penetration testing.

## Learning Objectives

1. **Design** secure cloud architectures
2. **Implement** cloud security controls
3. **Assess** cloud environments
4. **Identify** cloud misconfigurations
5. **Protect** cloud data
6. **Monitor** cloud activities
7. **Respond** to cloud incidents
8. **Comply** with cloud regulations

---

## Module 1: Cloud Fundamentals (Week 1)

### Lesson 1.1: Cloud Service Models

**Theory:**
- IaaS (Infrastructure as a Service)
- PaaS (Platform as a Service)
- SaaS (Software as a Service)
- Shared responsibility model

**Cloud Service Models:**
```
SaaS: Applications (Office 365, Salesforce)
PaaS: Platform (Azure App Service, Heroku)
IaaS: Infrastructure (EC2, Azure VMs)
On-Premises: Full control
```

**Shared Responsibility:**
| Component | On-Prem | IaaS | PaaS | SaaS |
|-----------|---------|------|------|------|
| Data | Customer | Customer | Customer | Customer |
| Applications | Customer | Customer | Customer | Provider |
| Runtime | Customer | Customer | Provider | Provider |
| Middleware | Customer | Customer | Provider | Provider |
| OS | Customer | Provider | Provider | Provider |
| Virtualization | Customer | Provider | Provider | Provider |
| Servers | Customer | Provider | Provider | Provider |
| Storage | Customer | Provider | Provider | Provider |
| Networking | Customer | Provider | Provider | Provider |

### Lesson 1.2: Cloud Providers

**Theory:**
- AWS fundamentals
- Azure fundamentals
- GCP fundamentals
- Multi-cloud considerations

**Practical Lab 1.2.1: Cloud Setup**
- Set up cloud accounts
- Configure basic services
- Implement security basics

---

## Module 2: AWS Security (Week 2)

### Lesson 2.1: IAM Security

**Theory:**
- IAM users and roles
- Policies
- Best practices
- Common misconfigurations

**IAM Best Practices:**
```
✓ Use least privilege principle
✓ Enable MFA
✓ Rotate access keys
✓ Use IAM roles
✓ Monitor IAM activity
✓ Use policy conditions
```

### Lesson 2.2: EC2 Security

**Theory:**
- Security groups
- NACLs
- Instance hardening
- Key management

**Practical Lab 2.2.1: EC2 Security**
- Configure security groups
- Harden instances
- Implement encryption

### Lesson 2.3: S3 Security

**Theory:**
- Bucket policies
- Access control
- Encryption
- Logging

**S3 Security Checklist:**
```
✓ Block public access
✓ Enable encryption
✓ Enable versioning
✓ Enable logging
✓ Use bucket policies
✓ Implement MFA delete
```

**Practical Lab 2.3.1: S3 Security**
- Secure S3 buckets
- Implement encryption
- Configure access logging

### Lesson 2.4: Network Security

**Theory:**
- VPC design
- Subnets and routing
- Security groups
- NACLs

**Practical Lab 2.4.1: VPC Security**
- Design secure VPC
- Configure security groups
- Implement network segmentation

---

## Module 3: Azure Security (Week 3)

### Lesson 3.1: Azure AD

**Theory:**
- Azure AD concepts
- Conditional access
- MFA implementation
- Privileged identity management

### Lesson 3.2: Azure Network Security

**Theory:**
- Virtual networks
- NSGs
- Azure Firewall
- VPN Gateway

### Lesson 3.3: Azure Data Protection

**Theory:**
- Encryption at rest
- Encryption in transit
- Key management
- Backup and recovery

**Practical Lab 3.3.1: Azure Security**
- Configure Azure AD
- Implement network security
- Protect data

---

## Module 4: GCP Security (Week 4)

### Lesson 4.1: GCP IAM

**Theory:**
- IAM policies
- Service accounts
- Organization policies
- Audit logging

### Lesson 4.2: GCP Network Security

**Theory:**
- VPC design
- Firewall rules
- Cloud Armor
- Load balancing

### Lesson 4: GCP Data Protection

**Theory:**
- Encryption
- Key management
- DLP (Data Loss Prevention)
- Backup strategies

**Practical Lab 4.3.1: GCP Security**
- Configure IAM
- Implement network security
- Protect data

---

## Module 5: Cloud Penetration Testing (Week 5)

### Lesson 5.1: Cloud Reconnaissance

**Theory:**
- Cloud enumeration
- Service discovery
- Misconfiguration detection
- Tool usage

**Cloud Recon Tools:**
- ScoutSuite
- Prowler
- CloudSploit
- Pacu

### Lesson 5.2: Cloud Exploitation

**Theory:**
- IAM privilege escalation
- Metadata service attacks
- Container escapes
- Serverless attacks

**Practical Lab 5.2.1: Cloud Penetration**
- Enumerate cloud environment
- Identify misconfigurations
- Exploit vulnerabilities

---

## Module 6: Cloud Compliance (Week 6)

### Lesson 6.1: Compliance Frameworks

**Theory:**
- Cloud security standards
- Compliance requirements
- Audit procedures
- Documentation

### Lesson 6.2: Cloud Monitoring

**Theory:**
- CloudTrail/Activity Log
- SIEM integration
- Alert configuration
- Incident response

**Practical Lab 6.2.1: Cloud Monitoring**
- Configure monitoring
- Implement alerting
- Respond to incidents

---

## Final Exam

**Duration:** 48 hours
**Format:** Cloud security assessment
**Passing Score:** 70%

### Exam Components
- Cloud architecture (30%)
- Security configuration (30%)
- Penetration testing (25%)
- Reporting (15%)

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 30% |
| Assignments | 25% |
| Quizzes | 10% |
| Final Exam | 35% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
