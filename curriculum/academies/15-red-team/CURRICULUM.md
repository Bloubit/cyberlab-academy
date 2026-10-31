# Academy 15: Red Team Operations

## Course Overview

**Duration:** 12 weeks (120 hours)
**Level:** Expert
**Prerequisites:** Academy 5, 6, 10, 14 completed
**Certification:** CyberLab Certified Expert (CCE) - Part 2

## Course Description

Advanced Red Team operations course covering adversary emulation, advanced persistent threats, evasion techniques, and purple teaming. Includes real-world attack scenarios and enterprise penetration testing.

## Learning Objectives

1. **Plan** Red Team operations
2. **Execute** advanced attacks
3. **Evade** security controls
4. **Maintain** persistent access
5. **Exfiltrate** data
6. **Document** operations
7. **Coordinate** with Blue Team
8. **Improve** organizational security

---

## Module 1: Red Team Methodology (Week 1)

### Lesson 1.1: Red Team Concept

**Theory:**
- Red Team vs Penetration Testing
- Adversary emulation
- MITRE ATT&CK framework
- Rules of engagement

**Red Team vs Pentest:**
| Aspect | Penetration Test | Red Team |
|--------|------------------|----------|
| Scope | Specific systems | Full environment |
| Duration | Weeks | Months |
| Knowledge | Known to IT | Covert |
| Goal | Find vulnerabilities | Simulate adversary |
| Team | Individual | Team-based |

### Lesson 1.2: MITRE ATT&CK Framework

**Theory:**
- Tactics, Techniques, Procedures (TTPs)
- ATT&CK Navigator
- Threat actor mapping
- Detection gaps

**ATT&CK Tactics:**
```
1. Initial Access
2. Execution
3. Persistence
4. Privilege Escalation
5. Defense Evasion
6. Credential Access
7. Discovery
8. Lateral Movement
9. Collection
10. Command and Control
11. Exfiltration
12. Impact
```

**Practical Lab 1.2.1: ATT&CK Mapping**
- Map attack techniques
- Identify detection gaps
- Plan attack paths

### Lesson 1.3: Operational Security

**Theory:**
- OPSEC principles
- Infrastructure security
- Communication security
- Evidence handling

**OPSEC Checklist:**
```
✓ Use dedicated infrastructure
✓ Encrypt communications
✓ Clean up artifacts
✓ Maintain plausible deniability
✓ Document everything
```

**Practical Lab 1.3.1: OPSEC Planning**
- Set up secure infrastructure
- Plan operational security
- Test OPSEC measures

---

## Module 2: Initial Access (Week 2-3)

### Lesson 2.1: Phishing Operations

**Theory:**
- Phishing infrastructure
- Email crafting
- Payload delivery
- Tracking and analytics

**Phishing Infrastructure:**
```
Attacker Infrastructure:
├── Domain (lookalike)
├── Email server
├── Web server (landing page)
├── Payload hosting
└── Tracking system
```

**Practical Lab 2.1.1: Phishing Campaign**
- Set up phishing infrastructure
- Create phishing emails
- Deliver payloads

### Lesson 2.2: Supply Chain Attacks

**Theory:**
- Software supply chain
- Hardware supply chain
- Dependency confusion
- Typosquatting

### Lesson 2.3: Physical Access

**Theory:**
- Physical security testing
- Social engineering
- USB drop attacks
- Wireless access

**Practical Lab 2.3.1: Physical Testing**
- Test physical security
- Conduct social engineering
- Assess wireless security

---

## Module 3: Execution and Persistence (Week 3-4)

### Lesson 3.1: Code Execution

**Theory:**
- Living off the land
- Signed binary proxy execution
- PowerShell attacks
- WMI execution

**LOLBins (Living Off the Land Binaries):**
```
- powershell.exe
- cmd.exe
- mshta.exe
- wscript.exe
- certutil.exe
- bitsadmin.exe
- regsvr32.exe
```

**Practical Lab 3.1.1: Code Execution**
- Use LOLBins
- Execute payloads
- Avoid detection

### Lesson 3.2: Persistence Mechanisms

**Theory:**
- Registry persistence
- Scheduled tasks
- Services
- WMI subscriptions
- DLL hijacking

**Persistence Methods:**
| Method | Stealth | Reliability |
|--------|---------|-------------|
| Registry Run Keys | Low | High |
| Scheduled Tasks | Medium | High |
| Services | Medium | High |
| WMI | High | Medium |
| DLL Hijacking | High | Medium |

**Practical Lab 3.2.1: Persistence Implementation**
- Implement persistence
- Test reliability
- Evade detection

### Lesson 3.3: Privilege Escalation

**Theory:**
- Windows escalation
- Linux escalation
- Token manipulation
- Credential theft

**Practical Lab 3.3.1: Privilege Escalation**
- Escalate privileges
- Maintain access
- Document methods

---

## Module 4: Defense Evasion (Week 5-6)

### Lesson 4.1: AV/EDR Evasion

**Theory:**
- Detection mechanisms
- Evasion techniques
- Custom tooling
- Payload obfuscation

**Evasion Techniques:**
```
1. Payload encryption
2. Process injection
3. Unhooking
4. Direct syscalls
5. AMSI bypass
6. ETW patching
```

