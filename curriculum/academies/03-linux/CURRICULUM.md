# Academy 3: Linux Administration

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Beginner
**Prerequisites:** Academy 1-2 completed
**Certification:** CyberLab Certified Beginner (CCB) - Part 3

## Course Description

This course provides comprehensive Linux administration skills essential for cybersecurity professionals. Students will master command-line operations, system administration, security configurations, and bash scripting.

## Learning Objectives

1. **Navigate** the Linux filesystem and command line
2. **Manage** users, groups, and permissions
3. **Configure** network and services
4. **Implement** Linux security controls
5. **Write** bash scripts for automation
6. **Troubleshoot** system issues
7. **Monitor** system activity
8. **Harden** Linux systems

---

## Module 1: Linux Fundamentals (Week 1)

### Lesson 1.1: Linux Overview

**Theory:**
- Linux distributions (Debian, Red Hat, Arch)
- Linux architecture (kernel, shell, filesystem)
- Open-source philosophy
- Linux in cybersecurity

**Linux Distributions for Security:**
| Distribution | Use Case |
|--------------|----------|
| Kali Linux | Penetration testing |
| Parrot OS | Security auditing |
| Ubuntu | General use |
| CentOS/RHEL | Enterprise servers |
| Alpine | Containers |

**Practical Lab 1.1.1: Linux Installation**
- Install Linux in VM
- Explore desktop environment
- Access terminal

### Lesson 1.2: File System Hierarchy

**Theory:**
- Filesystem Standard (FHS)
- Directory structure
- File types
- Path concepts

**Linux Directory Structure:**
```
/           Root directory
├── bin     Essential binaries
├── boot    Boot files
├── dev     Device files
├── etc     Configuration files
├── home    User home directories
├── lib     Shared libraries
├── media   Mount points
├── mnt     Temporary mounts
├── opt     Optional software
├── proc    Process information
├── root    Root user home
├── sbin    System binaries
├── srv     Service data
├── sys     System information
├── tmp     Temporary files
├── usr     User programs
└── var     Variable data
```

**Practical Lab 1.2.1: Filesystem Navigation**
- Navigate directory structure
- Create/delete directories
- Understand file paths

### Lesson 1.3: Basic Commands

**Theory:**
- File operations (ls, cd, pwd, cp, mv, rm)
- Text operations (cat, grep, find, less)
- System info (whoami, date, uname)
- Help system (man, info, --help)

**Essential Commands:**
```bash
# Navigation
ls -la          # List files with details
cd /path        # Change directory
pwd             # Print working directory

# File Operations
cp source dest  # Copy files
mv source dest  # Move/rename files
rm -rf dir      # Remove directory
mkdir -p dir    # Create directory

# Text Operations
cat file        # Display file
grep pattern file  # Search in file
find / -name "*.txt"  # Find files
head/tail file  # View beginning/end
```

**Practical Lab 1.3.1: Command Practice**
- Practice file operations
- Use wildcards and globbing
- Chain commands with pipes

### Lesson 1.4: Permissions

**Theory:**
- Permission types (read, write, execute)
- Permission notation (rwx, numeric)
- Ownership (user, group, others)
- Special permissions (SUID, SGID, sticky bit)

**Permission Notation:**
```
rwxr-xr-x = 755
rw-r--r-- = 644
rwx------ = 700

User   Group   Others
rwx    r-x     r-x
7      5       5
```

**Practical Lab 1.4.1: Permission Management**
- Change file permissions
- Manage ownership
- Test special permissions

---

## Module 2: User and Group Management (Week 2)

### Lesson 2.1: User Management

**Theory:**
- User accounts (UID, GID)
- User configuration files
- Creating/modifying users
- Password management

**User Configuration Files:**
```
/etc/passwd      User account info
/etc/shadow      Password hashes
/etc/group       Group information
/etc/gshadow     Group passwords
```

**Practical Lab 2.1.1: User Administration**
- Create user accounts
- Configure user properties
- Manage password policies

### Lesson 2.2: Group Management

**Theory:**
- Group types (primary, supplementary)
- Group configuration
- Managing group membership
- Group permissions

**Practical Lab 2.2.1: Group Administration**
- Create groups
- Add/remove users
- Configure group permissions

### Lesson 2.3: Sudo Configuration

**Theory:**
- Sudo purpose and security
- Sudoers file syntax
- Sudo logging
- Privilege escalation risks

**Sudoers Syntax:**
```
# User privilege specification
root    ALL=(ALL:ALL) ALL
user1   ALL=(ALL) NOPASSWD: ALL

# Group privilege
%admin  ALL=(ALL) ALL
```

**Practical Lab 2.3.1: Sudo Configuration**
- Configure sudo access
- Test sudo permissions
- Monitor sudo usage

### Lesson 2.4: PAM (Pluggable Authentication)

**Theory:**
- PAM architecture
- Authentication modules
- PAM configuration
- Security considerations

**Practical Lab 2.4.1: PAM Configuration**
- Configure authentication
- Implement account policies
- Test PAM modules

---

## Module 3: System Administration (Week 3)

### Lesson 3.1: Package Management

