# CyberLab Academy - Security Hardening Guide

## Quick Security Fixes

### 1. Backend Security Fixes

#### Fix Rate Limiting (routes/api.php)
```php
// Add to routes/api.php
Route::middleware('throttle:60,1')->group(function () {
    // General API routes
});

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
});
```

#### Fix Password Validation (RegisterRequest.php)
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:100',
        'email' => 'required|email|unique:users,email',
        'password' => [
            'required',
            'string',
            'min:12',
            'max:128',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
        ],
    ];
}
```

#### Add Authorization (LabPolicy.php)
```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Lab;

class LabPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Lab $lab): bool
    {
        return $lab->is_published || 
               $lab->creator_id === $user->id || 
               $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isInstructor() || $user->isAdmin();
    }

    public function update(User $user, Lab $lab): bool
    {
        return $lab->creator_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Lab $lab): bool
    {
        return $lab->creator_id === $user->id || $user->isAdmin();
    }
}
```

---

### 2. Frontend Security Fixes

#### Add Security Headers (next.config.js)
```javascript
/** @type {import('next').NextConfig} */
const nextConfig = {
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          {
            key: 'X-Frame-Options',
            value: 'DENY',
          },
          {
            key: 'X-Content-Type-Options',
            value: 'nosniff',
          },
          {
            key: 'X-XSS-Protection',
            value: '1; mode=block',
          },
          {
            key: 'Referrer-Policy',
            value: 'strict-origin-when-cross-origin',
          },
          {
            key: 'Permissions-Policy',
            value: 'camera=(), microphone=(), geolocation=()',
          },
        ],
      },
    ];
  },
};

module.exports = nextConfig;
```

#### Secure Token Storage (AuthContext.tsx)
```typescript
// Use httpOnly cookies instead of localStorage
// Backend should set cookies with:
// - httponly: true
// - secure: true
// - samesite: 'strict'

// Remove localStorage usage
// localStorage.removeItem('token'); // Remove this
// localStorage.setItem('token', token); // Remove this
```

---

### 3. Docker Security Fixes

#### Secure docker-compose.yml
```yaml
version: '3.8'

services:
  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_PASSWORD_FILE: /run/secrets/db_password
    secrets:
      - db_password
    volumes:
      - postgres-data:/var/lib/postgresql/data
    networks:
      - backend
    # Remove: ports: - "5432:5432"

  redis:
    image: redis:7-alpine
    command: >
      redis-server 
      --requirepass /run/secrets/redis_password
      --maxmemory 2gb
      --maxmemory-policy allkeys-lru
    secrets:
      - redis_password
    networks:
      - backend
    # Remove: ports: - "6379:6379"

  lab-controller:
    image: cyberlab/controller:latest
    security_opt:
      - no-new-privileges:true
    cap_drop:
      - ALL
    cap_add:
      - NET_BIND_SERVICE
    read_only: true
    tmpfs:
      - /tmp:size=100M
    networks:
      - backend
    # Remove: volumes: - /var/run/docker.sock:/var/run/docker.sock

secrets:
  db_password:
    file: ./secrets/db_password.txt
  redis_password:
    file: ./secrets/redis_password.txt

networks:
  frontend:
    driver: overlay
  backend:
    driver: overlay
    internal: true
```

---

### 4. Environment Configuration

#### Create .env file (NEVER commit)
```bash
# Backend .env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATED_KEY_HERE

DB_PASSWORD=STRONG_PASSWORD_HERE
REDIS_PASSWORD=STRONG_PASSWORD_HERE

JWT_SECRET=STRONG_SECRET_HERE
STRIPE_SECRET=sk_live_...
STRIPE_KEY=pk_live_...
```

#### Create secrets directory
```bash
mkdir -p secrets
echo "STRONG_DB_PASSWORD" > secrets/db_password.txt
echo "STRONG_REDIS_PASSWORD" > secrets/redis_password.txt
chmod 600 secrets/*.txt
```

---

### 5. Database Security

#### Enable Row Level Security
```sql
-- Enable RLS on sensitive tables
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE subscriptions ENABLE ROW LEVEL SECURITY;

-- Create policies
CREATE POLICY users_isolation ON users
    FOR ALL
    USING (auth.uid() = id);

CREATE POLICY payments_isolation ON payments
    FOR ALL
    USING (user_id = auth.uid());
```

#### Add Audit Logging
```sql
-- Create audit log table
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id UUID REFERENCES users(id),
    event VARCHAR(100) NOT NULL,
    ip_address INET,
    user_agent TEXT,
    data JSONB,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_audit_logs_user ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_event ON audit_logs(event);
CREATE INDEX idx_audit_logs_created ON audit_logs(created_at);
```

---

### 6. Monitoring Setup

#### Add Security Monitoring
```yaml
# Add to docker-compose.yml
  fail2ban:
    image: crazymax/fail2ban:latest
    network_mode: host
    volumes:
      - ./fail2ban/jail.local:/etc/fail2ban/jail.local
      - ./fail2ban/filter.d:/etc/fail2ban/filter.d
    cap_add:
      - NET_ADMIN
      - NET_RAW

  crowdsec:
    image: crowdsecurity/crowdsec:latest
    environment:
      - COLLECTIONS=crowdsecurity/linux
      - COLLECTIONS=crowdsecurity/apache2
```

---

### 7. Checklist

#### Before Deployment
- [ ] Change all default passwords
- [ ] Generate strong APP_KEY
- [ ] Disable APP_DEBUG in production
- [ ] Enable HTTPS
- [ ] Configure CORS properly
- [ ] Set up rate limiting
- [ ] Enable audit logging
- [ ] Configure backup
- [ ] Set up monitoring
- [ ] Run security scan

#### Weekly
- [ ] Review audit logs
- [ ] Check for security updates
- [ ] Review access logs
- [ ] Monitor failed logins

#### Monthly
- [ ] Rotate secrets
- [ ] Review user accounts
- [ ] Update dependencies
- [ ] Security training

---

## Emergency Response

### If Compromised
1. **Isolate** - Take affected systems offline
2. **Preserve** - Save logs and evidence
3. **Assess** - Determine scope of breach
4. **Contain** - Prevent further damage
5. **Eradicate** - Remove threat
6. **Recover** - Restore from backups
7. **Document** - Record incident
8. **Improve** - Update security measures

### Contact
- Security Team: security@cyberlab.academy
- Emergency: +1-XXX-XXX-XXXX
