# Academy 7: Web Application Security

## Course Overview

**Duration:** 8 weeks (80 hours)
**Level:** Advanced
**Prerequisites:** Academy 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 1

## Course Description

Comprehensive web application security course covering OWASP Top 10, advanced exploitation techniques, API security, and modern web vulnerabilities. Includes hands-on labs with real vulnerable applications.

## Learning Objectives

1. **Identify** web application vulnerabilities
2. **Exploit** OWASP Top 10 vulnerabilities
3. **Test** REST and GraphQL APIs
4. **Bypass** security controls
5. **Perform** source code review
6. **Implement** security testing automation
7. **Analyze** client-side vulnerabilities
8. **Report** web security findings

---

## Module 1: Web Application Fundamentals (Week 1)

### Lesson 1.1: Web Architecture

**Theory:**
- Client-server model
- HTTP/HTTPS protocol
- Cookies and sessions
- Authentication mechanisms

**Web Architecture:**
```
Client (Browser) → CDN → Load Balancer → Web Server → Application Server → Database
                              ↓
                         WAF (Web Application Firewall)
```

### Lesson 1.2: HTTP Protocol Deep Dive

**Theory:**
- HTTP methods (GET, POST, PUT, DELETE, PATCH)
- Status codes
- Headers
- Request/Response structure

**HTTP Methods:**
| Method | Purpose | Body | Secure |
|--------|---------|------|--------|
| GET | Retrieve data | No | Should be |
| POST | Create data | Yes | Yes |
| PUT | Update data | Yes | Yes |
| DELETE | Remove data | No | Yes |
| PATCH | Partial update | Yes | Yes |

**Practical Lab 1.2.1: HTTP Analysis**
- Capture HTTP traffic
- Analyze headers
- Manipulate requests

### Lesson 1.3: Session Management

**Theory:**
- Session tokens
- Cookie attributes
- Session fixation
- Session hijacking

**Practical Lab 1.3.1: Session Testing**
- Analyze session handling
- Test session security
- Exploit session flaws

---

## Module 2: OWASP Top 10 (Week 2-3)

### Lesson 2.1: Broken Access Control

**Theory:**
- IDOR (Insecure Direct Object Reference)
- Privilege escalation
- Missing function level access control
- CORS misconfiguration

**Exploitation Techniques:**
```
1. Parameter manipulation
2. Directory traversal
3. API endpoint testing
4. Function level testing
```

**Practical Lab 2.1.1: Access Control Testing**
- Test IDOR vulnerabilities
- Escalate privileges
- Bypass access controls

### Lesson 2.2: Cryptographic Failures

**Theory:**
- Weak algorithms
- Poor key management
- Data exposure
- Protocol vulnerabilities

**Practical Lab 2.2.1: Crypto Testing**
- Identify weak crypto
- Test for data exposure
- Analyze key management

### Lesson 2.3: Injection

**Theory:**
- SQL injection (all types)
- NoSQL injection
- OS command injection
- LDAP injection
- XPath injection

**SQL Injection Types:**
```sql
-- In-band (Union)
' UNION SELECT username, password FROM users--

-- Blind (Boolean)
' AND 1=1-- (true)
' AND 1=2-- (false)

-- Blind (Time-based)
' AND SLEEP(5)--

-- Error-based
' AND 1=CONVERT(int, @@version)--
```

**Practical Lab 2.3.1: Injection Attacks**
- Perform SQL injection
- Exploit NoSQL databases
- Test command injection

### Lesson 2.4: Insecure Design

**Theory:**
- Threat modeling
- Secure design patterns
- Business logic flaws
- Missing security controls

**Practical Lab 2.4.1: Design Review**
- Analyze application design
- Identify logic flaws
- Recommend improvements

### Lesson 2.5: Security Misconfiguration

**Theory:**
- Default configurations
- Verbose error messages
- Unnecessary features
- Missing security headers

**Security Headers:**
```
Content-Security-Policy
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Strict-Transport-Security
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin
```

**Practical Lab 2.5.1: Configuration Testing**
- Test security headers
- Identify misconfigurations
- Exploit default settings

### Lesson 2.6: Vulnerable Components

**Theory:**
- Outdated dependencies
- Known vulnerabilities
- Component inventory
- Update strategies

**Practical Lab 2.6.1: Component Analysis**
- Identify outdated components
- Find known vulnerabilities
- Test for exploitation

### Lesson 2.7: Authentication Failures

**Theory:**
- Brute force attacks
- Credential stuffing
- Weak passwords
- Multi-factor bypass

**Practical Lab 2.7.1: Authentication Testing**
- Test authentication mechanisms
- Bypass security controls
- Enumerate users

### Lesson 2.8: Software and Data Integrity

**Theory:**
- Deserialization attacks
- CI/CD pipeline security
- Auto-update vulnerabilities
- Integrity verification

**Practical Lab 2.8.1: Integrity Testing**
- Test deserialization
- Verify data integrity
- Analyze update mechanisms

### Lesson 2.9: Logging and Monitoring

**Theory:**
- Insufficient logging
- Missing alerting
- Log injection
- Audit trails

