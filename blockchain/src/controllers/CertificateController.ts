import { Request, Response } from 'express';
import { BlockchainService } from '../services/BlockchainService.js';
import { 
  IssueCertificateRequest, 
  RevokeCertificateRequest, 
  VerifyCertificateRequest,
  ApiResponse,
  PaginatedResponse
} from '../types/index.js';

export class CertificateController {
  private blockchainService: BlockchainService;

  constructor(blockchainService: BlockchainService) {
    this.blockchainService = blockchainService;
  }

  /**
   * POST /v1/certs/issue - Issue a certificate
   */
  async issueCertificate(req: Request<{}, ApiResponse, IssueCertificateRequest>, res: Response<ApiResponse>) {
    try {
      const {
        certificateNumber,
        studentId,
        studentAddress,
        courseId,
        pdfHash,
        finalScore,
        expiresAt = 0,
        metadata = ''
      } = req.body;

      // Validate required fields
      if (!certificateNumber || !studentId || !studentAddress || !courseId || !pdfHash || finalScore === undefined) {
        return res.status(400).json({
          success: false,
          error: 'Missing required fields: certificateNumber, studentId, studentAddress, courseId, pdfHash, finalScore'
        });
      }

      // Validate Ethereum address format
      if (!/^0x[a-fA-F0-9]{40}$/.test(studentAddress)) {
        return res.status(400).json({
          success: false,
          error: 'Invalid Ethereum address format'
        });
      }

      // Validate final score (should be between 0 and 100)
      if (finalScore < 0 || finalScore > 100) {
        return res.status(400).json({
          success: false,
          error: 'Final score must be between 0 and 100'
        });
      }

      const result = await this.blockchainService.issueCertificate(
        certificateNumber,
        studentId,
        studentAddress,
        courseId,
        pdfHash,
        finalScore,
        expiresAt,
        metadata
      );

      res.status(201).json({
        success: true,
        message: 'Certificate issued successfully',
        data: {
          transactionHash: result.txHash,
          certificate: result.certificate
        }
      });
    } catch (error) {
      console.error('Error in issueCertificate:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }

  /**
   * GET /v1/certs/:certId - Read certificate metadata & on-chain status
   */
  async getCertificate(req: Request<{ certId: string }>, res: Response<ApiResponse>) {
    try {
      const { certId } = req.params;

      if (!certId) {
        return res.status(400).json({
          success: false,
          error: 'Certificate ID is required'
        });
      }

      const certificate = await this.blockchainService.getCertificateByNumber(certId);

      res.json({
        success: true,
        data: certificate
      });
    } catch (error) {
      console.error('Error in getCertificate:', error);
      
      if (error instanceof Error && error.message.includes('Certificate not found')) {
        return res.status(404).json({
          success: false,
          error: 'Certificate not found'
        });
      }

      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }

  /**
   * GET /v1/certs/owner/:ownerAddr - List certs for holder
   */
  async getCertificatesByOwner(req: Request<{ ownerAddr: string }>, res: Response<PaginatedResponse<any>>) {
    try {
      const { ownerAddr } = req.params;
      const page = parseInt(req.query.page as string) || 1;
      const limit = parseInt(req.query.limit as string) || 10;

      if (!ownerAddr) {
        return res.status(400).json({
          success: false,
          error: 'Owner address is required',
          pagination: { page: 1, limit: 10, total: 0, pages: 0 }
        });
      }

      // For now, treat ownerAddr as studentId since that's what we have in the contract
      // In a real implementation, you might want to map addresses to student IDs
      const certificates = await this.blockchainService.getCertificatesByStudentId(ownerAddr);

      // Apply pagination
      const startIndex = (page - 1) * limit;
      const endIndex = startIndex + limit;
      const paginatedCertificates = certificates.slice(startIndex, endIndex);

      res.json({
        success: true,
        data: paginatedCertificates,
        pagination: {
          page,
          limit,
          total: certificates.length,
          pages: Math.ceil(certificates.length / limit)
        }
      });
    } catch (error) {
      console.error('Error in getCertificatesByOwner:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error',
        pagination: { page: 1, limit: 10, total: 0, pages: 0 }
      });
    }
  }

  /**
   * POST /v1/certs/revoke - Revoke a certificate
   */
  async revokeCertificate(req: Request<{}, ApiResponse, RevokeCertificateRequest>, res: Response<ApiResponse>) {
    try {
      const { certificateNumber, reason } = req.body;

      if (!certificateNumber || !reason) {
        return res.status(400).json({
          success: false,
          error: 'Certificate number and reason are required'
        });
      }

      const txHash = await this.blockchainService.revokeCertificate(certificateNumber, reason);

      res.json({
        success: true,
        message: 'Certificate revoked successfully',
        data: {
          transactionHash: txHash
        }
      });
    } catch (error) {
      console.error('Error in revokeCertificate:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }

  /**
   * POST /v1/certs/verify - Verify a presented certificate
   */
  async verifyCertificate(req: Request<{}, ApiResponse, VerifyCertificateRequest>, res: Response<ApiResponse>) {
    try {
      const { certificateNumber, pdfHash } = req.body;

      if (!certificateNumber || !pdfHash) {
        return res.status(400).json({
          success: false,
          error: 'Certificate number and PDF hash are required'
        });
      }

      const verification = await this.blockchainService.verifyCertificate(certificateNumber, pdfHash);

      // If valid, also get the full certificate data
      let certificate = null;
      if (verification.isValid) {
        try {
          certificate = await this.blockchainService.getCertificateByNumber(certificateNumber);
        } catch (error) {
          console.warn('Could not fetch certificate details:', error);
        }
      }

      res.json({
        success: true,
        data: {
          ...verification,
          certificate
        }
      });
    } catch (error) {
      console.error('Error in verifyCertificate:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }
}