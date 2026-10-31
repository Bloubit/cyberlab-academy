#!/bin/bash
# CyberLab Academy - Simple Monitoring Dashboard

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

clear

echo -e "${BLUE}=========================================="
echo "   CyberLab Academy - System Monitor"
echo "==========================================${NC}"
echo ""

# System Resources
echo -e "${BLUE}=== System Resources ===${NC}"
echo ""

# CPU Usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}')
if (( $(echo "$CPU_USAGE > 80" | bc -l) )); then
    echo -e "CPU:    ${RED}${CPU_USAGE}%${NC}"
else
    echo -e "CPU:    ${GREEN}${CPU_USAGE}%${NC}"
fi

# Memory Usage
MEMORY_INFO=$(free -h | awk '/Mem:/ {print $3 " / " $2}')
MEMORY_PERCENT=$(free | awk '/Mem:/ {printf "%.1f", $3/$2 * 100}')
if (( $(echo "$MEMORY_PERCENT > 80" | bc -l) )); then
    echo -e "Memory: ${RED}${MEMORY_INFO} (${MEMORY_PERCENT}%)${NC}"
else
    echo -e "Memory: ${GREEN}${MEMORY_INFO} (${MEMORY_PERCENT}%)${NC}"
fi

# Disk Usage
DISK_INFO=$(df -h / | awk 'NR==2 {print $3 " / " $2 " (" $5 ")"}')
DISK_PERCENT=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_PERCENT" -gt 80 ]; then
    echo -e "Disk:   ${RED}${DISK_INFO}${NC}"
else
    echo -e "Disk:   ${GREEN}${DISK_INFO}${NC}"
fi

# Uptime
UPTIME=$(uptime -p)
echo -e "Uptime: ${UPTIME}"

echo ""
echo -e "${BLUE}=== Docker Containers ===${NC}"
echo ""

# Container Status
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | head -20

echo ""
echo -e "${BLUE}=== Service Health ===${NC}"
echo ""

# Check Frontend
if curl -sf http://localhost:3000/api/health > /dev/null 2>&1; then
    echo -e "Frontend: ${GREEN}✓ Healthy${NC}"
else
    echo -e "Frontend: ${RED}✗ Down${NC}"
fi

# Check Backend
if curl -sf http://localhost:8000/api/health > /dev/null 2>&1; then
    echo -e "Backend:  ${GREEN}✓ Healthy${NC}"
else
    echo -e "Backend:  ${RED}✗ Down${NC}"
fi

# Check Nginx
if curl -sf http://localhost:8080/nginx-health > /dev/null 2>&1; then
    echo -e "Nginx:    ${GREEN}✓ Healthy${NC}"
else
    echo -e "Nginx:    ${RED}✗ Down${NC}"
fi

# Check PostgreSQL
if docker exec cyberlab-postgres pg_isready -U cyberlab > /dev/null 2>&1; then
    echo -e "Postgres: ${GREEN}✓ Healthy${NC}"
else
    echo -e "Postgres: ${RED}✗ Down${NC}"
fi

# Check Redis
if docker exec cyberlab-redis redis-cli ping > /dev/null 2>&1; then
    echo -e "Redis:    ${GREEN}✓ Healthy${NC}"
else
    echo -e "Redis:    ${RED}✗ Down${NC}"
fi

echo ""
echo -e "${BLUE}=== Recent Errors ===${NC}"
echo ""

# Show recent errors from logs
docker logs --tail 5 cyberlab-backend 2>&1 | grep -i "error" || echo "No recent errors"

echo ""
echo -e "${BLUE}=== Network Connections ===${NC}"
echo ""

# Show active connections
ss -tuln | grep -E ":(80|443|3000|8000|5432|6379)" | head -10

echo ""
echo -e "${BLUE}=========================================="
echo "   Press Ctrl+C to exit"
echo "==========================================${NC}"
