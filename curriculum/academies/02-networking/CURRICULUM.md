# Academy 2: Networking

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Beginner
**Prerequisites:** Academy 1 completed
**Certification:** CyberLab Certified Beginner (CCB) - Part 2

## Course Description

This comprehensive networking course covers the OSI model, TCP/IP protocol suite, network devices, and security fundamentals. Students will gain hands-on experience with network analysis, configuration, and troubleshooting.

## Learning Objectives

1. **Explain** the OSI and TCP/IP models
2. **Configure** network devices (routers, switches, firewalls)
3. **Analyze** network traffic using Wireshark
4. **Implement** network security controls
5. **Troubleshoot** common network issues
6. **Design** secure network architectures
7. **Apply** network segmentation principles
8. **Monitor** network activity for threats

---

## Module 1: Network Fundamentals (Week 1-2)

### Lesson 1.1: OSI Model

**Theory:**
- Seven layers of the OSI model
- Data encapsulation and de-encapsulation
- PDU (Protocol Data Units) at each layer

**OSI Model Reference:**
```
Layer 7: Application    - HTTP, FTP, SMTP, DNS
Layer 6: Presentation   - SSL/TLS, Encryption
Layer 5: Session        - NetBIOS, RPC
Layer 4: Transport      - TCP, UDP
Layer 3: Network        - IP, ICMP, Routing
Layer 2: Data Link      - Ethernet, MAC, Switching
Layer 1: Physical       - Cables, Hubs, Signals
```

**Practical Lab 1.1.1: Protocol Analysis**
- Map protocols to OSI layers
- Capture traffic at different layers
- Analyze encapsulation

### Lesson 1.2: TCP/IP Model

**Theory:**
- Four layers of TCP/IP
- Comparison with OSI model
- TCP vs UDP

**TCP vs UDP Comparison:**
| Feature | TCP | UDP |
|---------|-----|-----|
| Connection | Connection-oriented | Connectionless |
| Reliability | Guaranteed delivery | Best effort |
| Order | Ordered | Unordered |
| Speed | Slower | Faster |
| Use Case | Web, Email, SSH | DNS, Streaming, Gaming |

**Practical Lab 1.2.1: TCP Handshake**
- Capture TCP three-way handshake
- Analyze SYN, SYN-ACK, ACK
- Identify connection states

### Lesson 1.3: IP Addressing

**Theory:**
- IPv4 addressing
- Subnetting and CIDR
- IPv6 basics
- Private vs public IPs
- NAT (Network Address Translation)

**IPv4 Address Classes:**
| Class | Range | Default Mask | Purpose |
|-------|-------|--------------|---------|
| A | 1.0.0.0 - 126.255.255.255 | 255.0.0.0 | Large networks |
| B | 128.0.0.0 - 191.255.255.255 | 255.255.0.0 | Medium networks |
| C | 192.0.0.0 - 223.255.255.255 | 255.255.255.0 | Small networks |

**Private IP Ranges:**
```
10.0.0.0 - 10.255.255.255 (Class A)
172.16.0.0 - 172.31.255.255 (Class B)
192.168.0.0 - 192.168.255.255 (Class C)
```

**Practical Lab 1.3.1: Subnetting Practice**
- Calculate subnets
- Design IP addressing schemes
- Configure static IPs

### Lesson 1.4: Network Devices

**Theory:**
- Hubs vs Switches vs Routers
- Firewalls
- Access Points
- Load Balancers

**Device Comparison:**
| Device | Layer | Function | Intelligence |
|--------|-------|----------|--------------|
| Hub | 1 | Broadcast | None |
| Switch | 2 | MAC learning | Low |
| Router | 3 | IP routing | Medium |
| Firewall | 3-7 | Security filtering | High |

**Practical Lab 1.4.1: Device Configuration**
- Configure switch VLANs
- Set up router interfaces
- Implement basic firewall rules

---

## Module 2: TCP/IP Deep Dive (Week 2-3)

### Lesson 2.1: TCP Protocol

**Theory:**
- Connection establishment (three-way handshake)
- Flow control and windowing
- Error detection and recovery
- Connection termination

**TCP States:**
```
LISTEN → SYN_SENT → ESTABLISHED → FIN_WAIT_1 → FIN_WAIT_2
    ↓                                              ↓
SYN_RECEIVED → ESTABLISHED → CLOSE_WAIT → LAST_ACK
                                          ↓
                                       CLOSED
```

**Practical Lab 2.1.1: TCP Analysis**
- Capture TCP sessions
- Analyze state transitions
- Identify connection issues

### Lesson 2.2: UDP Protocol

**Theory:**
- Connectionless communication
- Datagram structure
- Use cases
- UDP vs TCP decision

**Practical Lab 2.2.1: UDP Traffic Analysis**
- Capture UDP traffic
- Compare with TCP
- Analyze DNS queries

### Lesson 2.3: DNS

**Theory:**
- DNS hierarchy
- Record types (A, AAAA, CNAME, MX, TXT, NS)
- DNS resolution process
- DNS security

