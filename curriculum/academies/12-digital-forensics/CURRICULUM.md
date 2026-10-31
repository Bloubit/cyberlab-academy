# Academy 12: Digital Forensics

## Course Overview

**Duration:** 8 weeks (80 hours)
**Level:** Professional
**Prerequisites:** Academy 3, 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 6

## Course Description

Comprehensive digital forensics course covering evidence collection, analysis, and presentation. Includes disk forensics, memory forensics, network forensics, and mobile forensics.

## Learning Objectives

1. **Collect** digital evidence properly
2. **Analyze** disk images
3. **Perform** memory forensics
4. **Investigate** network traffic
5. **Recover** deleted data
6. **Create** forensic reports
7. **Present** evidence in court
8. **Maintain** chain of custody

---

## Module 1: Forensics Fundamentals (Week 1)

### Lesson 1.1: Forensics Methodology

**Theory:**
- Forensics process
- Evidence handling
- Chain of custody
- Legal considerations

**Forensics Process:**
```
1. Identification → Find evidence
2. Preservation → Secure evidence
3. Collection → Gather evidence
4. Examination → Analyze evidence
5. Analysis → Interpret evidence
6. Reporting → Document findings
```

### Lesson 1.2: Evidence Types

**Theory:**
- Volatile vs non-volatile
- Digital evidence categories
- Evidence prioritization
- Collection order

**Evidence Volatility:**
```
Most Volatile → Least Volatile
1. CPU registers/cache
2. Routing table, ARP cache
3. Process table
4. Memory (RAM)
5. Temporary files
6. Disk/Storage
7. Remote logging data
8. Archival media
```

**Practical Lab 1.2.1: Evidence Collection**
- Identify evidence types
- Collect evidence properly
- Document chain of custody

---

## Module 2: Disk Forensics (Week 2-3)

### Lesson 2.1: Disk Imaging

**Theory:**
- Forensic imaging tools
- Write blockers
- Image formats
- Verification

**Imaging Tools:**
- FTK Imager
- dd/ddrescue
- Guymager
- EWF (Expert Witness Format)

**Practical Lab 2.1.1: Disk Imaging**
- Create forensic images
- Verify image integrity
- Document process

### Lesson 2.2: File System Analysis

**Theory:**
- FAT/NTFS
- ext4/APFS
- File metadata
- Deleted files

**File System Structures:**
```
NTFS Components:
- MFT (Master File Table)
- $LogFile
- $Volume
- $Bitmap
- Boot sector
```

**Practical Lab 2.2.1: File System Analysis**
- Analyze file systems
- Recover deleted files
- Extract metadata

### Lesson 2.3: Timeline Analysis

**Theory:**
- Timestamp analysis
- Event correlation
- Activity reconstruction
- Timeline tools

**Timeline Tools:**
- Plaso/log2timeline
- SIFT Workstation
- Autopsy
- AXIOM

**Practical Lab 2.3.1: Timeline Creation**
- Create timelines
- Correlate events
- Reconstruct activities

---

## Module 3: Memory Forensics (Week 4)

### Lesson 3.1: Memory Acquisition

**Theory:**
- Memory dump tools
- Acquisition methods
- Live vs dead analysis
- Memory formats

**Memory Tools:**
- WinPmem
- LiME
- FTK
- DumpIt

### Lesson 3.2: Memory Analysis

**Theory:**
- Volatility framework
- Process analysis
- Network connections
- Malware detection

**Volatility Commands:**
```bash
# Process list
volatility -f memory.dmp imageinfo
volatility -f memory.dmp --profile=Win10x64 pslist

# Network connections
volatility -f memory.dmp --profile=Win10x64 netscan

# DLLs
volatility -f memory.dmp --profile=Win10x64 dlllist

# Handles
volatility -f memory.dmp --profile=Win10x64 handles
```

**Practical Lab 3.2.1: Memory Analysis**
- Analyze memory dumps
- Extract processes
- Find malware artifacts

---

## Module 4: Network Forensics (Week 5)

### Lesson 4.1: Packet Capture Analysis

**Theory:**
- PCAP analysis
- Protocol analysis
- Session reconstruction
- Evidence extraction

**Practical Lab 4.1.1: Packet Analysis**
- Analyze PCAP files
- Reconstruct sessions
- Extract evidence

### Lesson 4.2: Network Log Analysis

**Theory:**
- Firewall logs
- Proxy logs
- IDS/IPS logs
- DNS logs

**Practical Lab 4.2.1: Log Analysis**
- Analyze network logs
- Identify suspicious activity
- Create timelines

---

## Module 5: Mobile Forensics (Week 6)

### Lesson 5.1: Mobile Acquisition

**Theory:**
- iOS forensics
- Android forensics
- Acquisition methods
- Tools and techniques

### Lesson 5.2: Mobile Analysis

**Theory:**
- App data analysis
- Chat recovery
- Location data
- Deleted data recovery

**Practical Lab 5.2.1: Mobile Forensics**
- Acquire mobile devices
- Analyze app data
- Recover deleted data

---

## Module 6: Specialized Forensics (Week 7)

### Lesson 6.1: Cloud Forensics

**Theory:**
- Cloud evidence
- API-based forensics
- Multi-tenant challenges
- Legal considerations

### Lesson 6.2: Browser Forensics

**Theory:**
- Browser artifacts
- History analysis
- Cookie analysis
- Download recovery

**Practical Lab 6.2.1: Browser Forensics**
- Extract browser data
- Analyze user activity
- Recover evidence

---

## Module 7: Reporting and Testimony (Week 8)

### Lesson 7.1: Forensic Reporting

**Theory:**
- Report structure
- Evidence documentation
- Technical writing
- Expert testimony

**Report Structure:**
```
1. Executive Summary
2. Methodology
3. Evidence List
4. Analysis Findings
5. Conclusions
6. Appendices
   - Chain of custody
   - Technical details
   - Screenshots
```

### Lesson 7.2: Courtroom Testimony

**Theory:**
- Expert witness role
- Testimony preparation
- Cross-examination
- Daubert standard

**Practical Lab 7.2.1: Report Writing**
- Create forensic report
- Prepare for testimony
- Present findings

---

## Final Exam

**Duration:** 72 hours
**Format:** Forensic investigation
**Passing Score:** 70%

### Exam Components
- Disk forensics (30%)
- Memory forensics (25%)
- Network forensics (20%)
- Reporting (25%)

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
