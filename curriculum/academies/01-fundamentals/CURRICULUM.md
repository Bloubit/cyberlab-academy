# Academy 1: Cybersecurity Fundamentals

## Course Overview

**Duration:** 4 weeks (40 hours)
**Level:** Beginner
**Prerequisites:** Basic computer literacy
**Certification:** CyberLab Certified Beginner (CCB) - Part 1

## Course Description

This foundational course introduces the core concepts of cybersecurity. Students will learn the CIA triad, common threat landscapes, basic security principles, and develop a security mindset. The course follows OSCP methodology with 70% hands-on labs.

## Learning Objectives

By the end of this course, students will be able to:

1. **Explain** the CIA triad and its application in real-world scenarios
2. **Identify** common cyber threats and attack vectors
3. **Apply** basic security principles to protect systems
4. **Recognize** social engineering tactics
5. **Describe** security controls and their purposes
6. **Demonstrate** security awareness in daily operations
7. **Document** security incidents properly
8. **Use** basic security tools (password managers, 2FA, encryption)

---

## Module 1: Introduction to Cybersecurity (Week 1)

### Lesson 1.1: What is Cybersecurity?

**Theory:**
- Definition and scope of cybersecurity
- History of cybersecurity (from Morris Worm to modern threats)
- Why cybersecurity matters
- The evolving threat landscape

**Key Concepts:**
- Confidentiality, Integrity, Availability (CIA Triad)
- Defense in Depth
- Least Privilege
- Need to Know

**Practical Lab 1.1.1: Security Mindset**
- Analyze real-world security incidents
- Identify what went wrong
- Propose solutions

### Lesson 1.2: Threat Landscape

**Theory:**
- Types of threat actors (script kiddies, hacktivists, APTs, insiders)
- Motivation behind attacks
- Attack vectors and surfaces
- Common vulnerabilities

**Threat Actor Categories:**
| Actor | Motivation | Skill Level | Resources |
|-------|------------|-------------|-----------|
| Script Kiddie | Fame | Low | Public tools |
| Hacktivist | Ideology | Medium | Shared resources |
| Cybercriminal | Financial | Medium-High | Organized groups |
| APT | State-sponsored | High | Government funding |
| Insider | Various | Variable | Access privileges |

**Practical Lab 1.2.1: Threat Actor Analysis**
- Research current threat actors
- Map their TTPs (Tactics, Techniques, Procedures)
- Create threat actor profiles

### Lesson 1.3: Security Principles

**Theory:**
- Defense in Depth
- Least Privilege
- Separation of Duties
- Security by Design
- Zero Trust Architecture

**Defense in Depth Layers:**
```
┌─────────────────────────────────────┐
│           Physical Security         │
├─────────────────────────────────────┤
│         Network Security            │
├─────────────────────────────────────┤
│         Host Security               │
├─────────────────────────────────────┤
│        Application Security         │
├─────────────────────────────────────┤
│          Data Security              │
├─────────────────────────────────────┤
│      Administrative Controls        │
└─────────────────────────────────────┘
```

**Practical Lab 1.3.1: Security Architecture Review**
- Analyze a sample network diagram
- Identify missing security layers
- Propose improvements

### Lesson 1.4: Security Controls

**Theory:**
- Preventive controls
- Detective controls
- Corrective controls
- Deterrent controls
- Compensating controls

**Control Types Matrix:**
| Type | Purpose | Examples |
|------|---------|----------|
| Preventive | Stop incidents | Firewalls, encryption |
| Detective | Identify incidents | IDS, logging |
| Corrective | Fix incidents | Backups, patches |
| Deterrent | Discourage attacks | Warning banners, cameras |
| Compensating | Alternative controls | MFA when passwords weak |

**Practical Lab 1.4.1: Control Implementation**
- Implement basic security controls
- Test control effectiveness
- Document control failures

---

## Module 2: Common Cyber Threats (Week 1-2)

### Lesson 2.1: Malware Types

**Theory:**
- Viruses and worms
- Trojans and backdoors
- Ransomware
- Spyware and adware
- Rootkits
- Fileless malware

**Malware Characteristics:**
| Type | Propagation | Impact | Detection |
|------|-------------|--------|-----------|
| Virus | File attachment | Data corruption | Antivirus |
| Worm | Network replication | Resource exhaustion | Network monitoring |
| Trojan | Social engineering | Backdoor access | Behavior analysis |
| Ransomware | Phishing/exploits | Data encryption | Backup verification |
| Rootkit | Privilege escalation | Persistent access | Integrity checking |

**Practical Lab 2.1.1: Malware Analysis Basics**
- Analyze malware samples (safely in sandbox)
- Identify indicators of compromise
- Document findings

### Lesson 2.2: Network Attacks