**DNS Record Types:**
| Record | Purpose | Example |
|--------|---------|---------|
| A | IPv4 address | example.com → 93.184.216.34 |
| AAAA | IPv6 address | example.com → 2606:2800:220:1:... |
| CNAME | Alias | www.example.com → example.com |
| MX | Mail server | example.com → mail.example.com |
| NS | Name server | example.com → ns1.example.com |
| TXT | Text record | SPF, DKIM, DMARC |

**Practical Lab 2.3.1: DNS Analysis**
- Query DNS records
- Analyze DNS traffic
- Implement DNS security

### Lesson 2.4: HTTP/HTTPS

**Theory:**
- HTTP methods (GET, POST, PUT, DELETE)
- Status codes
- Headers
- TLS/SSL handshake
- Certificate verification

**HTTP Status Codes:**
| Code | Category | Description |
|------|----------|-------------|
| 2xx | Success | Request successful |
| 3xx | Redirection | Resource moved |
| 4xx | Client Error | Bad request, not found |
| 5xx | Server Error | Internal server error |

**Practical Lab 2.4.1: HTTP Traffic Analysis**
- Capture HTTP/HTTPS traffic
- Analyze request/response
- Inspect headers

---

## Module 3: Network Security (Week 3-4)

### Lesson 3.1: Firewalls

**Theory:**
- Packet filtering
- Stateful inspection
- Application layer firewalls
- Next-generation firewalls

**Firewall Types:**
| Type | OSI Layer | Function |
|------|-----------|----------|
| Packet filtering | 3-4 | Rule-based filtering |
| Stateful | 3-4 | Connection tracking |
| Application | 7 | Deep packet inspection |
| NGFW | 3-7 | Advanced threat protection |

**Practical Lab 3.1.1: Firewall Configuration**
- Configure iptables rules
- Test firewall effectiveness
- Analyze firewall logs

### Lesson 3.2: IDS/IPS

**Theory:**
- Signature-based detection
- Anomaly-based detection
- Network IDS vs Host IDS
- IDS vs IPS

**Detection Methods:**
| Method | Pros | Cons |
|--------|------|------|
| Signature | Low false positives | Can't detect new attacks |
| Anomaly | Detects unknown attacks | High false positives |

**Practical Lab 3.2.1: IDS Setup**
- Deploy Snort/Suricata
- Configure rules
- Test detection

### Lesson 3.3: VPN

**Theory:**
- Site-to-site VPN
- Remote access VPN
- IPSec vs SSL VPN
- Tunneling protocols

**Practical Lab 3.3.1: VPN Configuration**
- Set up OpenVPN server
- Configure client connections
- Test VPN security

### Lesson 3.4: Network Segmentation

**Theory:**
- VLAN implementation
- DMZ architecture
- Microsegmentation
- Zero Trust networking

**Network Segmentation Example:**
```
Internet → Edge Firewall → DMZ → Core Firewall → Internal
                         │           │
                    Web Servers    User VLANs
                    Mail           Server VLAN
                                   Guest VLAN
```

**Practical Lab 3.4.1: Segmentation Implementation**
- Configure VLANs
- Implement inter-VLAN routing
- Test isolation

---

## Module 4: Network Analysis (Week 4-5)

### Lesson 4.1: Wireshark Fundamentals

**Theory:**
- Capturing packets
- Display filters
- Protocol analysis
- Follow TCP streams

**Wireshark Filters:**
```
# Filter by IP
ip.addr == 192.168.1.100

# Filter by protocol
tcp.port == 80
http.request.method == "GET"

# Filter by content
tcp contains "password"
```

**Practical Lab 4.1.1: Traffic Capture**
- Capture network traffic
- Apply display filters
- Analyze protocols

### Lesson 4.2: Network Scanning

**Theory:**
- Port scanning techniques
- Service identification
- OS fingerprinting
- Network mapping

**Port Scan Types:**
| Scan | TCP Flags | Stealth | Speed |
|------|-----------|---------|-------|
| SYN | SYN | High | Fast |
| ACK | ACK | Medium | Fast |
| FIN | FIN | High | Slow |
| XMAS | FIN+PSH+URG | High | Slow |

**Practical Lab 4.2.1: Nmap Scanning**
- Perform port scans
- Identify services
- Create network maps

### Lesson 4.3: Protocol Analysis

**Theory:**
- ARP analysis
- ICMP analysis
- DHCP analysis
- SNMP analysis

**Practical Lab 4.3.1: Protocol Deep Dive**
- Analyze ARP traffic
- Detect ARP spoofing
- Monitor DHCP requests

### Lesson 4.4: Network Troubleshooting

**Theory:**
- Common network issues
- Troubleshooting methodology
- Diagnostic tools
- Performance analysis

**Troubleshooting Commands:**
```bash
# Connectivity
ping, traceroute, tracert

# DNS
nslookup, dig, host

# Network info
ifconfig, ip, netstat, ss

# Packet capture
tcpdump, wireshark

# Port check
telnet, nc, nmap
```

