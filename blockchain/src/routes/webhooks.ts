import { Router, IRouter } from 'express';
import { TransactionController } from '../controllers/TransactionController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router: IRouter = Router();

// Initialize services and controllers
let transactionController: TransactionController | null = null;

function getTransactionController(): TransactionController {
  if (!transactionController) {
    try {
      const blockchainService = new BlockchainService();
      transactionController = new TransactionController(blockchainService);
    } catch (error) {
      console.error('Failed to initialize blockchain service for webhooks:', error);
      throw error;
    }
  }
  return transactionController;
}

/**
 * Webhook Routes
 */

// POST /v1/webhook/tc - Transaction confirmation webhook/callback
router.post('/tc', (req, res) => getTransactionController().transactionConfirmationWebhook(req, res));

export default router;