import { Router } from 'express';
import { SystemController } from '../controllers/SystemController.js';
import { BlockchainService } from '../services/BlockchainService.js';

const router = Router();

// Initialize services and controllers
let systemController: SystemController;

try {
  const blockchainService = new BlockchainService();
  systemController = new SystemController(blockchainService);
} catch (error) {
  console.error('Failed to initialize blockchain service for system routes:', error);
  throw error;
}

/**
 * System Routes
 */

// GET /v1/health - Health check
router.get('/health', (req, res) => systemController.healthCheck(req, res));

// GET /v1/metrics - Prometheus metrics endpoint (JSON format)
router.get('/metrics', (req, res) => systemController.getMetrics(req, res));

// GET /v1/metrics/prometheus - Prometheus-formatted metrics
router.get('/metrics/prometheus', (req, res) => systemController.getPrometheusMetrics(req, res));

export default router;