# Academy 10: Active Directory Security

## Course Overview

**Duration:** 8 weeks (80 hours)
**Level:** Professional
**Prerequisites:** Academy 5, 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 4

## Course Description

Comprehensive Active Directory security course covering AD attacks, defense mechanisms, security hardening, and incident response. Includes hands-on labs with realistic AD environments.

## Learning Objectives

1. **Enumerate** Active Directory environments
2. **Exploit** AD vulnerabilities
3. **Perform** Kerberos attacks
4. **Implement** AD security controls
5. **Detect** AD attacks
6. **Harden** AD infrastructure
7. **Respond** to AD incidents
8. **Audit** AD security

---

## Module 1: AD Fundamentals (Week 1)

### Lesson 1.1: Active Directory Architecture

**Theory:**
- Domain structure
- Organizational Units
- Group Policy
- Trust relationships

**AD Structure:**
```
Forest
├── Domain 1
│   ├── OU 1
│   │   ├── Users
│   │   ├── Computers
│   │   └── Groups
│   └── OU 2
└── Domain 2
    └── Child Domain
```

### Lesson 1.2: AD Protocols

**Theory:**
- LDAP
- Kerberos
- NTLM
- DNS

**Practical Lab 1.2.1: AD Enumeration**
- Enumerate domain objects
- Map AD structure
- Identify trusts

---

## Module 2: AD Enumeration (Week 2)

### Lesson 2.1: Enumeration Techniques

**Theory:**
- User enumeration
- Group enumeration
- Computer enumeration
- Policy enumeration

**Enumeration Tools:**
- BloodHound
- ADRecon
- PowerView
- ldapsearch

**Practical Lab 2.1.1: AD Enumeration**
- Enumerate users
- Map group memberships
- Find privileged accounts

### Lesson 2.2: BloodHound Analysis

**Theory:**
- Graph analysis
- Attack path identification
- Shortest path finding
- Data collection

**Practical Lab 2.2.1: BloodHound Analysis**
- Collect AD data
- Analyze attack paths
- Identify vulnerabilities

---

## Module 3: Credential Attacks (Week 3)

### Lesson 3.1: Password Attacks

**Theory:**
- Password spraying
- Credential stuffing
- Offline cracking
- Password policies

### Lesson 3.2: Kerberos Attacks

**Theory:**
- Kerberoasting
- AS-REP roasting
- Golden ticket
- Silver ticket
- Pass-the-Ticket

**Kerberos Attack Flow:**
```
1. Kerberoast: Request service tickets → Crack offline
2. AS-REP: Find accounts without preauth → Crack
3. Golden Ticket: Forge TGT → Domain persistence
4. Silver Ticket: Forge TGS → Service access
```

**Practical Lab 3.2.1: Kerberos Attacks**
- Perform Kerberoasting
- Execute AS-REP roasting
- Forge tickets

### Lesson 3.3: NTLM Attacks

**Theory:**
- Pass-the-Hash
- Pass-the-NTLM
- NTLM relay
- Responder attacks

**Practical Lab 3.3.1: NTLM Attacks**
- Capture NTLM hashes
- Relay NTLM authentication
- Bypass NTLM restrictions

---

## Module 4: Privilege Escalation (Week 4-5)

### Lesson 4.1: Local Privilege Escalation

**Theory:**
- Service misconfigurations
- Unquoted service paths
- DLL hijacking
- Token manipulation

### Lesson 4.2: Domain Privilege Escalation

**Theory:**
- Group membership abuse
- delegation attacks
- ACL abuse
- DC synchronization

**Privilege Escalation Paths:**
```
User → Local Admin → Domain Admin → Enterprise Admin
      ↓
   Service Account → Domain Admin
      ↓
   Delegation Abuse → Domain Admin
```

**Practical Lab 4.2.1: Privilege Escalation**
- Escalate to local admin
- Compromise domain admin
- Achieve domain dominance

---

## Module 5: Persistence (Week 5-6)

### Lesson 5.1: AD Persistence Techniques

**Theory:**
- Golden ticket
- Silver ticket
- DCsync
- Skeleton key
- AdminSDHolder

**Practical Lab 5.1.1: Persistence Implementation**
- Create golden tickets
- Implement DCsync
- Establish persistence

---

## Module 6: Detection and Defense (Week 6-7)

### Lesson 6.1: AD Monitoring

**Theory:**
- Event log monitoring
- SIEM integration
- Alert rules
- Anomaly detection

### Lesson 6.2: AD Hardening

**Theory:**
- Password policies
- Group policy hardening
- Privileged access management
- Tiered administration

**AD Hardening Checklist:**
```
✓ Implement LAPS
✓ Enforce strong passwords
✓ Disable NTLM where possible
✓ Enable Kerberos AES
✓ Monitor privileged accounts
✓ Implement MFA
✓ Restrict delegation
✓ Audit GPOs
```

**Practical Lab 6.2.1: AD Hardening**
- Implement security controls
- Configure monitoring
- Test defenses

---

## Module 7: Incident Response (Week 7-8)

### Lesson 7.1: AD Incident Response

**Theory:**
- Compromise detection
- Evidence collection
- Remediation
- Recovery

### Lesson 7.2: AD Forensics

**Theory:**
- Log analysis
- Artifact collection
- Timeline creation
- Attribution

**Practical Lab 7.2.1: AD Incident Response**
- Detect compromises
- Collect evidence
- Remediate incidents

---

## Module 8: Advanced Topics (Week 8)

### Lesson 8.1: Azure AD

**Theory:**
- Azure AD architecture
- Hybrid identity
- Cloud attacks
- Defense mechanisms

### Lesson 8.2: AD Security Assessment

**Theory:**
- Assessment methodologies
- Testing tools
- Reporting
- Remediation guidance

**Practical Lab 8.2.1: AD Assessment**
- Assess AD security
- Identify vulnerabilities
- Create remediation plan

---

## Final Exam

**Duration:** 72 hours
**Format:** AD penetration test
**Passing Score:** 70%

### Exam Components
- AD enumeration (20%)
- Credential attacks (25%)
- Privilege escalation (25%)
- Persistence (15%)
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
