# Academy 4: Python for Security

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Intermediate
**Prerequisites:** Academy 1-3 completed
**Certification:** CyberLab Certified Practitioner (CCP) - Part 1

## Course Description

This course teaches Python programming specifically for cybersecurity applications. Students will learn to write security tools, automate tasks, analyze data, and develop custom exploit code.

## Learning Objectives

1. **Write** Python scripts for security automation
2. **Develop** network scanning tools
3. **Create** vulnerability assessment scripts
4. **Analyze** log files and data
5. **Build** exploit code
6. **Implement** cryptographic functions
7. **Develop** web scraping tools
8. **Create** security utilities

---

## Module 1: Python Fundamentals (Week 1)

### Lesson 1.1: Python Basics

**Theory:**
- Python syntax and structure
- Variables and data types
- Operators
- Input/output

**Key Concepts:**
```python
# Variables
name = "CyberLab"
age = 25
is_active = True

# Lists
tools = ["nmap", "sqlmap", "hydra"]

# Dictionaries
scan_result = {"host": "192.168.1.1", "ports": [22, 80, 443]}
```

**Practical Lab 1.1.1: Python Basics**
- Write basic programs
- Work with variables
- Use data structures

### Lesson 1.2: Control Flow

**Theory:**
- If/else statements
- Loops (for, while)
- List comprehensions
- Error handling

**Practical Lab 1.2.1: Control Structures**
- Implement conditional logic
- Use loops effectively
- Handle exceptions

### Lesson 1.3: Functions and Modules

**Theory:**
- Function definition
- Parameters and return values
- Lambda functions
- Module imports

**Practical Lab 1.3.1: Function Development**
- Create reusable functions
- Build custom modules
- Use standard library

### Lesson 1.4: File Operations

**Theory:**
- Reading files
- Writing files
- Working with CSV
- JSON handling

**Practical Lab 1.4.1: File Processing**
- Parse log files
- Generate reports
- Process data files

---

## Module 2: Network Programming (Week 2)

### Lesson 2.1: Socket Programming

**Theory:**
- TCP sockets
- UDP sockets
- Client/server model
- Connection handling

**Socket Example:**
```python
import socket

# TCP Client
client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
client.connect(("192.168.1.1", 80))
client.send(b"GET / HTTP/1.1\r\nHost: target\r\n\r\n")
response = client.recv(1024)
print(response.decode())
```

**Practical Lab 2.1.1: Socket Programming**
- Create TCP client/server
- Implement UDP communication
- Handle multiple connections

### Lesson 2.2: Network Scanning

**Theory:**
- Port scanning techniques
- Service detection
- Banner grabbing
- OS fingerprinting

**Scanner Example:**
```python
import socket

def scan_port(host, port):
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(1)
        result = sock.connect_ex((host, port))
        sock.close()
        return result == 0
    except:
        return False

# Scan ports
for port in range(1, 1024):
    if scan_port("192.168.1.1", port):
        print(f"Port {port} is open")
```

**Practical Lab 2.2.1: Port Scanner**
- Build port scanner
- Add service detection
- Create scanning tools

### Lesson 2.3: Packet Manipulation

**Theory:**
- Scapy library
- Packet crafting
- Packet sniffing
- Network analysis

**Practical Lab 2.3.1: Packet Tools**
- Craft custom packets
- Sniff network traffic
- Analyze protocols

### Lesson 2.4: Network Automation

**Theory:**
- Paramiko (SSH automation)
- Netmiko
- NAPALM
- Automated configuration

**Practical Lab 2.4.1: Network Automation**
- Automate SSH tasks
- Configure devices
- Create network tools

---

## Module 3: Web Security Tools (Week 3)

### Lesson 3.1: Web Scraping

**Theory:**
- Requests library
- BeautifulSoup
- HTML parsing
- Data extraction

**Scraper Example:**
```python
import requests
from bs4 import BeautifulSoup

response = requests.get("http://target.com")
soup = BeautifulSoup(response.text, 'html.parser')

# Extract links
links = soup.find_all('a')
for link in links:
    print(link.get('href'))
```

**Practical Lab 3.1.1: Web Scraper**
- Build web scraper
- Extract data
- Create site mapper

### Lesson 3.2: HTTP Tools

**Theory:**
- HTTP requests
- Session handling
- Authentication
- Proxy support

**Practical Lab 3.2.1: HTTP Tools**
- Create HTTP client
- Handle sessions
- Implement authentication

### Lesson 3.3: Vulnerability Scanning

**Theory:**
- Vulnerability detection
- Pattern matching
- Payload generation
- Report generation

**Practical Lab 3.3.1: Vuln Scanner**
- Build vulnerability scanner
- Detect common flaws
- Generate reports

### Lesson 3.4: Exploit Development

**Theory:**
- Buffer overflow concepts
- Shellcode generation
- Exploit structure
- Payload encoding

**Practical Lab 3.4.1: Exploit Tools**
- Create exploit frameworks
- Generate payloads
- Develop exploits

