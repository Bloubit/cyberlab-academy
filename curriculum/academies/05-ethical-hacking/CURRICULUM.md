# Academy 5: Ethical Hacking

## Course Overview

**Duration:** 8 weeks (80 hours)
**Level:** Intermediate
**Prerequisites:** Academy 1-4 completed
**Certification:** CyberLab Certified Practitioner (CCP) - Part 2

## Course Description

This comprehensive ethical hacking course teaches penetration testing methodologies, vulnerability assessment, and exploitation techniques following industry standards (OSCP methodology).

## Learning Objectives

1. **Perform** reconnaissance and enumeration
2. **Identify** vulnerabilities in systems
3. **Exploit** common security flaws
4. **Document** findings professionally
5. **Apply** penetration testing methodologies
6. **Use** industry-standard tools
7. **Bypass** security controls
8. **Report** findings effectively

---

## Module 1: Methodology (Week 1)

### Lesson 1.1: Penetration Testing Phases

**Theory:**
- Pre-engagement
- Reconnaissance
- Scanning
- Gaining Access
- Maintaining Access
- Covering Tracks
- Reporting

**PTES (Penetration Testing Execution Standard):**
```
1. Pre-engagement    → Rules of engagement
2. Intelligence      → Passive reconnaissance
3. Threat Modeling   → Identify attack vectors
4. Vulnerability     → Active scanning
5. Exploitation      → Gain access
6. Post-Exploitation → Privilege escalation, lateral movement
7. Reporting         → Documentation
```

**Practical Lab 1.1.1: Methodology Application**
- Follow PTES methodology
- Document each phase
- Create test plan

### Lesson 1.2: Legal and Ethical Considerations

**Theory:**
- Authorization and scope
- Rules of engagement
- Legal implications
- Professional ethics

**Practical Lab 1.2.1: Documentation**
- Create authorization documents
- Define scope boundaries
- Document rules of engagement

---

## Module 2: Reconnaissance (Week 2)

### Lesson 2.1: Passive Reconnaissance

**Theory:**
- OSINT techniques
- Social media analysis
- DNS enumeration
- WHOIS lookups

**OSINT Tools:**
- Maltego
- theHarvester
- Recon-ng
- Shodan

**Practical Lab 2.1.1: Passive Recon**
- Gather target information
- Build target profile
- Document findings

### Lesson 2.2: Active Reconnaissance

**Theory:**
- Network scanning
- Port scanning
- Service enumeration
- OS fingerprinting

**Tools:**
- Nmap
- Masscan
- Unicornscan

**Practical Lab 2.2.1: Active Scanning**
- Perform network scans
- Enumerate services
- Identify OS versions

### Lesson 2.3: Enumeration

**Theory:**
- SMB enumeration
- SNMP enumeration
- LDAP enumeration
- Web enumeration

**Practical Lab 2.3.1: Service Enumeration**
- Enumerate network services
- Extract information
- Identify attack vectors

---

## Module 3: Vulnerability Assessment (Week 3)

### Lesson 3.1: Vulnerability Scanning

**Theory:**
- Vulnerability scanners
- False positives/negatives
- Risk assessment
- Prioritization

**Tools:**
- Nessus
- OpenVAS
- Nikto
- Nuclei

**Practical Lab 3.1.1: Vulnerability Scanning**
- Scan target systems
- Analyze results
- Validate findings

### Lesson 3.2: Web Vulnerabilities

**Theory:**
- OWASP Top 10
- SQL Injection
- XSS
- CSRF
- File inclusion

**Practical Lab 3.2.1: Web Testing**
- Test for web vulnerabilities
- Exploit web flaws
- Document findings

---

## Module 4: Exploitation (Week 4-5)

### Lesson 4.1: Network Exploitation

**Theory:**
- Metasploit Framework
- Manual exploitation
- Exploit databases
- Payload generation

**Practical Lab 4.1.1: Network Exploits**
- Use Metasploit
- Manual exploitation
- Post-exploitation

### Lesson 4.2: Web Application Exploitation

**Theory:**
- SQL Injection exploitation
- XSS exploitation
- Command injection
- File upload vulnerabilities

**Practical Lab 4.2.1: Web Exploitation**
- Exploit SQL injection
- Perform XSS attacks
- Upload malicious files

### Lesson 4.3: Password Attacks

**Theory:**
- Brute force attacks
- Dictionary attacks
- Rainbow tables
- Password spraying

**Tools:**
- Hydra
- John the Ripper
- Hashcat
- Medusa