**Theory:**
- Man-in-the-Middle (MITM)
- Denial of Service (DoS/DDoS)
- DNS poisoning
- ARP spoofing
- Packet sniffing

**Practical Lab 2.2.1: Network Attack Detection**
- Set up network monitoring
- Detect simulated attacks
- Analyze network traffic

### Lesson 2.3: Social Engineering

**Theory:**
- Phishing (email, spear, whaling)
- Pretexting
- Baiting
- Quid pro quo
- Tailgating and piggybacking

**Phishing Indicators:**
```
⚠️ Red Flags:
• Urgency or threats
• Generic greetings
• Suspicious links
• Poor grammar/spelling
• Unexpected attachments
• Request for credentials
• Spoofed sender address
```

**Practical Lab 2.3.1: Phishing Detection**
- Analyze phishing emails
- Identify red flags
- Create phishing awareness materials

### Lesson 2.4: Web-Based Attacks

**Theory:**
- Cross-Site Scripting (XSS)
- SQL Injection
- Cross-Site Request Forgery (CSRF)
- Directory traversal
- Command injection

**Practical Lab 2.4.1: Web Attack Recognition**
- Identify web vulnerabilities
- Understand attack vectors
- Learn basic mitigations

---

## Module 3: Security Fundamentals (Week 2-3)

### Lesson 3.1: Authentication and Access Control

**Theory:**
- Authentication factors (something you know, have, are)
- Single Sign-On (SSO)
- Multi-Factor Authentication (MFA)
- Role-Based Access Control (RBAC)
- Access Control Lists (ACL)

**Authentication Factors:**
| Factor | Type | Examples |
|--------|------|----------|
| Knowledge | Something you know | Password, PIN |
| Possession | Something you have | Token, smart card |
| Inherence | Something you are | Biometrics |
| Location | Somewhere you are | GPS, IP address |
| Behavior | Something you do | Typing pattern |

**Practical Lab 3.1.1: MFA Implementation**
- Set up MFA on various platforms
- Test MFA bypass attempts
- Document MFA weaknesses

### Lesson 3.2: Cryptography Basics

**Theory:**
- Symmetric vs asymmetric encryption
- Hashing algorithms
- Digital signatures
- PKI (Public Key Infrastructure)
- TLS/SSL

**Encryption Comparison:**
| Type | Speed | Key Management | Use Case |
|------|-------|----------------|----------|
| Symmetric | Fast | Shared secret | Data encryption |
| Asymmetric | Slow | Public/private | Key exchange |
| Hashing | Fast | N/A | Integrity verification |

**Practical Lab 3.2.1: Cryptography Hands-On**
- Encrypt/decrypt data
- Verify file hashes
- Generate key pairs

### Lesson 3.3: Network Security Basics

**Theory:**
- Firewalls and their types
- Intrusion Detection/Prevention Systems (IDS/IPS)
- VPN (Virtual Private Network)
- Network segmentation
- DMZ (Demilitarized Zone)

**Network Security Architecture:**
```
Internet → Firewall → DMZ → Firewall → Internal Network
                ↓
            Web Servers
            Mail Servers
```

**Practical Lab 3.3.1: Firewall Configuration**
- Configure basic firewall rules
- Test rule effectiveness
- Analyze firewall logs

### Lesson 3.4: Endpoint Security

**Theory:**
- Antivirus/anti-malware
- Host-based firewall
- Endpoint Detection and Response (EDR)
- Patch management
- Device encryption

**Practical Lab 3.4.1: Endpoint Protection**
- Deploy endpoint security solutions
- Test detection capabilities
- Respond to simulated incidents

---

## Module 4: Security Operations (Week 3-4)

### Lesson 4.1: Security Monitoring

**Theory:**
- Log management
- Security Information and Event Management (SIEM)
- Alert triage
- Incident detection

**SIEM Architecture:**
```
Data Sources → Collection → Normalization → Correlation → Alerting → Dashboard
```

**Practical Lab 4.1.1: Log Analysis**
- Configure log collection
- Analyze security events
- Create basic alerts

### Lesson 4.2: Incident Response

**Theory:**
- Incident Response Lifecycle (NIST)
- Preparation
- Detection and Analysis
- Containment, Eradication, Recovery
- Post-Incident Activity

**Incident Response Phases:**
1. **Preparation** - Plans, teams, tools
2. **Detection** - Identify incidents
3. **Analysis** - Understand scope
4. **Containment** - Limit damage
5. **Eradication** - Remove threat
6. **Recovery** - Restore systems
7. **Lessons Learned** - Improve

**Practical Lab 4.2.1: Incident Response Simulation**
- Respond to simulated incident
- Document response actions
- Create incident report

### Lesson 4.3: Business Continuity

