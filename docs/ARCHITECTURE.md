# Architecture Overview

This document describes the system architecture of **Certchain**, an online learning platform with blockchain-based certificate verification.

## System Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              EXTERNAL USERS                                 │
│                    (Students, Teachers, Administrators)                     │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                            FRONTEND (Port 3000)                             │
│                                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │   Landing   │  │    Auth     │  │   Courses   │  │     Verification    │ │
│  │   Domain    │  │   Domain    │  │   Domain    │  │       Domain        │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────────────┘ │
│                                                                             │
│  Stack: Nuxt 3, Vue.js, Tailwind CSS, TypeScript                           │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                                      ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                        NGINX REVERSE PROXY (Port 8000)                      │
│                     Load Balancing, SSL Termination                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                      │
                ┌─────────────────────┼─────────────────────┐
                ▼                     ▼                     ▼
┌───────────────────────┐ ┌───────────────────────┐ ┌───────────────────────┐
│   BACKEND API (PHP)   │ │  QUEUE WORKER (PHP)   │ │ RABBITMQ WORKER (PHP) │
│                       │ │                       │ │                       │
│   Laravel 11          │ │   Background Jobs     │ │   Email Processing    │
│   RESTful API         │ │   Redis Queue         │ │   Async Messaging     │
│   Sanctum Auth        │ │   Video Processing    │ │                       │
│   Eloquent ORM        │ │   Blockchain Jobs     │ │                       │
└───────────────────────┘ └───────────────────────┘ └───────────────────────┘
         │                         │                         │
         └─────────────────────────┼─────────────────────────┘
                                   ▼
         ┌─────────────────────────────────────────────────────┐
         │                    DATA LAYER                       │
         │                                                     │
         │  ┌──────────────┐  ┌──────────────┐  ┌───────────┐  │
         │  │  PostgreSQL  │  │    Redis     │  │  RabbitMQ │  │
         │  │   Port 5432  │  │  Port 6379   │  │ Port 5672 │  │
         │  │              │  │              │  │           │  │
         │  │  User Data   │  │  Sessions    │  │  Message  │  │
         │  │  Courses     │  │  Cache       │  │  Queue    │  │
         │  │  Certs       │  │  Queues      │  │  (Email)  │  │
         │  └──────────────┘  └──────────────┘  └───────────┘  │
         └─────────────────────────────────────────────────────┘
                                   │
                                   ▼
         ┌─────────────────────────────────────────────────────┐
         │                  STORAGE LAYER                      │
         │                                                     │
         │  ┌────────────────────────────────────────────────┐ │
         │  │              MinIO (S3-Compatible)             │ │
         │  │              Ports 9000, 9001, 9002            │ │
         │  │                                                │ │
         │  │  • Video Lessons (HLS Streaming)               │ │
         │  │  • Course Thumbnails                           │ │
         │  │  • Certificate PDFs                            │ │
         │  │  • User Avatars                                │ │
         │  └────────────────────────────────────────────────┘ │
         └─────────────────────────────────────────────────────┘
                                   │
                                   ▼
         ┌─────────────────────────────────────────────────────┐
         │              BLOCKCHAIN LAYER (Port 3001)           │
         │                                                     │
         │  ┌────────────────────────────────────────────────┐ │
         │  │         Blockchain Certificate Service         │ │
         │  │              Node.js + Express                 │ │
         │  │                                                │ │
         │  │  • Certificate Issuance API                    │ │
         │  │  • Certificate Verification                    │ │
         │  │  • Transaction Monitoring                      │ │
         │  └────────────────────────────────────────────────┘ │
         │                         │                           │
         │                         ▼                           │
         │  ┌────────────────────────────────────────────────┐ │
         │  │          Hardhat Node (Port 8545)              │ │
         │  │              Local Blockchain                  │ │
         │  │                                                │ │
         │  │  • Smart Contracts (Solidity)                  │ │
         │  │  • Certificate Storage                         │ │
         │  │  • Immutable Records                           │ │
         │  └────────────────────────────────────────────────┘ │
         └─────────────────────────────────────────────────────┘
