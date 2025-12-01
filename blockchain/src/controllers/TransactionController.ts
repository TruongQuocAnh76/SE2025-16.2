import { Request, Response } from 'express';
import { BlockchainService } from '../services/BlockchainService.js';
import { ApiResponse } from '../types/index.js';

export class TransactionController {
  private blockchainService: BlockchainService;

  constructor(blockchainService: BlockchainService) {
    this.blockchainService = blockchainService;
  }

  /**
   * GET /v1/tx/:txHash - Get transaction status / confirmations
   */
  async getTransactionStatus(req: Request<{ txHash: string }>, res: Response<ApiResponse>) {
    try {
      const { txHash } = req.params;

      if (!txHash) {
        return res.status(400).json({
          success: false,
          error: 'Transaction hash is required'
        });
      }

      // Validate transaction hash format
      if (!/^0x[a-fA-F0-9]{64}$/.test(txHash)) {
        return res.status(400).json({
          success: false,
          error: 'Invalid transaction hash format'
        });
      }

      const status = await this.blockchainService.getTransactionStatus(txHash);

      res.json({
        success: true,
        data: status
      });
    } catch (error) {
      console.error('Error in getTransactionStatus:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }

  /**
   * POST /v1/webhook/tc - Transaction confirmation webhook/callback
   * This would be called by external services when transactions are confirmed
   */
  async transactionConfirmationWebhook(req: Request, res: Response<ApiResponse>) {
    try {
      const { transactionHash, blockNumber, confirmations, status } = req.body;

      // Validate webhook payload
      if (!transactionHash || !blockNumber || confirmations === undefined || !status) {
        return res.status(400).json({
          success: false,
          error: 'Invalid webhook payload'
        });
      }

      // Here you would typically:
      // 1. Verify the webhook signature to ensure it's from a trusted source
      // 2. Update your database with the transaction confirmation
      // 3. Trigger any necessary business logic (e.g., send notifications)

      console.log('Transaction confirmation received:', {
        transactionHash,
        blockNumber,
        confirmations,
        status
      });

      // For now, just acknowledge receipt
      res.json({
        success: true,
        message: 'Transaction confirmation received'
      });
    } catch (error) {
      console.error('Error in transactionConfirmationWebhook:', error);
      res.status(500).json({
        success: false,
        error: error instanceof Error ? error.message : 'Internal server error'
      });
    }
  }
}