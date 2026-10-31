# Academy 9: Cryptography

## Course Overview

**Duration:** 6 weeks (60 hours)
**Level:** Advanced
**Prerequisites:** Academy 4, 6 completed
**Certification:** CyberLab Certified Professional (CCP-Pro) - Part 3

## Course Description

Comprehensive cryptography course covering encryption algorithms, key management, cryptographic protocols, cryptanalysis, and practical applications in cybersecurity.

## Learning Objectives

1. **Understand** cryptographic principles
2. **Implement** encryption algorithms
3. **Analyze** cryptographic protocols
4. **Perform** cryptanalysis
5. **Apply** cryptography in security
6. **Evaluate** cryptographic implementations
7. **Break** weak cryptographic systems
8. **Design** secure systems

---

## Module 1: Cryptographic Fundamentals (Week 1)

### Lesson 1.1: History of Cryptography

**Theory:**
- Classical ciphers
- Enigma machine
- Modern cryptography
- Quantum threats

### Lesson 1.2: Mathematical Foundations

**Theory:**
- Number theory
- Modular arithmetic
- Prime numbers
- Discrete logarithm

### Lesson 1.3: Symmetric Encryption

**Theory:**
- Block ciphers (AES, DES, 3DES)
- Stream ciphers (RC4, ChaCha20)
- Modes of operation
- Padding schemes

**Block Cipher Modes:**
| Mode | Description | Parallelizable |
|------|-------------|----------------|
| ECB | Electronic Codebook | Yes |
| CBC | Cipher Block Chaining | Encryption: No |
| CTR | Counter | Yes |
| GCM | Galois/Counter Mode | Yes |

**Practical Lab 1.3.1: Symmetric Encryption**
- Implement AES encryption
- Test different modes
- Analyze security properties

### Lesson 1.4: Asymmetric Encryption

**Theory:**
- RSA
- ECC (Elliptic Curve Cryptography)
- Diffie-Hellman
- Key exchange

**Practical Lab 1.4.1: Asymmetric Encryption**
- Generate key pairs
- Encrypt/decrypt data
- Sign/verify messages

---

## Module 2: Hash Functions (Week 2)

### Lesson 2.1: Hash Algorithms

**Theory:**
- MD5 (broken)
- SHA-1 (deprecated)
- SHA-256
- SHA-3
- BLAKE2

**Hash Comparison:**
| Algorithm | Output Size | Status |
|-----------|-------------|--------|
| MD5 | 128-bit | Broken |
| SHA-1 | 160-bit | Deprecated |
| SHA-256 | 256-bit | Secure |
| SHA-3 | Variable | Secure |

### Lesson 2.2: HMAC and MACs

**Theory:**
- HMAC (Hash-based MAC)
- CMAC
- Poly1305
- Authentication

**Practical Lab 2.2.1: Hash Functions**
- Generate hashes
- Verify integrity
- Implement HMAC

### Lesson 2.3: Password Hashing

**Theory:**
- bcrypt
- scrypt
- Argon2
- Salt and pepper

**Password Hashing Comparison:**
| Algorithm | Features | Recommendation |
|-----------|----------|----------------|
| MD5 | Fast, broken | Never use |
| bcrypt | Salt, work factor | Good |
| scrypt | Memory-hard | Better |
| Argon2 | Memory-hard, winner | Best |

**Practical Lab 2.3.1: Password Security**
- Implement password hashing
- Test cracking resistance
- Analyze security

---

## Module 3: Public Key Infrastructure (Week 3)

### Lesson 3.1: PKI Concepts

**Theory:**
- Digital certificates
- Certificate authorities
- Trust chains
- Certificate revocation

### Lesson 3.2: X.509 Certificates

**Theory:**
- Certificate structure
- Certificate fields
- Extensions
- Validation

### Lesson 3.3: TLS/SSL

**Theory:**
- TLS handshake
- Cipher suites
- Certificate validation
- Forward secrecy

**TLS Handshake:**
```
Client → Server: ClientHello
Server → Client: ServerHello, Certificate
Client → Server: Key Exchange
Server → Client: Finished
Client → Server: Finished
```

**Practical Lab 3.3.1: TLS Implementation**
- Configure TLS
- Test certificate validation
- Analyze handshake

---

## Module 4: Cryptanalysis (Week 4)

### Lesson 4.1: Classical Cryptanalysis

**Theory:**
- Frequency analysis
- Pattern recognition
- Brute force
- Known plaintext

### Lesson 4.2: Modern Cryptanalysis

**Theory:**
- Side-channel attacks
- Padding oracle attacks
- Timing attacks
- Fault injection

**Practical Lab 4.2.1: Cryptanalysis**
- Analyze weak ciphers
- Exploit vulnerabilities
- Break weak implementations

### Lesson 4.3: Hash Attacks

**Theory:**
- Collision attacks
- Preimage attacks
- Length extension
- Rainbow tables

**Practical Lab 4.3.1: Hash Attacks**
- Find collisions
- Use rainbow tables
- Analyze hash security

---

## Module 5: Applied Cryptography (Week 5)

### Lesson 5.1: Email Encryption

**Theory:**
- PGP/GPG
- S/MIME
- Key management
- Digital signatures

### Lesson 5.2: Disk Encryption

**Theory:**
- Full disk encryption
- File encryption
- Key management
- Recovery mechanisms

### Lesson 5.3: Database Encryption

**Theory:**
- Transparent encryption
- Column-level encryption
- Key management
- Performance considerations

**Practical Lab 5.3.1: Encryption Implementation**
- Implement encryption
- Manage keys
- Test security

---

## Module 6: Quantum Cryptography (Week 6)

### Lesson 6.1: Quantum Computing Threats

**Theory:**
- Quantum computers
- Shor's algorithm
- Grover's algorithm
- Post-quantum cryptography

### Lesson 6.2: Post-Quantum Algorithms

**Theory:**
- Lattice-based cryptography
- Code-based cryptography
- Hash-based signatures
- Quantum key distribution

**Practical Lab 6.2.1: Post-Quantum Cryptography**
- Implement post-quantum algorithms
- Test security properties
- Analyze performance

---

## Final Exam

**Duration:** 48 hours
**Format:** Cryptography challenges
**Passing Score:** 70%

### Exam Components
- Encryption challenges (30%)
- Cryptanalysis (30%)
- Implementation (40%)

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
