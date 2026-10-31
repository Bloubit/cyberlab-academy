#!/bin/bash
# CyberLab Academy - Development Startup Script
# Opens two terminal windows: one for backend, one for frontend

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Get script directory (works no matter where the user runs the script from)
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   CyberLab Academy - Dev Startup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if PostgreSQL is running
if ! pg_isready -q 2>/dev/null; then
    echo "Starting PostgreSQL..."
    brew services start postgresql@18 2>/dev/null || echo "Could not start PostgreSQL"
    sleep 2
fi

echo -e "${GREEN}✓${NC} PostgreSQL is running"

# Kill any existing servers on ports 3000 and 8000
kill $(lsof -ti :8000) 2>/dev/null || true
kill $(lsof -ti :3000) 2>/dev/null || true
sleep 1

# Open backend terminal
echo "Starting backend server (port 8000)..."
osascript -e 'tell app "Terminal" to do script "cd '\'"$DIR/backend"\'' && php artisan serve --port=8000; exec bash"'

# Wait for backend to start
echo "Waiting for backend to start..."
sleep 5

# Check if backend is running
if curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Backend is running!"
else
    echo -e "${YELLOW}⚠${NC} Backend may still be starting..."
fi

# Open frontend terminal
echo "Starting frontend server (port 3000)..."
osascript -e 'tell app "Terminal" to do script "cd '\'"$DIR/frontend"\'' && NEXT_PUBLIC_API_URL=http://localhost:8000/api npm run dev; exec bash"'

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Open in browser: http://localhost:3000${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "  Backend API:  http://localhost:8000"
echo "  Frontend:     http://localhost:3000"
echo ""
echo "  Demo Accounts:"
echo "  - Admin:      admin@cyberlab.academy / admin123"
echo "  - Student:    student@cyberlab.academy / student123"
echo ""
echo -e "${YELLOW}ℹ${NC} Frontend may take 5-10 seconds to compile first time"