```

## Services Overview

### 1. Frontend (Nuxt 3)

**Technology Stack:**
- Nuxt 3 (Vue.js framework)
- TypeScript
- Tailwind CSS
- Domain-driven architecture

**Domains:**
| Domain | Purpose |
|--------|---------|
| `landing` | Homepage, membership, about pages |
| `auth` | Login, registration, password reset, teacher applications |
| `courses` | Course browsing, learning, quizzes |
| `payment` | Stripe/PayPal payment processing |
| `verification` | Public certificate verification |
| `base` | Shared components, admin panel, settings |

**Key Features:**
- Server-side rendering (SSR) for SEO
- Hot module replacement in development
- Presigned URL uploads for media

### 2. Backend API (Laravel 11)

**Technology Stack:**
- PHP 8.2+
- Laravel 11
- Laravel Sanctum (API authentication)
- Eloquent ORM

**Architecture Layers:**
```
┌─────────────────────────────────────────┐
│            HTTP Controllers             │
│    (Request validation, responses)      │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│              Services                   │
│    (Business logic, orchestration)      │
│                                         │
│  AuthService, CourseService,            │
│  CertificateService, PaymentService,    │
│  BlockchainService, GradingService...   │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│            Repositories                 │
│    (Data access abstraction)            │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│           Eloquent Models               │
│    (Database ORM mapping)               │
└─────────────────────────────────────────┘
```

**API Endpoints Categories:**
- `/api/auth/*` - Authentication (login, register, OAuth)
- `/api/users/*` - User management
- `/api/courses/*` - Course CRUD and enrollment
- `/api/lessons/*` - Lesson content
- `/api/quizzes/*` - Quiz management
- `/api/certificates/*` - Certificate issuance
- `/api/payments/*` - Payment processing
- `/api/admin/*` - Admin dashboard
- `/api/system/*` - System logs and cache

### 3. Blockchain Service (Node.js)

**Technology Stack:**
- Node.js 20+
- Express.js
- Hardhat (Ethereum development)
- Ethers.js
- Solidity smart contracts

**API Endpoints:**
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/certs/issue` | POST | Issue certificate to blockchain |
| `/v1/certs/:certId` | GET | Get certificate details |
| `/v1/certs/verify` | POST | Verify certificate hash |
| `/v1/certs/revoke` | POST | Revoke a certificate |
| `/v1/tx/:txHash` | GET | Get transaction status |
| `/v1/health` | GET | Health check |
| `/v1/metrics` | GET | Prometheus metrics |

**Supported Networks:**
- Hardhat (local development)
- Polygon
- Ethereum
- Sepolia (testnet)

### 4. Queue Workers

**Redis Queue Worker:**
- Processes background jobs
- Video HLS transcoding
- Blockchain certificate issuance
- Configured with 3 retries, 1-hour timeout

**RabbitMQ Worker:**
- Email processing (password reset, notifications)
- Decoupled from main application flow

## Data Flow

### Certificate Issuance Flow

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌────────────┐
│  Student │───▶│  Backend │───▶│  Queue   │───▶│ Blockchain │
│ Complete │    │   API    │    │  Worker  │    │  Service   │
│  Course  │    │          │    │          │    │            │
└──────────┘    └──────────┘    └──────────┘    └────────────┘
                     │                               │
                     │         ┌──────────┐          │
                     └────────▶│ Database │◀─────────┘
                               │ (Update) │
                               └──────────┘
                                     │
                     ┌───────────────┘
                     ▼
              ┌────────────┐
              │   MinIO    │
              │ (PDF file) │
              └────────────┘

1. Student completes course → triggers certificate request
2. Backend validates eligibility, generates PDF, stores in MinIO
3. Job queued for blockchain issuance
4. Queue worker calls blockchain service
5. Smart contract stores certificate hash
6. Database updated with blockchain transaction details
```

## Database Schema

See the full database schema and ERD in: [docs/DATABASE_SCHEMA.md](docs/DATABASE_SCHEMA.md)

This file contains the complete entity relationship diagram, table descriptions, indexes, and migration commands used by the platform.

### Authentication Flow

```
┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│  Client  │───▶│ Frontend │───▶│  Backend │───▶│   Redis  │
│ (Login)  │    │          │    │  Sanctum │    │ (Session)│
└──────────┘    └──────────┘    └──────────┘    └──────────┘
                                      │
                                      ▼
                               ┌────────────┐
                               │ PostgreSQL │
                               │  (Users)   │
                               └────────────┘

Supported Auth Methods:
- Email/Password
- Google OAuth
- Facebook OAuth
```

### Payment Flow

```
┌──────────┐    ┌──────────┐    ┌────────────┐
│  Client  │───▶│ Frontend │───▶│  Stripe/   │
│          │    │          │    │  PayPal    │
└──────────┘    └──────────┘    └────────────┘
                     │                │
                     ▼                ▼
              ┌──────────┐    ┌────────────┐
              │  Backend │◀───│  Webhook   │
              │   API    │    │ (Confirm)  │
              └──────────┘    └────────────┘
                     │
                     ▼
              ┌────────────┐
              │ PostgreSQL │
              │ (Payments) │
              └────────────┘

1. Client initiates payment
2. Backend creates payment intent
3. Client completes payment on Stripe/PayPal
4. Webhook confirms payment
5. Enrollment activated
```

## Environment Configuration

### Service Ports (Development)

| Service | Port | Purpose |
|---------|------|---------|
| Frontend | 3000 | Nuxt development server |
| Backend (nginx) | 8000 | API reverse proxy |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache/Queue |
| RabbitMQ | 5672 | Message queue |
| RabbitMQ UI | 15672 | Management console |
| MinIO API | 9000 | S3 API |
| MinIO Console | 9001 | Web UI |
| MinIO Proxy | 9002 | Nginx proxy for presigned URLs |
| Blockchain Service | 3001 | Certificate API |
| Hardhat Node | 8545 | Local blockchain |

### Docker Networks

All services communicate via the `app_network` bridge network, allowing inter-service communication using service names as hostnames.

## Scalability Considerations

1. **Horizontal Scaling:**
   - Backend and queue workers can be scaled independently
   - Nginx handles load balancing

2. **Database:**
   - PostgreSQL with connection pooling
   - Redis for session/cache offloading

3. **Storage:**
   - MinIO can be replaced with AWS S3 in production
   - CDN integration for video streaming

4. **Blockchain:**
   - Configurable network (local/testnet/mainnet)
   - Contract deployment per network
