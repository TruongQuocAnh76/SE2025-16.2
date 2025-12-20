import express, { Express } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import compression from 'compression';
import rateLimit from 'express-rate-limit';
import dotenv from 'dotenv';

// Import routes
import certificateRoutes from './routes/certificates.js';
import transactionRoutes from './routes/transactions.js';
import webhookRoutes from './routes/webhooks.js';
import systemRoutes from './routes/system.js';

// Load environment variables
dotenv.config();

const app: Express = express();
const PORT = process.env.PORT || 3001;

// Security middleware
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      scriptSrc: ["'self'"],
      imgSrc: ["'self'", "data:", "https:"],
    },
  },
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: {
    success: false,
    error: 'Too many requests from this IP, please try again later.'
  },
  standardHeaders: true,
  legacyHeaders: false,
});

app.use(limiter);

// CORS configuration
const corsOptions = {
  origin: (origin: string | undefined, callback: (error: Error | null, allow?: boolean) => void) => {
    const allowedOrigins = process.env.ALLOWED_ORIGINS?.split(',') || ['http://localhost:3000', 'http://localhost:8000'];
    
    // Allow requests with no origin (like mobile apps or curl requests)
    if (!origin) return callback(null, true);
    
    if (allowedOrigins.indexOf(origin) !== -1) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  credentials: true,
  optionsSuccessStatus: 200
};

app.use(cors(corsOptions));

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Compression middleware
app.use(compression());

// Logging middleware
if (process.env.NODE_ENV !== 'production') {
  app.use(morgan('dev'));
} else {
  app.use(morgan('combined'));
}

// API Routes
app.use('/v1/certs', certificateRoutes);
app.use('/v1/tx', transactionRoutes);
app.use('/v1/webhook', webhookRoutes);

// System routes (health, metrics) - these don't need versioning
app.use('/v1', systemRoutes);

// Root endpoint
app.get('/', (req, res) => {
  res.json({
    success: true,
    message: 'Blockchain Certificate Service API',
    version: '1.0.0',
    endpoints: {
      certificates: {
        'POST /v1/certs/issue': 'Issue a certificate',
        'GET /v1/certs/:certId': 'Get certificate details',
        'GET /v1/certs/owner/:ownerAddr': 'List certificates by owner',
        'POST /v1/certs/revoke': 'Revoke a certificate',
        'POST /v1/certs/verify': 'Verify a certificate'
      },
      transactions: {
        'GET /v1/tx/:txHash': 'Get transaction status'
      },
      webhooks: {
        'POST /v1/webhook/tc': 'Transaction confirmation webhook'
      },
      system: {
        'GET /v1/health': 'Health check',
        'GET /v1/metrics': 'Service metrics (JSON)',
        'GET /v1/metrics/prometheus': 'Prometheus metrics'
      }
    }
  });
});

// Error handling middleware
app.use((err: Error, req: express.Request, res: express.Response, next: express.NextFunction) => {
  console.error('Unhandled error:', err);
  
  res.status(500).json({
    success: false,
    error: process.env.NODE_ENV === 'production' 
      ? 'Internal server error' 
      : err.message
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    error: 'Endpoint not found'
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 Blockchain Certificate Service running on port ${PORT}`);
  console.log(`🌍 Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`🔗 Network: ${process.env.BLOCKCHAIN_NETWORK || 'hardhat'}`);
  console.log(`📋 Health check: http://localhost:${PORT}/v1/health`);
});

// Handle graceful shutdown
process.on('SIGTERM', () => {
  console.log('SIGTERM received, shutting down gracefully');
  process.exit(0);
});

process.on('SIGINT', () => {
  console.log('SIGINT received, shutting down gracefully');
  process.exit(0);
});

export default app;