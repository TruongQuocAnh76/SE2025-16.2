import { Router, IRouter } from 'express';
import { SystemController } from '../controllers/SystemController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router: import('express').Router = Router();

// Initialize services and controllers
let systemController: SystemController | null = null;

function getSystemController(): SystemController {
  if (!systemController) {
    try {
      const blockchainService = new BlockchainService();
      systemController = new SystemController(blockchainService);
    } catch (error) {
      console.error('Failed to initialize blockchain service for system routes:', error);
      throw error;
    }
  }
  return systemController;
}

/**
 * System Routes
 */

// GET /v1/health - Health check
router.get('/health', (req, res) => getSystemController().healthCheck(req, res));

// GET /v1/metrics - Prometheus metrics endpoint (JSON format)
router.get('/metrics', (req, res) => getSystemController().getMetrics(req, res));

// GET /v1/metrics/prometheus - Prometheus-formatted metrics
router.get('/metrics/prometheus', (req, res) => getSystemController().getPrometheusMetrics(req, res));

export default router;