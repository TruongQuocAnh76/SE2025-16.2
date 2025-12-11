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
  console.error('Failed to initialize blockchain service for transactions:', error);
  throw error;
}

/**
 * Transaction Routes
 */

// GET /v1/tx/:txHash - Get transaction status / confirmations
router.get('/:txHash', (req, res) => transactionController.getTransactionStatus(req, res));

export default router;