**Practical Lab 4.4.1: Troubleshooting Scenarios**
- Diagnose connectivity issues
- Resolve DNS problems
- Fix routing issues

---

## Module 5: Advanced Networking (Week 5-6)

### Lesson 5.1: Wireless Security

**Theory:**
- WiFi protocols (WEP, WPA, WPA2, WPA3)
- Wireless attacks
- Rogue access points
- WiFi security best practices

**Wireless Security Comparison:**
| Protocol | Security | Status |
|----------|----------|--------|
| WEP | Weak | Deprecated |
| WPA | Moderate | Legacy |
| WPA2 | Strong | Current standard |
| WPA3 | Very Strong | New standard |

**Practical Lab 5.1.1: Wireless Security**
- Configure secure WiFi
- Test wireless security
- Detect rogue APs

### Lesson 5.2: IPv6

**Theory:**
- IPv6 addressing
- IPv6 headers
- IPv6 transition mechanisms
- IPv6 security considerations

**Practical Lab 5.2.1: IPv6 Configuration**
- Configure IPv6 addresses
- Test IPv6 connectivity
- Implement IPv6 security

### Lesson 5.3: SDN and Network Automation

**Theory:**
- Software-Defined Networking
- Network automation tools
- Infrastructure as Code
- API-driven networking

**Practical Lab 5.3.1: Network Automation**
- Write network scripts
- Automate configurations
- Monitor programmatically

### Lesson 5.4: Cloud Networking

**Theory:**
- VPC (Virtual Private Cloud)
- Subnets and routing
- Security groups
- Cloud load balancing

**Practical Lab 5.4.1: Cloud Network Setup**
- Create VPC architecture
- Configure security groups
- Implement load balancing

---

## Assignments

### Assignment 1: Network Design
**Objective:** Design a secure network for a small business
**Deliverables:**
- Network topology diagram
- IP addressing scheme
- Security controls
- Documentation

### Assignment 2: Packet Analysis
**Objective:** Analyze captured network traffic
**Deliverables:**
- Traffic analysis report
- Protocol identification
- Security findings
- Recommendations

### Assignment 3: Security Audit
**Objective:** Conduct network security audit
**Deliverables:**
- Vulnerability assessment
- Configuration review
- Risk analysis
- Remediation plan

---

## Quiz Questions

### Quiz 1: Fundamentals

1. **Which OSI layer is responsible for routing?**
   a) Layer 2
   b) Layer 3
   c) Layer 4
   d) Layer 7

2. **What is the purpose of NAT?**
   a) Encrypt traffic
   b) Translate IP addresses
   c) Filter packets
   d) Route packets

3. **Which protocol is connectionless?**
   a) TCP
   b) UDP
   c) HTTP
   d) SSH

4. **What does a switch use to forward frames?**
   a) IP address
   b) MAC address
   c) Port number
   d) Hostname

5. **Which DNS record maps a hostname to an IP?**
   a) MX
   b) CNAME
   c) A
   d) TXT

### Quiz 2: Security

1. **What is the primary function of a firewall?**
   a) Route packets
   b) Filter traffic
   c) Assign IPs
   d) Resolve DNS

2. **Which IDS uses known attack patterns?**
   a) Anomaly-based
   b) Signature-based
   c) Heuristic
   d) Behavioral

3. **What type of VPN uses SSL/TLS?**
   a) IPSec
   b) PPTP
   c) SSL VPN
   d) L2TP

4. **What is VLAN hopping?**
   a) Moving between VLANs
   b) Unauthorized VLAN access
   c) VLAN configuration
   d) VLAN tagging

5. **What is the purpose of a DMZ?**
   a) Internal network
   b) Public-facing servers
   c) Guest access
   d) Management network

---

## Final Exam

**Duration:** 3 hours
**Format:** Theory + Practical
**Passing Score:** 75%

### Exam Sections

**Section 1: Theory (30 points)**
- OSI/TCP-IP models
- Protocol knowledge
- Network security concepts

**Section 2: Analysis (40 points)**
- Packet capture analysis
- Network diagram interpretation
- Traffic pattern recognition

**Section 3: Practical (30 points)**
- Network configuration
- Security implementation
- Troubleshooting

---

## Resources

### Required Tools
- Wireshark
- Nmap
- tcpdump
- Netcat
- Cisco Packet Tracer / GNS3

### Practice Platforms
- Cisco Networking Academy
- CyberLab Networking Labs
- TryHackMe Network modules

### Books
- "Computer Networking: A Top-Down Approach"
- "TCP/IP Illustrated, Volume 1"
- "Network Security Assessment"

---

## Grading Criteria

| Component | Weight |
|-----------|--------|
| Lab Completions | 35% |
| Assignments | 25% |
| Quizzes | 15% |
| Final Exam | 25% |

**Passing Grade:** 70%
**Certification Requirement:** 80%