**Theory:**
- Business Impact Analysis (BIA)
- Recovery Point Objective (RPO)
- Recovery Time Objective (RTO)
- Disaster Recovery Planning
- Backup strategies

**Backup Types:**
| Type | Description | RPO | RTO |
|------|-------------|-----|-----|
| Full | Complete backup | Long | Long |
| Incremental | Changes since last backup | Short | Medium |
| Differential | Changes since last full | Medium | Medium |

**Practical Lab 4.3.1: Backup and Recovery**
- Implement backup strategy
- Test recovery procedures
- Document recovery time

### Lesson 4.4: Security Policies

**Theory:**
- Acceptable Use Policy (AUP)
- Password Policy
- Data Classification
- Security Awareness Training
- Compliance frameworks (NIST, ISO 27001, GDPR)

**Policy Framework:**
```
Policies → Standards → Procedures → Guidelines
   ↓           ↓            ↓             ↓
  Why         What          How           Should
```

**Practical Lab 4.4.1: Policy Development**
- Review existing policies
- Identify gaps
- Propose improvements

---

## Assignments

### Assignment 1: Threat Analysis Report
**Objective:** Analyze a real-world security incident
**Deliverables:**
- Incident timeline
- Attack vector analysis
- Impact assessment
- Lessons learned
- Recommendations

### Assignment 2: Security Assessment
**Objective:** Conduct basic security assessment
**Deliverables:**
- Asset inventory
- Threat identification
- Risk assessment
- Control recommendations
- Implementation plan

### Assignment 3: Incident Response Plan
**Objective:** Create incident response plan
**Deliverables:**
- IR policy document
- Communication plan
- Escalation procedures
- Recovery procedures
- Contact list

---

## Quiz Questions

### Quiz 1: Fundamentals

1. **What does CIA stand for in cybersecurity?**
   a) Central Intelligence Agency
   b) Confidentiality, Integrity, Availability
   c) Control, Identification, Authentication
   d) Cyber, Internet, Access

2. **Which of the following is a preventive security control?**
   a) IDS
   b) Firewall
   c) Log monitoring
   d) Antivirus scan

3. **What is the primary goal of social engineering?**
   a) Technical exploitation
   b) Physical intrusion
   c) Manipulating humans
   d) Network scanning

4. **What is Defense in Depth?**
   a) Using one strong security control
   b) Layering multiple security controls
   c) Hiding network infrastructure
   d) Encrypting all data

5. **Which authentication factor is "something you are"?**
   a) Password
   b) Smart card
   c) Fingerprint
   d) Token

### Quiz 2: Threats

1. **What type of malware encrypts files and demands payment?**
   a) Virus
   b) Worm
   c) Ransomware
   d) Trojan

2. **What is a Man-in-the-Middle attack?**
   a) Intercepting communications
   b) Overwhelming servers
   c) Guessing passwords
   d) Installing malware

3. **Which phishing targets high-profile individuals?**
   a) Spear phishing
   b) Whaling
   c) Vishing
   d) Smishing

4. **What is SQL Injection?**
   a) Inserting malicious SQL code
   b) Installing SQL server
   c) Querying databases
   d) Creating tables

5. **What is the purpose of a rootkit?**
   a) Encrypt data
   b) Steal passwords
   c) Maintain persistent access
   d) Crash systems

---

## Final Exam

**Duration:** 2 hours
**Format:** Multiple choice + Practical scenarios
**Passing Score:** 75%

### Exam Sections

**Section 1: Theory (40 points)**
- Security principles
- Threat types
- Security controls
- Risk management

**Section 2: Scenarios (30 points)**
- Analyze security incidents
- Recommend controls
- Create response plans

**Section 3: Practical (30 points)**
- Identify vulnerabilities
- Use security tools
- Document findings

### Sample Exam Questions

1. **Scenario:** A company's employee clicked a phishing link and installed malware. What should be the FIRST action?
   a) Notify management
   b) Isolate the affected system
   c) Run antivirus scan
   d) Change all passwords

2. **Scenario:** You discover a server logging failed login attempts but no one is investigating. What control is missing?
   a) Preventive control
   b) Detective control
   c) Corrective control
   d) Deterrent control

3. **Practical:** Given a network diagram, identify security weaknesses and propose solutions.

---

## Resources

### Required Reading
- NIST Cybersecurity Framework
- OWASP Top 10
- SANS Security Awareness

### Tools
- Password managers (Bitwarden, KeePass)
- 2FA apps (Google Authenticator, Authy)
- Encryption tools (VeraCrypt, GPG)

### Practice Platforms
- CyberLab Academy Labs
- TryHackMe (Basics)
- HackTheBox (Starting Point)

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 30% |
| Assignments | 25% |
| Quizzes | 20% |
| Final Exam | 25% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
