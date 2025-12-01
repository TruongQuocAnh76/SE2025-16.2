import { Router } from 'express';
import { CertificateController } from '../controllers/CertificateController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router = Router();

// Initialize services and controllers
let certificateController: CertificateController;

try {
  const blockchainService = new BlockchainService();
  certificateController = new CertificateController(blockchainService);
} catch (error) {
  console.error('Failed to initialize blockchain service:', error);
  throw error;
}

/**
 * Certificate Management Routes
 */

// POST /v1/certs/issue - Issue a certificate
router.post('/issue', (req, res) => certificateController.issueCertificate(req, res));

// GET /v1/certs/:certId - Read certificate metadata & on-chain status
router.get('/:certId', (req, res) => certificateController.getCertificate(req, res));

// GET /v1/certs/owner/:ownerAddr - List certs for holder (pagination)
router.get('/owner/:ownerAddr', (req, res) => certificateController.getCertificatesByOwner(req, res));

// POST /v1/certs/revoke - Revoke a certificate
router.post('/revoke', (req, res) => certificateController.revokeCertificate(req, res));

// POST /v1/certs/verify - Verify a presented certificate
router.post('/verify', (req, res) => certificateController.verifyCertificate(req, res));

export default router;