**Theory:**
- Package managers (apt, yum, pacman)
- Installing/removing software
- Repository management
- Dependency resolution

**Package Manager Commands:**
```bash
# Debian/Ubuntu (apt)
apt update
apt install package
apt remove package
apt upgrade

# Red Hat/CentOS (yum/dnf)
yum install package
yum remove package
yum update
```

**Practical Lab 3.1.1: Package Management**
- Install software packages
- Configure repositories
- Resolve dependencies

### Lesson 3.2: Service Management

**Theory:**
- Systemd services
- Service states (start, stop, enable)
- Service dependencies
- Service logs

**Service Commands:**
```bash
systemctl start service     # Start service
systemctl stop service      # Stop service
systemctl enable service    # Enable on boot
systemctl status service    # Check status
journalctl -u service       # View logs
```

**Practical Lab 3.2.1: Service Administration**
- Manage system services
- Configure service startup
- Troubleshoot service issues

### Lesson 3.3: Process Management

**Theory:**
- Process states
- Process priorities
- Signals
- Background/foreground processes

**Process Commands:**
```bash
ps aux              # List processes
top/htop            # Process monitor
kill PID            # Send signal
kill -9 PID         # Force kill
nice -n 10 command  # Set priority
bg/fg               # Background/foreground
```

**Practical Lab 3.3.1: Process Management**
- Monitor processes
- Manage process priorities
- Handle zombie processes

### Lesson 3.4: Disk Management

**Theory:**
- Disk partitioning (fdisk, parted)
- Filesystem creation (mkfs)
- Mounting/unmounting
- LVM (Logical Volume Management)
- RAID configurations

**Disk Commands:**
```bash
fdisk -l            # List partitions
df -h               # Disk usage
du -sh directory    # Directory size
mount /dev/sdb1 /mnt  # Mount partition
umount /mnt         # Unmount
```

**Practical Lab 3.4.1: Disk Administration**
- Partition disks
- Create filesystems
- Configure LVM

---

## Module 4: Networking (Week 3-4)

### Lesson 4.1: Network Configuration

**Theory:**
- Network interfaces
- IP configuration
- DNS configuration
- Routing

**Network Commands:**
```bash
ip addr show        # Show IP addresses
ip route show       # Show routing table
ip link show        # Show interfaces
ss -tuln           # Show listening ports
```

**Practical Lab 4.1.1: Network Configuration**
- Configure network interfaces
- Set static IP addresses
- Configure DNS

### Lesson 4.2: Firewall (iptables/nftables)

**Theory:**
- iptables concepts
- Rule structure
- Chains and tables
- NAT configuration

**iptables Basics:**
```bash
iptables -L                     # List rules
iptables -A INPUT -s IP -j DROP # Block IP
iptables -A INPUT -p tcp --dport 22 -j ACCEPT  # Allow SSH
```

**Practical Lab 4.2.1: Firewall Configuration**
- Configure iptables rules
- Implement NAT
- Test firewall

### Lesson 4.3: SSH Configuration

**Theory:**
- SSH keys
- SSH hardening
- SSH tunneling
- SSH security best practices

**SSH Hardening:**
```
# /etc/ssh/sshd_config
Port 2222
PermitRootLogin no
PasswordAuthentication no
MaxAuthTries 3
AllowUsers user1 user2
```

**Practical Lab 4.3.1: SSH Setup**
- Generate SSH keys
- Configure SSH server
- Implement SSH tunneling

### Lesson 4.4: Network Monitoring

**Theory:**
- Network tools (netstat, ss, lsof)
- Packet capture (tcpdump)
- Bandwidth monitoring
- Connection tracking

**Practical Lab 4.4.1: Network Monitoring**
- Monitor network connections
- Capture and analyze traffic
- Detect suspicious activity

---

## Module 5: Security Hardening (Week 4-5)

### Lesson 5.1: File Security

**Theory:**
- File integrity monitoring
- ACLs (Access Control Lists)
- Encryption (GPG, LUKS)
- Secure deletion

**File Security Commands:**
```bash
# ACLs
getfacl file
setfacl -m u:user:rwx file

# Encryption
gpg -c file       # Encrypt
gpg file.gpg      # Decrypt

# Integrity
sha256sum file    # Generate hash
```

**Practical Lab 5.1.1: File Security**
- Implement file permissions
- Use ACLs
- Encrypt sensitive files

### Lesson 5.2: System Hardening

**Theory:**
- CIS Benchmarks
- Disable unnecessary services
- Kernel hardening
- Audit configuration

**Hardening Checklist:**
- Disable unused services
- Remove unnecessary packages
- Configure SELinux/AppArmor
- Enable auditing
- Set password policies
- Configure logging

**Practical Lab 5.2.1: System Hardening**
- Apply CIS benchmarks
- Disable unnecessary services
- Configure auditd

### Lesson 5.3: Logging and Monitoring

**Theory:**
- System logs (/var/log)
- Log rotation
- Centralized logging
- Audit logs

**Important Log Files:**
```
/var/log/auth.log       Authentication logs
/var/log/syslog         System logs
/var/log/kern.log       Kernel logs
/var/log/dmesg          Boot messages
/var/log/apache2/       Web server logs
```

