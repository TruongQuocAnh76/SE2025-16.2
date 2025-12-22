// API Request Types
export interface IssueCertificateRequest {
  certificateNumber: string;
  studentId: string;
  studentAddress: string;
  courseId: string;
  pdfHash: string;
  finalScore: number;
  expiresAt?: number;
  metadata?: string;
}

export interface RevokeCertificateRequest {
  certificateNumber: string;
  reason: string;
}

export interface VerifyCertificateRequest {
  certificateNumber: string;
  pdfHash: string;
}

// API Response Types
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  error?: string;
}

export interface PaginatedResponse<T = any> {
  success: boolean;
  data: T[];
  error?: string;
  pagination: {
    page: number;
    limit: number;
    total: number;
    pages: number;
  };
}

// Certificate Types
export interface Certificate {
  certificateNumber: string;
  studentId: string;
  courseId: string;
  pdfHash: string;
  finalScore: number;
  issuedAt: number;
  expiresAt: number;
  isRevoked: boolean;
  revocationReason: string;
  metadata: string;
}

// Transaction Types
export interface TransactionStatus {
  hash?: string;
  transactionHash?: string;
  status: 'pending' | 'confirmed' | 'failed';
  confirmations: number;
  blockNumber?: number;
  gasUsed?: string;
  effectiveGasPrice?: string;
}

// System Types
export interface HealthStatus {
  status: 'healthy' | 'unhealthy' | 'ok' | 'error';
  timestamp: number;
  uptime?: number;
  services?: {
    blockchain: 'connected' | 'disconnected';
    contract: 'deployed' | 'not-found';
  };
  blockchain?: {
    network: string;
    status: 'connected' | 'disconnected';
  };
  contract?: {
    address: string;
    status: 'deployed' | 'not-found';
  };
  version?: string;
}

export interface MetricsData {
  totalCertificatesIssued: number;
  totalCertificatesRevoked?: number;
  totalRevocations?: number;
  requestsProcessed?: number;
  averageResponseTime?: number;
  contractAddress?: string;
  networkName?: string;
  blockNumber?: number;
  uptime?: number;
  blockchain?: {
    network: string;
    blockNumber: number;
    gasPrice: string;
  };
}
