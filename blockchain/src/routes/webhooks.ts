import { Router } from 'express';
import { TransactionController } from '../controllers/TransactionController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router = Router();

// Initialize services and controllers
let transactionController: TransactionController;

try {
  const blockchainService = new BlockchainService();
  transactionController = new TransactionController(blockchainService);
} catch (error) {
  console.error('Failed to initialize blockchain service for webhooks:', error);
  throw error;
}

/**
 * Webhook Routes
 */

// POST /v1/webhook/tc - Transaction confirmation webhook/callback
router.post('/tc', (req, res) => transactionController.transactionConfirmationWebhook(req, res));

export default router;