**Practical Lab 4.3.1: Password Attacks**
- Crack password hashes
- Perform brute force
- Use password spraying

---

## Module 5: Post-Exploitation (Week 5-6)

### Lesson 5.1: Privilege Escalation

**Theory:**
- Linux privilege escalation
- Windows privilege escalation
- Misconfigurations
- Kernel exploits

**PrivEsc Checklist:**
- SUID binaries
- Sudo misconfigurations
- Kernel vulnerabilities
- Scheduled tasks
- Unquoted service paths

**Practical Lab 5.1.1: Privilege Escalation**
- Escalate on Linux
- Escalate on Windows
- Document methods

### Lesson 5.2: Lateral Movement

**Theory:**
- Network pivoting
- Pass-the-hash
- Credential reuse
- Remote execution

**Practical Lab 5.2.1: Lateral Movement**
- Pivot through network
- Move between systems
- Escalate access

### Lesson 5.3: Maintaining Access

**Theory:**
- Backdoors
- Persistence mechanisms
- Rootkits
- C2 frameworks

**Practical Lab 5.3.1: Persistence**
- Create backdoors
- Establish persistence
- Evade detection

---

## Module 6: Social Engineering (Week 6)

### Lesson 6.1: Social Engineering Attacks

**Theory:**
- Phishing campaigns
- Pretexting
- Physical security
- USB attacks

**Practical Lab 6.1.1: Social Engineering**
- Create phishing campaign
- Test physical security
- Document results

---

## Module 7: Reporting (Week 7)

### Lesson 7.1: Report Writing

**Theory:**
- Executive summary
- Technical findings
- Risk ratings
- Remediation guidance

**Report Structure:**
```
1. Executive Summary
2. Scope and Methodology
3. Findings Summary
4. Detailed Findings
   - Vulnerability description
   - Risk rating
   - Evidence
   - Remediation
5. Appendices
```

**Practical Lab 7.1.1: Report Creation**
- Write executive summary
- Document technical findings
- Create remediation plan

---

## Module 8: Advanced Topics (Week 8)

### Lesson 8.1: Active Directory Attacks

**Theory:**
- Kerberoasting
- AS-REP roasting
- Pass-the-Hash
- DCSync

**Practical Lab 8.1.1: AD Attacks**
- Attack AD environment
- Extract credentials
- Compromise domain

### Lesson 8.2: Wireless Attacks

**Theory:**
- WiFi cracking
- Evil twin attacks
- WPA/WPA2 attacks
- Bluetooth attacks

**Practical Lab 8.2.1: Wireless Testing**
- Crack WiFi passwords
- Perform evil twin
- Test wireless security

### Lesson 8.3: Cloud Penetration Testing

**Theory:**
- AWS/GCP/Azure security
- Cloud misconfigurations
- Container security
- Serverless attacks

**Practical Lab 8.3.1: Cloud Testing**
- Assess cloud security
- Identify misconfigurations
- Document findings

---

## Labs

### Lab 1: Vulnerable Machine
- Multi-step exploitation
- Privilege escalation
- Evidence collection

### Lab 2: Web Application
- OWASP vulnerabilities
- Business logic flaws
- API testing

### Lab 3: Active Directory
- Domain enumeration
- Credential attacks
- Domain compromise

### Lab 4: Comprehensive Pentest
- Full methodology application
- Real-world scenario
- Professional report

---

## Quiz Questions

1. **What is the first phase of penetration testing?**
   a) Scanning
   b) Reconnaissance
   c) Exploitation
   d) Reporting

2. **What does OSINT stand for?**
   a) Open Source Intelligence
   b) Official Security Intelligence
   c) Online Security Information
   d) Organized Security Intelligence

3. **Which tool is used for port scanning?**
   a) Metasploit
   b) Nmap
   c) John
   d) Hydra

4. **What is privilege escalation?**
   a) Gaining root access
   b) Moving laterally
   c) Covering tracks
   d) Reporting findings

5. **What is the purpose of a pivot?**
   a) Privilege escalation
   b) Network traversal
   c) Password cracking
   d) Vulnerability scanning

---

## Final Exam

**Duration:** 24 hours (OSCP-style)
**Format:** Practical penetration test
**Passing Score:** 70% (root/admin on sufficient machines)

### Exam Format
- Multiple vulnerable machines
- Limited time
- NoMetasploit/Unlimited Metasploit options
- Report required

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 35% |
| Assignments | 25% |
| Quizzes | 10% |
| Final Exam | 30% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