---

## Module 4: Data Analysis (Week 4)

### Lesson 4.1: Log Analysis

**Theory:**
- Log parsing
- Pattern recognition
- Anomaly detection
- Report generation

**Practical Lab 4.1.1: Log Analyzer**
- Parse various log formats
- Detect suspicious activity
- Generate security reports

### Lesson 4.2: Data Visualization

**Theory:**
- Matplotlib
- Seaborn
- Chart types
- Dashboard creation

**Practical Lab 4.2.1: Security Dashboard**
- Create visualizations
- Build monitoring dashboard
- Display security metrics

### Lesson 4.3: Database Operations

**Theory:**
- SQLite
- MySQL/PostgreSQL
- SQL injection
- Database security

**Practical Lab 4.3.1: Database Tools**
- Create database tools
- Implement secure queries
- Build data processors

### Lesson 4.4: Cryptography

**Theory:**
- Hash functions
- Encryption/decryption
- Key generation
- Certificate handling

**Practical Lab 4.4.1: Crypto Tools**
- Implement cryptographic functions
- Create encryption tools
- Build certificate tools

---

## Module 5: Automation Frameworks (Week 5)

### Lesson 5.1: Task Automation

**Theory:**
- Scheduling tasks
- Batch processing
- Parallel execution
- Error handling

**Practical Lab 5.1.1: Automation Scripts**
- Create scheduled tasks
- Process batches
- Handle failures

### Lesson 5.2: API Development

**Theory:**
- Flask/FastAPI
- REST API design
- Authentication
- Documentation

**Practical Lab 5.2.1: Security API**
- Build security API
- Implement authentication
- Create documentation

### Lesson 5.3: Tool Integration

**Theory:**
- Wrapper scripts
- Tool orchestration
- Result aggregation
- Workflow automation

**Practical Lab 5.3.1: Tool Framework**
- Integrate security tools
- Create workflows
- Automate assessments

### Lesson 5.4: GUI Development

**Theory:**
- Tkinter
- PyQt
- GUI design
- Event handling

**Practical Lab 5.4.1: Security GUI**
- Create GUI applications
- Design user interfaces
- Implement functionality

---

## Module 6: Advanced Topics (Week 6)

### Lesson 6.1: Malware Analysis

**Theory:**
- PE file parsing
- String analysis
- API monitoring
- Behavior analysis

**Practical Lab 6.1.1: Malware Tools**
- Create analysis tools
- Parse executable files
- Detect malware indicators

### Lesson 6.2: Forensics Tools

**Theory:**
- File carving
- Metadata extraction
- Timeline analysis
- Evidence collection

**Practical Lab 6.2.1: Forensics Scripts**
- Build forensics tools
- Extract evidence
- Create timelines

### Lesson 6.3: Cloud Security

**Theory:**
- AWS/GCP/Azure APIs
- Cloud security assessment
- Configuration auditing
- Compliance checking

**Practical Lab 6.3.1: Cloud Tools**
- Create cloud scanners
- Audit configurations
- Check compliance

### Lesson 6.4: Project Development

**Theory:**
- Project planning
- Code organization
- Testing
- Documentation

**Practical Lab 6.4.1: Capstone Project**
- Develop security tool
- Document project
- Present results

---

## Assignments

### Assignment 1: Network Scanner
**Objective:** Build comprehensive port scanner
**Deliverables:**
- Working scanner
- Service detection
- Report generation
- Documentation

### Assignment 2: Log Analyzer
**Objective:** Create log analysis tool
**Deliverables:**
- Parser for multiple formats
- Anomaly detection
- Report generation
- Usage documentation

### Assignment 3: Security Tool
**Objective:** Develop original security tool
**Deliverables:**
- Working application
- Source code
- Documentation
- Presentation

---

## Quiz Questions

1. **Which library is used for HTTP requests in Python?**
   a) urllib
   b) requests
   c) http
   d) socket

2. **What is the purpose of try/except in Python?**
   a) Loop control
   b) Error handling
   c) File operations
   d) Function definition

3. **Which library is used for web scraping?**
   a) BeautifulSoup
   b) Pandas
   c) NumPy
   d) Matplotlib

4. **What is a lambda function?**
   a) Named function
   b) Anonymous function
   c) Class method
   d) Module function

5. **Which module is used for socket programming?**
   a) network
   b) socket
   c) connection
   d) tcp

---

## Final Exam

**Duration:** 4 hours
**Format:** Practical coding
**Passing Score:** 75%

### Exam Sections

**Section 1: Code Completion (30 points)**
- Complete security scripts
- Fix buggy code
- Optimize functions

**Section 2: Tool Development (40 points)**
- Build network scanner
- Create analysis tool
- Implement automation

**Section 3: Problem Solving (30 points)**
- Solve security challenges
- Write efficient code
- Document solutions

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 30% |
| Assignments | 30% |
| Quizzes | 15% |
| Final Exam | 25% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