**Practical Lab 2.9.1: Logging Analysis**
- Test logging mechanisms
- Inject log data
- Analyze monitoring gaps

---

## Module 3: API Security (Week 4)

### Lesson 3.1: REST API Security

**Theory:**
- Authentication (OAuth, JWT, API keys)
- Authorization
- Rate limiting
- Input validation

**REST Security Checklist:**
```
✓ Authentication required
✓ Authorization enforced
✓ Rate limiting implemented
✓ Input validation
✓ Output encoding
✓ HTTPS enforced
✓ Error handling
✓ Logging enabled
```

**Practical Lab 3.1.1: REST API Testing**
- Test authentication
- Bypass authorization
- Abuse rate limiting

### Lesson 3.2: GraphQL Security

**Theory:**
- Query complexity
- Introspection abuse
- Authorization bypass
- Batch attacks

**Practical Lab 3.2.1: GraphQL Testing**
- Enumerate schema
- Test authorization
- Exploit query complexity

### Lesson 3.3: API Authentication Attacks

**Theory:**
- JWT vulnerabilities
- OAuth flaws
- API key exposure
- Token manipulation

**Practical Lab 3.3.1: Auth Attacks**
- Manipulate JWT tokens
- Exploit OAuth flows
- Steal API keys

---

## Module 4: Client-Side Attacks (Week 5)

### Lesson 4.1: Cross-Site Scripting (XSS)

**Theory:**
- Reflected XSS
- Stored XSS
- DOM-based XSS
- Mutation XSS

**XSS Payloads:**
```javascript
// Basic
<script>alert('XSS')</script>

// Image tag
<img src=x onerror=alert('XSS')>

// SVG
<svg onload=alert('XSS')>

// Event handler
<body onload=alert('XSS')>
```

**Practical Lab 4.1.1: XSS Exploitation**
- Find XSS vulnerabilities
- Bypass filters
- Steal session cookies

### Lesson 4.2: Cross-Site Request Forgery

**Theory:**
- CSRF mechanisms
- Token-based defense
- SameSite cookies
- CSRF bypass techniques

**Practical Lab 4.2.1: CSRF Attacks**
- Perform CSRF attacks
- Bypass CSRF protections
- Exploit state-changing actions

### Lesson 4.3: Clickjacking

**Theory:**
- Frame injection
- UI redressing
- Drag-and-drop attacks
- Defense mechanisms

**Practical Lab 4.3.1: Clickjacking**
- Create clickjacking attacks
- Test frame protection
- Bypass defenses

### Lesson 4.4: WebSocket Security

**Theory:**
- WebSocket protocol
- Authentication
- Input validation
- Message integrity

**Practical Lab 4.4.1: WebSocket Testing**
- Test WebSocket security
- Manipulate messages
- Bypass authentication

---

## Module 5: Advanced Attacks (Week 6)

### Lesson 5.1: Server-Side Request Forgery

**Theory:**
- SSRF mechanisms
- Internal network access
- Cloud metadata abuse
- Filter bypass

**Practical Lab 5.1.1: SSRF Exploitation**
- Perform SSRF attacks
- Access internal services
- Exploit cloud metadata

### Lesson 5.2: File Upload Vulnerabilities

**Theory:**
- Upload bypass techniques
- Web shell deployment
- Content-type manipulation
- Path traversal

**Practical Lab 5.2.1: File Upload Attacks**
- Bypass upload restrictions
- Deploy web shells
- Execute arbitrary code

### Lesson 5.3: XML External Entity

**Theory:**
- XXE injection
- Blind XXE
- SSRF via XXE
- Prevention techniques

**Practical Lab 5.3.1: XXE Attacks**
- Exploit XXE vulnerabilities
- Extract sensitive data
- Perform SSRF

### Lesson 5.4: Server-Side Template Injection

**Theory:**
- SSTI detection
- Template engine exploitation
- Remote code execution
- Prevention

**Practical Lab 5.4.1: SSTI Attacks**
- Detect SSTI vulnerabilities
- Exploit template engines
- Execute commands

---

## Module 6: Source Code Review (Week 7)

### Lesson 6.1: Code Review Methodology

**Theory:**
- Manual review techniques
- Automated analysis
- Pattern recognition
- Vulnerability identification

### Lesson 6.2: Common Code Vulnerabilities

**Theory:**
- Hardcoded secrets
- SQL injection patterns
- XSS in templates
- Insecure deserialization

**Practical Lab 6.2.1: Code Review**
- Review source code
- Identify vulnerabilities
- Recommend fixes

---

## Module 7: Automation and Tools (Week 8)

### Lesson 7.1: Security Testing Tools

**Theory:**
- Burp Suite
- OWASP ZAP
- Nuclei
- SQLMap

### Lesson 7.2: Custom Tool Development

**Theory:**
- Python for web testing
- Custom scanners
- Automation frameworks
- Integration techniques

**Practical Lab 7.2.1: Tool Development**
- Create custom tools
- Automate testing
- Integrate with workflows

---

## Final Exam

**Duration:** 72 hours
**Format:** Web application penetration test
**Passing Score:** 70%

### Exam Components
- Vulnerability identification (30%)
- Exploitation (40%)
- Reporting (30%)

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
