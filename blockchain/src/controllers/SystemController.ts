import { Request, Response } from 'express';
import { BlockchainService } from '../services/BlockchainService.js';
import { HealthStatus, MetricsData, ApiResponse } from '../types/index.js';

export class SystemController {
  private blockchainService: BlockchainService;
  private startTime: number;

  constructor(blockchainService: BlockchainService) {
    this.blockchainService = blockchainService;
    this.startTime = Date.now();
  }

  /**
   * GET /v1/health - Health check
   */
  async healthCheck(req: Request, res: Response<ApiResponse<HealthStatus>>) {
    try {
      let blockchainStatus: 'connected' | 'disconnected' = 'disconnected';
      let contractStatus: 'deployed' | 'not-found' = 'not-found';

      try {
        // Test blockchain connection
        await this.blockchainService.getNetworkInfo();
        blockchainStatus = 'connected';

        // Test contract deployment
        const isAuthorized = await this.blockchainService.isAuthorizedIssuer();
        contractStatus = 'deployed'; // If we can call a method, contract exists
      } catch (error) {
        console.error('Health check failed:', error);
      }

      const healthStatus: HealthStatus = {
        status: blockchainStatus === 'connected' && contractStatus === 'deployed' ? 'ok' : 'error',
        timestamp: Date.now(),
        services: {
          blockchain: blockchainStatus,
          contract: contractStatus
        },
        version: '1.0.0'
      };

      const statusCode = healthStatus.status === 'ok' ? 200 : 503;
      
      res.status(statusCode).json({
        success: healthStatus.status === 'ok',
        data: healthStatus
      });
    } catch (error) {
      console.error('Error in healthCheck:', error);
      
      const healthStatus: HealthStatus = {
        status: 'error',
        timestamp: Date.now(),
        services: {
          blockchain: 'disconnected',
          contract: 'not-found'
        },
        version: '1.0.0'
      };

      res.status(503).json({
        success: false,
        data: healthStatus,
        error: error instanceof Error ? error.message : 'Health check failed'
      });
    }
  }

  /**
   * GET /v1/metrics - Prometheus metrics endpoint
   */
  async getMetrics(req: Request, res: Response<ApiResponse<MetricsData>>) {
    try {
      const networkInfo = await this.blockchainService.getNetworkInfo();
      const totalCertificates = await this.blockchainService.getTotalCertificates();
      
      // Note: We don't have a direct way to count revoked certificates from the contract
      // This would need to be tracked separately or by parsing events
      const totalRevoked = 0; // Placeholder

      const metrics: MetricsData = {
        totalCertificatesIssued: totalCertificates,
        totalCertificatesRevoked: totalRevoked,
        contractAddress: this.blockchainService.getContractAddressInfo(),
        networkName: networkInfo.name,
        blockNumber: networkInfo.blockNumber,
        uptime: Math.floor((Date.now() - this.startTime) / 1000) // seconds
      };

      res.json({
        success: true,
        data: metrics
      });
    } catch (error) {
      console.error('Error in getMetrics:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }

  /**
   * GET /v1/metrics/prometheus - Prometheus-formatted metrics
   */
  async getPrometheusMetrics(req: Request, res: Response) {
    try {
      const networkInfo = await this.blockchainService.getNetworkInfo();
      const totalCertificates = await this.blockchainService.getTotalCertificates();
      const uptime = Math.floor((Date.now() - this.startTime) / 1000);

      // Prometheus format metrics
      const prometheusMetrics = [
        '# HELP blockchain_certificates_issued_total Total number of certificates issued',
        '# TYPE blockchain_certificates_issued_total counter',
        `blockchain_certificates_issued_total{network="${networkInfo.name}"} ${totalCertificates}`,
        '',
        '# HELP blockchain_service_uptime_seconds Service uptime in seconds',
        '# TYPE blockchain_service_uptime_seconds gauge',
        `blockchain_service_uptime_seconds ${uptime}`,
        '',
        '# HELP blockchain_current_block_number Current block number',
        '# TYPE blockchain_current_block_number gauge',
        `blockchain_current_block_number{network="${networkInfo.name}"} ${networkInfo.blockNumber}`,
        '',
        '# HELP blockchain_service_info Service information',
        '# TYPE blockchain_service_info gauge',
        `blockchain_service_info{version="1.0.0",network="${networkInfo.name}",contract="${this.blockchainService.getContractAddressInfo()}"} 1`,
        ''
      ].join('\n');

      res.setHeader('Content-Type', 'text/plain');
      res.send(prometheusMetrics);
    } catch (error) {
      console.error('Error in getPrometheusMetrics:', error);
      res.status(500).send('# Error generating metrics\n');
    }
  }
}