# CyberLab Academy - Frontend

Futuristic cybersecurity learning platform built with Next.js, TypeScript, and TailwindCSS.

## Tech Stack

- **Next.js 15** - React framework
- **TypeScript** - Type safety
- **TailwindCSS** - Styling
- **Shadcn UI** - Components
- **Framer Motion** - Animations
- **Zustand** - State management
- **React Hook Form** - Forms
- **Zod** - Validation
- **Axios** - API client

## Features

- Cyberpunk/neon design theme
- Dark mode optimized
- Glass morphism effects
- Smooth animations
- Responsive design
- Terminal interface for labs
- QR code certificates
- Real-time notifications

## Quick Start

### 1. Install Dependencies

```bash
cd frontend
npm install
```

### 2. Environment Setup

```bash
cp .env.example .env.local
```

Edit `.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### 3. Run Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000)

## Project Structure

```
frontend/
├── src/
│   ├── app/                    # Next.js App Router
│   │   ├── auth/               # Authentication pages
│   │   │   ├── login/
│   │   │   └── register/
│   │   ├── dashboard/          # Student dashboard
│   │   ├── courses/            # Course pages
│   │   │   ├── [id]/
│   │   │   └── lab/[id]/
│   │   ├── ctf/                # CTF challenges
│   │   ├── profile/            # User profile
│   │   ├── certificates/       # Certificate viewer
│   │   └── leaderboard/        # Global rankings
│   ├── components/
│   │   ├── ui/                 # Reusable UI components
│   │   │   ├── Button.tsx
│   │   │   ├── Card.tsx
│   │   │   ├── Input.tsx
│   │   │   ├── Badge.tsx
│   │   │   ├── Avatar.tsx
│   │   │   └── Progress.tsx
│   │   ├── layout/             # Layout components
│   │   │   ├── Navbar.tsx
│   │   │   └── Footer.tsx
│   │   └── landing/            # Landing page
│   │       └── LandingPage.tsx
│   ├── context/                # React Context
│   │   └── AuthContext.tsx
│   ├── hooks/                  # Custom hooks
│   │   ├── useNotifications.ts
│   │   └── useMediaQuery.ts
│   ├── lib/                    # Utilities
│   │   ├── api.ts              # API client
│   │   └── utils.ts            # Helper functions
│   ├── styles/                 # Global styles
│   │   └── globals.css
│   └── types/                  # TypeScript types
│       └── index.ts
├── public/                     # Static assets
├── tailwind.config.ts          # Tailwind configuration
├── tsconfig.json               # TypeScript configuration
└── package.json
```

## Pages

### Landing Page (`/`)
- Hero section with animated background
- Features grid
- Course previews
- Testimonials
- Call to action

### Authentication
- **Login** (`/auth/login`)
- **Register** (`/auth/register`)

### Dashboard (`/dashboard`)
- User stats (XP, Level, Courses)
- Progress tracking
- Active labs
- Recent activity
- Certificates

### Courses (`/courses`)
- Course catalog
- Course detail (`/courses/[id]`)
- Module browser
- Lesson viewer

### Labs (`/courses/lab/[id]`)
- Machine info
- Terminal interface
- Challenge cards
- Flag submission
- Hints system

### CTF (`/ctf`)
- Challenge browser
- Category filters
- Difficulty filters
- Leaderboard widget

### Profile (`/profile`)
- User info
- Skills radar
- Badges collection
- Statistics
- Recent activity

### Leaderboard (`/leaderboard`)
- Global rankings
- Podium display
- Your position
- Period filters

### Certificate (`/certificates/[id]`)
- Certificate viewer
- QR code
- Download PDF
- Verification

## Design System

### Colors
- **Neon Blue**: `#00d4ff` - Primary accent
- **Neon Purple**: `#9945ff` - Secondary accent
- **Neon Green**: `#14ffec` - Success/terminal
- **Neon Pink**: `#ff00ff` - Highlight

### Components
- `.cyber-card` - Glowing card effect
- `.neon-border` - Neon border glow
- `.neon-text` - Glowing text
- `.glass-effect` - Glass morphism
- `.terminal` - Terminal styling

### Animations
- `animate-glow` - Pulsing glow effect
- `animate-float` - Floating animation
- `animate-scan-line` - Scan line effect
- `animate-typing` - Typewriter effect

## API Integration

The frontend connects to the Laravel backend API:

```typescript
// src/lib/api.ts
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL;

// Features:
// - Token-based authentication
// - Automatic token refresh
// - Request/response interceptors
// - Error handling
```

## Building for Production

```bash
npm run build
npm start
```

## Docker

```bash
docker build -t cyberlab-frontend .
docker run -p 3000:3000 cyberlab-frontend
```

## License

Proprietary - CyberLab Academy
