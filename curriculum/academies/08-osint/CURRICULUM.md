# Academy 8: OSINT (Open Source Intelligence)

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Advanced
**Prerequisites:** Academy 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 2

## Course Description

Comprehensive OSINT course covering intelligence gathering, social media analysis, domain reconnaissance, person of interest profiling, and dark web investigations. Includes real-world case studies and practical investigations.

## Learning Objectives

1. **Gather** intelligence from open sources
2. **Analyze** social media profiles
3. **Investigate** domains and infrastructure
4. **Profile** persons of interest
5. **Conduct** dark web investigations
6. **Verify** information accuracy
7. **Document** intelligence findings
8. **Create** intelligence reports

---

## Module 1: OSINT Fundamentals (Week 1)

### Lesson 1.1: OSINT Methodology

**Theory:**
- Intelligence cycle
- Collection methods
- Analysis techniques
- Reporting standards

**Intelligence Cycle:**
```
Planning → Collection → Processing → Analysis → Dissemination → Feedback
```

### Lesson 1.2: Search Engine Mastery

**Theory:**
- Google dorking
- Advanced search operators
- Search engine alternatives
- Cached content

**Google Dorking:**
```
site:target.com          # Search specific site
filetype:pdf             # Search specific file type
intitle:"index of"       # Directory listings
inurl:admin              # Admin pages
cache:target.com         # Cached versions
related:target.com       # Similar sites
```

**Practical Lab 1.2.1: Search Techniques**
- Practice advanced searches
- Find hidden information
- Document findings

### Lesson 1.3: OSINT Tools

**Theory:**
- Maltego
- Recon-ng
- SpiderFoot
- theHarvester

**Practical Lab 1.3.1: Tool Proficiency**
- Use OSINT tools
- Automate collection
- Analyze results

---

## Module 2: Person Investigation (Week 2)

### Lesson 2.1: Social Media Analysis

**Theory:**
- Platform-specific techniques
- Profile enumeration
- Relationship mapping
- Timeline analysis

**Social Media Platforms:**
| Platform | Data Points | Tools |
|----------|-------------|-------|
| LinkedIn | Professional info | LinkedIn Scrapers |
| Facebook | Personal info | Social Searcher |
| Twitter | Activities | TweetDeck |
| Instagram | Photos/locations | InstaLoader |
| GitHub | Code/activities | GitHub Search |

**Practical Lab 2.1.1: Social Media Investigation**
- Research target profiles
- Map relationships
- Create timeline

### Lesson 2.2: Email Investigation

**Theory:**
- Email enumeration
- Breach checking
- Header analysis
- Verification techniques

**Tools:**
- HaveIBeenPwned
- Hunter.io
- EmailHeaderAnalyzer
- theHarvester

**Practical Lab 2.2.1: Email Investigation**
- Enumerate email addresses
- Check for breaches
- Analyze email headers

### Lesson 2.3: Phone Number Investigation

**Theory:**
- Number validation
- Carrier lookup
- Location tracking
- Associated accounts

**Practical Lab 2.3.1: Phone Investigation**
- Research phone numbers
- Identify owners
- Find associated accounts

---

## Module 3: Domain Investigation (Week 3)

### Lesson 3.1: Domain Intelligence

**Theory:**
- WHOIS analysis
- DNS enumeration
- Subdomain discovery
- Infrastructure mapping

**DNS Enumeration:**
```bash
# Record types
dig target.com A
dig target.com MX
dig target.com NS
dig target.com TXT

# Subdomain enumeration
amass enum -d target.com
subfinder -d target.com
```

**Practical Lab 3.1.1: Domain Investigation**
- Perform WHOIS lookup
- Enumerate subdomains
- Map infrastructure

### Lesson 3.2: Website Analysis

**Theory:**
- Technology stack detection
- CMS identification
- Framework detection
- Version fingerprinting

**Tools:**
- Wappalyzer
- BuiltWith
- WhatWeb
- Netcraft

**Practical Lab 3.2.1: Website Analysis**
- Identify technologies
- Detect CMS
- Find vulnerabilities

### Lesson 3.3: Certificate Intelligence

**Theory:**
- Certificate Transparency
- SSL/TLS analysis
- Subdomain discovery
- Infrastructure mapping

**Practical Lab 3.3.1: Certificate Investigation**
- Search certificate logs
- Discover subdomains
- Map infrastructure

---

## Module 4: Infrastructure Investigation (Week 4)

### Lesson 4.1: IP Address Intelligence

**Theory:**
- Geolocation
- ASN lookup
- Reverse DNS
- Port scanning

**Tools:**
- Shodan
- Censys
- IPinfo
- BGPView

**Practical Lab 4.1.1: IP Investigation**
- Research IP addresses
- Identify hosting providers
- Find associated services

### Lesson 4.2: Network Intelligence

**Theory:**
- BGP analysis
- Network topology
- Peering relationships
- Infrastructure overlap

**Practical Lab 4.2.1: Network Analysis**
- Map network infrastructure
- Identify relationships
- Find shared infrastructure

### Lesson 4.3: Cloud Intelligence

**Theory:**
- Cloud provider identification
- Storage bucket discovery
- Serverless analysis
- Cloud misconfigurations

**Practical Lab 4.3.1: Cloud Investigation**
- Find cloud resources
- Identify misconfigurations
- Map cloud infrastructure

---

## Module 5: Dark Web Intelligence (Week 5)

### Lesson 5.1: Dark Web Fundamentals

**Theory:**
- Tor network
- Onion services
- Dark web markets
- Intelligence gathering

**Practical Lab 5.1.1: Dark Web Research**
- Navigate Tor network
- Research onion services
- Gather intelligence

### Lesson 5.2: Dark Web Monitoring

**Theory:**
- Threat intelligence
- Data breach monitoring
- Credential exposure
- Brand monitoring

**Practical Lab 5.2.1: Dark Web Monitoring**
- Monitor dark web
- Find leaked data
- Report findings

---

## Module 6: Analysis and Reporting (Week 6)

### Lesson 6.1: Intelligence Analysis

**Theory:**
- Data correlation
- Pattern recognition
- Link analysis
- Timeline analysis

### Lesson 6.2: Report Writing

**Theory:**
- Intelligence reports
- Executive summaries
- Visual representations
- Confidence levels

**Report Structure:**
```
1. Executive Summary
2. Intelligence Requirements
3. Collection Methods
4. Findings
   - Person Intelligence
   - Network Intelligence
   - Infrastructure Intelligence
5. Analysis
6. Recommendations
7. Appendices
```

**Practical Lab 6.2.1: Report Creation**
- Create intelligence report
- Visualize findings
- Present results

---

## Final Exam

**Duration:** 48 hours
**Format:** OSINT investigation
**Passing Score:** 70%

### Exam Components
- Person investigation (30%)
- Domain investigation (30%)
- Infrastructure investigation (20%)
- Report writing (20%)

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
