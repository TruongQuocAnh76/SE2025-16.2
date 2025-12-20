import { Router, IRouter } from 'express';
import { CertificateController } from '../controllers/CertificateController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router: IRouter = Router();

// Initialize services and controllers
let certificateController: CertificateController | null = null;

function getCertificateController(): CertificateController {
  if (!certificateController) {
    try {
      const blockchainService = new BlockchainService();
      certificateController = new CertificateController(blockchainService);
    } catch (error) {
      console.error('Failed to initialize blockchain service:', error);
      throw error;
    }
  }
  return certificateController;
}

/**
 * Certificate Management Routes
 */

// POST /v1/certs/issue - Issue a certificate
router.post('/issue', (req, res) => getCertificateController().issueCertificate(req, res));

// GET /v1/certs/:certId - Read certificate metadata & on-chain status
router.get('/:certId', (req, res) => getCertificateController().getCertificate(req, res));

// GET /v1/certs/owner/:ownerAddr - List certs for holder (pagination)
router.get('/owner/:ownerAddr', (req, res) => getCertificateController().getCertificatesByOwner(req, res));

// POST /v1/certs/revoke - Revoke a certificate
router.post('/revoke', (req, res) => getCertificateController().revokeCertificate(req, res));

// POST /v1/certs/verify - Verify a presented certificate
router.post('/verify', (req, res) => getCertificateController().verifyCertificate(req, res));

export default router;