**Practical Lab 4.1.1: AV Evasion**
- Bypass AV detection
- Evade EDR
- Test evasion techniques

### Lesson 4.2: Network Evasion

**Theory:**
- Protocol manipulation
- Encryption
- Tunneling
- Domain fronting

**Practical Lab 4.2.1: Network Evasion**
- Evade network monitoring
- Use encryption
- Implement tunneling

### Lesson 4.3: Log Evasion

**Theory:**
- Log clearing
- Log manipulation
- Anti-forensics
- Timestomping

**Practical Lab 4.3.1: Log Evasion**
- Clear logs
- Manipulate timestamps
- Evade forensics

---

## Module 5: Lateral Movement (Week 6-7)

### Lesson 5.1: Credential Access

**Theory:**
- Credential dumping
- Password spraying
- Kerberos attacks
- Token theft

**Credential Access Tools:**
- Mimikatz
- Rubeus
- SharpKatz
- LaZagne

**Practical Lab 5.1.1: Credential Access**
- Dump credentials
- Crack passwords
- Steal tokens

### Lesson 5.2: Lateral Movement Techniques

**Theory:**
- Pass-the-Hash
- Pass-the-Ticket
- Remote execution
- Pivot techniques

**Lateral Movement Methods:**
```
1. RDP
2. SMB/WMI
3. PSRemoting
4. DCOM
5. SSH
6. Scheduled Tasks
```

**Practical Lab 5.2.1: Lateral Movement**
- Move through network
- Compromise multiple systems
- Maintain access

---

## Module 6: Data Exfiltration (Week 8)

### Lesson 6.1: Collection

**Theory:**
- Data discovery
- Sensitive data identification
- Collection techniques
- Staging

### Lesson 6.2: Exfiltration Methods

**Theory:**
- DNS exfiltration
- HTTP/HTTPS exfiltration
- Steganography
- Physical exfiltration

**Exfiltration Methods:**
| Method | Stealth | Speed |
|--------|---------|-------|
| DNS | High | Slow |
| HTTPS | Medium | Fast |
| Steganography | High | Slow |
| Cloud storage | Medium | Fast |

**Practical Lab 6.2.1: Data Exfiltration**
- Exfiltrate data
- Avoid detection
- Test methods

---

## Module 7: Command and Control (Week 9)

### Lesson 7.1: C2 Frameworks

**Theory:**
- C2 architecture
- Framework comparison
- Infrastructure setup
- Profile management

**C2 Frameworks:**
- Cobalt Strike
- Mythic
- Sliver
- Brute Ratel

### Lesson 7.2: C2 Operations

**Theory:**
- Beacon configuration
- Communication profiles
- Pivoting
- Malleable C2

**Practical Lab 7.2.1: C2 Setup**
- Configure C2 infrastructure
- Set up beacons
- Test communications

---

## Module 8: Purple Teaming (Week 10)

### Lesson 8.1: Purple Team Concept

**Theory:**
- Red/Blue collaboration
- Exercise planning
- Metrics and measurement
- Improvement cycles

### Lesson 8.2: Exercise Execution

**Theory:**
- Tabletop exercises
- Simulated attacks
- Detection validation
- Gap analysis

**Purple Team Exercise:**
```
1. Plan → Define scope and goals
2. Execute → Run attack scenarios
3. Detect → Blue Team detection
4. Analyze → Review detection gaps
5. Improve → Update controls
6. Repeat → Continuous improvement
```

**Practical Lab 8.2.1: Purple Team Exercise**
- Plan exercise
- Execute attacks
- Validate detections

---

## Module 9: Reporting and Debrief (Week 11)

### Lesson 9.1: Red Team Reporting

**Theory:**
- Executive summary
- Technical findings
- TTP mapping
- Recommendations

**Report Structure:**
```
1. Executive Summary
2. Engagement Overview
3. Attack Narrative
4. Technical Findings
   - Initial Access
   - Lateral Movement
   - Data Exfiltration
5. TTP Mapping (ATT&CK)
6. Detection Gaps
7. Recommendations
8. Appendices
```

### Lesson 9.2: Debrief Sessions

**Theory:**
- Blue Team debrief
- Management briefing
- Lessons learned
- Improvement planning

**Practical Lab 9.2.1: Reporting**
- Create Red Team report
- Present findings
- Conduct debrief

---

## Module 10: Capstone Operations (Week 12)

### Lesson 10.1: Full Red Team Exercise

**Theory:**
- Multi-week engagement
- Real-world scenarios
- Team coordination
- Documentation

**Capstone Components:**
```
Week 1-2: Reconnaissance and Planning
Week 3-4: Initial Access
Week 5-6: Execution and Persistence
Week 7-8: Lateral Movement
Week 9-10: Data Exfiltration
Week 11: Reporting
Week 12: Debrief
```

**Practical Lab 10.1.1: Capstone Exercise**
- Execute full engagement
- Document operations
- Present results

---

## Final Exam

**Duration:** 2 weeks (full Red Team engagement)
**Format:** Comprehensive Red Team operation
**Passing Score:** 70%

### Exam Components
- Planning and preparation (15%)
- Initial access (20%)
- Execution and persistence (20%)
- Lateral movement (20%)
- Data exfiltration (15%)
- Reporting (10%)

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 25% |
| Assignments | 20% |
| Quizzes | 5% |
| Final Exam | 50% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
