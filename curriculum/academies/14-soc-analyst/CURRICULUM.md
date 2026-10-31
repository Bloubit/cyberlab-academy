# Academy 14: SOC Analyst

## Course Overview

**Duration:** 10 weeks (100 hours)
**Level:** Expert
**Prerequisites:** Academy 1-6, 12 completed
**Certification:** CyberLab Certified Expert (CCE) - Part 1

## Course Description

Comprehensive SOC Analyst course covering security monitoring, incident detection, threat hunting, and incident response. Includes hands-on experience with SIEM, SOAR, and security operations.

## Learning Objectives

1. **Monitor** security events
2. **Detect** security incidents
3. **Analyze** security alerts
4. **Respond** to incidents
5. **Hunt** for threats
6. **Document** findings
7. **Improve** detection rules
8. **Manage** security operations

---

## Module 1: SOC Fundamentals (Week 1)

### Lesson 1.1: SOC Structure

**Theory:**
- SOC types (internal, MSSP, virtual)
- SOC roles and responsibilities
- SOC maturity models
- SOC metrics

**SOC Tiers:**
```
Tier 1: Alert Monitoring
- Initial triage
- Alert validation
- Escalation decisions

Tier 2: Incident Analysis
- Deep investigation
- Threat analysis
- Containment actions

Tier 3: Advanced Analysis
- Threat hunting
- Malware analysis
- Forensics
```

### Lesson 1.2: SOC Processes

**Theory:**
- Alert handling workflow
- Escalation procedures
- Documentation standards
- Shift handover

**Alert Handling Flow:**
```
Alert → Triage → Validate → Investigate → Contain → Eradicate → Recover → Document
```

**Practical Lab 1.2.1: SOC Operations**
- Handle security alerts
- Follow SOC procedures
- Document incidents

---

## Module 2: SIEM Operations (Week 2-3)

### Lesson 2.1: SIEM Fundamentals

**Theory:**
- SIEM architecture
- Log sources
- Correlation rules
- Alert tuning

**SIEM Components:**
```
Data Sources → Collection → Normalization → Correlation → Alerting → Dashboard
     ↓              ↓            ↓              ↓            ↓          ↓
   Logs         Parsing      Standardize     Rules       Alerts    Visualization
```

### Lesson 2.2: Log Analysis

**Theory:**
- Log types
- Log parsing
- Pattern recognition
- Anomaly detection

**Log Sources:**
| Source | Purpose |
|--------|---------|
| Firewall | Network traffic |
| IDS/IPS | Intrusion detection |
| Endpoint | Host activity |
| Authentication | User activity |
| Application | App activity |
| DNS | Name resolution |
| Proxy | Web traffic |

**Practical Lab 2.2.1: Log Analysis**
- Analyze various log types
- Identify patterns
- Detect anomalies

### Lesson 2.3: Detection Rules

**Theory:**
- Rule creation
- Sigma rules
- YARA rules
- Custom detections

**Sigma Rule Example:**
```yaml
title: Suspicious Process Creation
status: experimental
description: Detects suspicious process creation
author: SOC Team
logsource:
    category: process_creation
    product: windows
detection:
    selection:
        CommandLine|contains:
            - 'mimikatz'
            - 'lazagne'
            - 'procdump'
    condition: selection
level: high
```

**Practical Lab 2.3.1: Rule Creation**
- Write detection rules
- Test rule effectiveness
- Tune false positives

---

## Module 3: Incident Detection (Week 3-4)

### Lesson 3.1: Detection Categories

**Theory:**
- Malware detection
- Network attacks
- Insider threats
- Data exfiltration

### Lesson 3.2: Alert Triage

**Theory:**
- Alert prioritization
- False positive handling
- Alert correlation
- Context enrichment

**Alert Priority Matrix:**
| Severity | Response Time | Examples |
|----------|---------------|----------|
| Critical | Immediate | Active breach, ransomware |
| High | < 1 hour | Malware detection, C2 |
| Medium | < 4 hours | Policy violation, suspicious activity |
| Low | < 24 hours | Informational, anomalies |

**Practical Lab 3.2.1: Alert Triage**
- Triage security alerts
- Prioritize incidents
- Handle false positives

### Lesson 3.3: Threat Intelligence Integration

**Theory:**
- TI feeds
- IOC matching
- Threat scoring
- Enrichment

**Practical Lab 3.3.1: TI Integration**
- Use threat intelligence
- Match IOCs
- Enrich alerts

---

## Module 4: Incident Response (Week 5-6)

### Lesson 4.1: IR Process

**Theory:**
- NIST IR lifecycle
- Preparation
- Detection and analysis
- Containment, eradication, recovery
- Post-incident activity