**Practical Lab 5.3.1: Log Management**
- Configure log rotation
- Analyze system logs
- Set up log monitoring

### Lesson 5.4: Intrusion Detection

**Theory:**
- File integrity monitoring (AIDE, Tripwire)
- Rootkit detection (rkhunter, chkrootkit)
- Log analysis
- Alert configuration

**Practical Lab 5.4.1: IDS Setup**
- Install and configure AIDE
- Set up rkhunter
- Create monitoring scripts

---

## Module 6: Bash Scripting (Week 5-6)

### Lesson 6.1: Shell Scripting Basics

**Theory:**
- Script structure
- Variables
- User input
- Output

**Basic Script Structure:**
```bash
#!/bin/bash
# Comment

# Variables
NAME="CyberLab"
echo "Welcome to $NAME"

# User input
read -p "Enter username: " USER
```

**Practical Lab 6.1.1: First Scripts**
- Write basic scripts
- Use variables
- Handle user input

### Lesson 6.2: Control Structures

**Theory:**
- if/else statements
- Case statements
- Loops (for, while, until)
- Functions

**Control Structures:**
```bash
# If/else
if [ condition ]; then
    echo "true"
else
    echo "false"
fi

# For loop
for i in {1..10}; do
    echo $i
done

# While loop
while [ $count -lt 10 ]; do
    ((count++))
done

# Function
function greet() {
    echo "Hello $1"
}
```

**Practical Lab 6.2.1: Control Flow**
- Implement conditional logic
- Use loops effectively
- Create reusable functions

### Lesson 6.3: Advanced Scripting

**Theory:**
- Arrays
- String manipulation
- Arithmetic operations
- Regular expressions

**Practical Lab 6.3.1: Advanced Techniques**
- Work with arrays
- Parse text with regex
- Perform calculations

### Lesson 6.4: Security Scripts

**Theory:**
- Security automation
- Monitoring scripts
- Backup scripts
- Incident response scripts

**Example Security Script:**
```bash
#!/bin/bash
# Security monitoring script

# Check for failed logins
failed=$(grep "Failed password" /var/log/auth.log | tail -5)
echo "Recent failed logins:"
echo "$failed"

# Check listening ports
echo -e "\nListening ports:"
ss -tuln | grep LISTEN

# Check disk space
echo -e "\nDisk usage:"
df -h | grep -v tmpfs
```

**Practical Lab 6.4.1: Security Automation**
- Write monitoring scripts
- Create backup automation
- Develop incident response tools

---

## Assignments

### Assignment 1: System Setup
**Objective:** Set up and secure a Linux server
**Deliverables:**
- Installation documentation
- Hardening checklist
- Service configuration
- Security report

### Assignment 2: Automation Project
**Objective:** Create automation scripts
**Deliverables:**
- Monitoring scripts
- Backup automation
- Security scanning tools
- Documentation

### Assignment 3: Security Audit
**Objective:** Audit a Linux system
**Deliverables:**
- Vulnerability assessment
- Configuration review
- Hardening recommendations
- Implementation plan

---

## Quiz Questions

### Quiz 1: Fundamentals

1. **What command changes file permissions?**
   a) chmod
   b) chown
   c) chgrp
   d) perm

2. **Which directory contains configuration files?**
   a) /bin
   b) /etc
   c) /var
   d) /tmp

3. **What is the root user's UID?**
   a) 0
   b) 1
   c) 100
   d) 1000

4. **Which command shows running processes?**
   a) ls
   b) ps
   c) df
   d) du

5. **What does "chmod 755" mean?**
   a) Read-write for all
   b) Full access for owner, read-execute for others
   c) Read-only for all
   d) No access

### Quiz 2: Security

1. **Which file contains user password hashes?**
   a) /etc/passwd
   b) /etc/shadow
   c) /etc/group
   d) /etc/sudoers

2. **What is the purpose of sudo?**
   a) Switch user
   b) Execute as superuser
   c) Secure delete
   d) System update

3. **Which command securely deletes files?**
   a) rm
   b) shred
   c) delete
   d) wipe

4. **What is SELinux?**
   a) Security tool
   b) Linux distribution
   c) Kernel module
   d) File manager

5. **Which log contains authentication events?**
   a) syslog
   b) auth.log
   c) kern.log
   d) dmesg

---

## Final Exam

**Duration:** 3 hours
**Format:** Practical + Theory
**Passing Score:** 75%

### Exam Sections

**Section 1: Theory (25 points)**
- Filesystem knowledge
- Command syntax
- Security concepts

**Section 2: Administration (40 points)**
- User management
- Service configuration
- Network setup

**Section 3: Security (35 points)**
- System hardening
- Script writing
- Security auditing

---

## Resources

### Required Tools
- Virtual Machine (VirtualBox/VMware)
- Kali Linux or Ubuntu
- Terminal access

### Practice Platforms
- CyberLab Linux Labs
- OverTheWire (Bandit)
- Linux Survival

### Books
- "The Linux Command Line"
- "Linux Security Cookbook"
- "How Linux Works"

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