**IR Phases:**
```
1. Preparation
   - Playbooks
   - Runbooks
   - Communication plans

2. Detection & Analysis
   - Alert validation
   - Scope determination
   - Evidence collection

3. Containment
   - Short-term containment
   - Long-term containment
   - Evidence preservation

4. Eradication
   - Remove threat
   - Patch vulnerabilities
   - Reset credentials

5. Recovery
   - Restore systems
   - Verify integrity
   - Monitor for recurrence

6. Lessons Learned
   - Incident review
   - Process improvement
   - Documentation
```

### Lesson 4.2: Playbook Development

**Theory:**
- Playbook structure
- Decision trees
- Automation
- Testing

**Playbook Example:**
```markdown
# Ransomware Response Playbook

## Detection
- Alert type: Ransomware
- Indicators: File encryption, ransom notes

## Containment
1. Isolate affected systems
2. Disconnect from network
3. Preserve evidence

## Investigation
1. Identify entry point
2. Determine scope
3. Find IOCs

## Eradication
1. Remove malware
2. Patch vulnerabilities
3. Reset credentials

## Recovery
1. Restore from backup
2. Verify integrity
3. Monitor systems

## Post-Incident
1. Document findings
2. Update playbooks
3. Conduct review
```

**Practical Lab 4.2.1: Playbook Development**
- Create response playbooks
- Test playbooks
- Improve processes

### Lesson 4.3: Evidence Collection

**Theory:**
- Forensic acquisition
- Volatile data collection
- Chain of custody
- Evidence handling

**Evidence Collection Checklist:**
```
□ Memory dump
□ Disk image
□ Network captures
□ Log files
□ System information
□ User activity
□ Timeline data
```

**Practical Lab 4.3.1: Evidence Collection**
- Collect evidence properly
- Maintain chain of custody
- Document collection process

---

## Module 5: Threat Hunting (Week 7-8)

### Lesson 5.1: Threat Hunting Methodology

**Theory:**
- Hunting hypothesis
- Data collection
- Analysis techniques
- Findings documentation

**Hunting Process:**
```
1. Hypothesis → What are we looking for?
2. Data Collection → Gather relevant data
3. Analysis → Investigate data
4. Findings → Document results
5. Detection → Create detection rules
```

### Lesson 5.2: Hunting Techniques

**Theory:**
- IOC-based hunting
- TTP-based hunting
- Anomaly-based hunting
- Intelligence-driven hunting

**Hunting Queries Example:**
```splunk
# Suspicious PowerShell
index=windows sourcetype=WinEventLog:Security EventCode=4688
| regex NewProcessName=".*powershell.*"
| regex Process_Command_Line=".*-enc.*|.*downloadstring.*|.*bypass.*"

# Lateral Movement
index=windows sourcetype=WinEventLog:Security EventCode=4624 Logon_Type=3
| stats count by src_ip, dest_ip
| where count > 10
```

**Practical Lab 5.2.1: Threat Hunting**
- Develop hunting hypotheses
- Execute hunting queries
- Document findings

### Lesson 5.3: Hunting Tools

**Theory:**
- SIEM queries
- Endpoint detection
- Network analysis
- Custom tools

**Practical Lab 5.3.1: Tool Usage**
- Use hunting tools
- Develop custom queries
- Automate hunting

---

## Module 6: SOAR Operations (Week 8-9)

### Lesson 6.1: SOAR Fundamentals

**Theory:**
- SOAR concepts
- Automation benefits
- Integration points
- Use cases

### Lesson 6.2: Playbook Automation

**Theory:**
- Automated workflows
- Incident enrichment
- Response actions
- Metrics tracking

**SOAR Workflow Example:**
```
Alert Received → Enrich IP → Check TI → Check Reputation → 
Auto-Block if Malicious → Notify Analyst → Create Ticket
```

**Practical Lab 6.2.1: SOAR Implementation**
- Create automated playbooks
- Integrate with tools
- Test automation

---

## Module 7: SOC Metrics and Improvement (Week 9-10)

### Lesson 7.1: SOC Metrics

**Theory:**
- MTTD (Mean Time to Detect)
- MTTR (Mean Time to Respond)
- Alert volume
- False positive rate

**SOC Metrics Dashboard:**
```
Key Metrics:
- MTTD: < 1 hour target
- MTTR: < 4 hours target
- Alert closure rate
- False positive rate: < 20%
- Incident escalation rate
```

### Lesson 7.2: Continuous Improvement

**Theory:**
- Process improvement
- Rule tuning
- Training needs
- Tool optimization

**Practical Lab 7.2.1: SOC Improvement**
- Analyze SOC metrics
- Identify improvements
- Implement changes

---

## Final Exam

**Duration:** 100 hours (simulated SOC shift)
**Format:** SOC operations simulation
**Passing Score:** 70%

### Exam Components
- Alert handling (30%)
- Incident response (30%)
- Threat hunting (20%)
- Documentation (20%)

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
