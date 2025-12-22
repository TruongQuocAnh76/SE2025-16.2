import { ethers } from 'ethers';
import { Certificate, TransactionStatus } from '../types/index.js';

export class BlockchainService {
  private provider: ethers.Provider;
  private signer: ethers.Signer;
  private contract: ethers.Contract;
  private contractAddress: string;

  // Certificate Registry ABI (minimal required methods)
  private static readonly CONTRACT_ABI = [
    'function issueCertificate(string memory _certificateNumber, string memory _studentId, address _studentAddress, string memory _courseId, string memory _pdfHash, uint256 _finalScore, uint256 _expiresAt, string memory _metadata) external',
    'function revokeCertificate(string memory _certificateNumber, string memory _reason) external',
    'function getCertificateByCertNumber(string memory _certificateNumber) external view returns (tuple(string certificateNumber, string studentId, string courseId, string pdfHash, uint256 finalScore, uint256 issuedAt, uint256 expiresAt, bool isRevoked, string revocationReason, string metadata))',
    'function getCertificatesByStudentId(string memory _studentId) external view returns (tuple(string certificateNumber, string studentId, string courseId, string pdfHash, uint256 finalScore, uint256 issuedAt, uint256 expiresAt, bool isRevoked, string revocationReason, string metadata)[])',
    'function verifyCertificate(string memory _certificateNumber, string memory _pdfHash) external view returns (bool isValid, bool isRevoked, uint256 issuedAt, uint256 expiresAt)',
    'function getTotalCertificates() external view returns (uint256)',
    'function isAuthorizedIssuer(address _issuer) external view returns (bool)',
    'function owner() external view returns (address)',
    'event CertificateIssued(uint256 indexed certificateId, string indexed certificateNumber, string indexed studentId, address studentAddress, string courseId, uint256 finalScore, string pdfHash)',
    'event CertificateRevoked(uint256 indexed certificateId, string indexed certificateNumber, string reason)'
  ];

  constructor() {
    const networkName = process.env.BLOCKCHAIN_NETWORK || 'hardhat';
    const privateKey = process.env.PRIVATE_KEY;

    if (!privateKey) {
      throw new Error('PRIVATE_KEY environment variable is required');
    }

    // Initialize provider based on network
    this.provider = this.initializeProvider(networkName);
    this.signer = new ethers.Wallet(privateKey, this.provider);
    
    // Get contract address for current network
    this.contractAddress = this.getContractAddress(networkName);
    
    if (!this.contractAddress) {
      throw new Error(`Contract address not configured for network: ${networkName}`);
    }

    // Initialize contract instance
    this.contract = new ethers.Contract(
      this.contractAddress,
      BlockchainService.CONTRACT_ABI,
      this.signer
    );
  }

  private initializeProvider(networkName: string): ethers.Provider {
    switch (networkName) {
      case 'hardhat':
      case 'localhost':
        return new ethers.JsonRpcProvider(process.env.HARDHAT_RPC_URL || 'http://localhost:8545');
      case 'polygon':
        return new ethers.JsonRpcProvider(process.env.POLYGON_RPC_URL);
      case 'polygonMumbai':
        return new ethers.JsonRpcProvider(process.env.POLYGON_MUMBAI_RPC_URL);
      case 'mainnet':
        return new ethers.JsonRpcProvider(process.env.ETHEREUM_RPC_URL);
      case 'sepolia':
        return new ethers.JsonRpcProvider(process.env.SEPOLIA_RPC_URL);
      default:
        throw new Error(`Unsupported network: ${networkName}`);
    }
  }

  private getContractAddress(networkName: string): string {
    return process.env.CERTIFICATE_CONTRACT || '';
  }

  /**
   * Issue a new certificate on the blockchain
   */
  async issueCertificate(
    certificateNumber: string,
    studentId: string,
    studentAddress: string,
    courseId: string,
    pdfHash: string,
    finalScore: number,
    expiresAt: number = 0,
    metadata: string = ''
  ): Promise<{ txHash: string; certificate: Certificate }> {
    try {
      // Convert final score to the contract format (multiply by 100)
      const scoreForContract = Math.round(finalScore * 100);

      const tx = await this.contract.issueCertificate(
        certificateNumber,
        studentId,
        studentAddress,
        courseId,
        pdfHash,
        scoreForContract,
        expiresAt,
        metadata
      );

      const receipt = await tx.wait();
      
      // Get the certificate data from the contract
      const certificate = await this.getCertificateByNumber(certificateNumber);

      return {
        txHash: receipt.hash,
        certificate
      };
    } catch (error) {
      console.error('Error issuing certificate:', error);
      throw new Error(`Failed to issue certificate: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  /**
   * Revoke a certificate on the blockchain
   */
  async revokeCertificate(certificateNumber: string, reason: string): Promise<string> {
    try {
      const tx = await this.contract.revokeCertificate(certificateNumber, reason);
      const receipt = await tx.wait();
      return receipt.hash;
    } catch (error) {
      console.error('Error revoking certificate:', error);
      throw new Error(`Failed to revoke certificate: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  /**
   * Get certificate by certificate number
   */
  async getCertificateByNumber(certificateNumber: string): Promise<Certificate> {
    try {
      const result = await this.contract.getCertificateByCertNumber(certificateNumber);
      return this.formatCertificate(result);
    } catch (error) {
      console.error('Error getting certificate:', error);
      throw new Error(`Certificate not found: ${certificateNumber}`);
    }
  }

  /**
   * Get all certificates for a student by their ID
   */
  async getCertificatesByStudentId(studentId: string): Promise<Certificate[]> {
    try {
      const results = await this.contract.getCertificatesByStudentId(studentId);
      return results.map((result: any) => this.formatCertificate(result));
    } catch (error) {
      console.error('Error getting certificates for student:', error);
      throw new Error(`Failed to get certificates for student: ${studentId}`);
    }
  }

  /**
   * Verify a certificate
   */
  async verifyCertificate(certificateNumber: string, pdfHash: string): Promise<{
    isValid: boolean;
    isRevoked: boolean;
    issuedAt: number;
    expiresAt: number;
  }> {
    try {
      const result = await this.contract.verifyCertificate(certificateNumber, pdfHash);
      return {
        isValid: result[0],
        isRevoked: result[1],
        issuedAt: Number(result[2]),
        expiresAt: Number(result[3])
      };
    } catch (error) {
      console.error('Error verifying certificate:', error);
      return {
        isValid: false,
        isRevoked: false,
        issuedAt: 0,
        expiresAt: 0
      };
    }
  }

  /**
   * Get transaction status
   */
  async getTransactionStatus(txHash: string): Promise<TransactionStatus> {
    try {
      const receipt = await this.provider.getTransactionReceipt(txHash);
      const currentBlock = await this.provider.getBlockNumber();

      if (!receipt) {
        return {
          hash: txHash,
          status: 'pending',
          confirmations: 0
        };
      }

      const confirmations = currentBlock - receipt.blockNumber + 1;
      const status = receipt.status === 1 ? 'confirmed' : 'failed';

      return {
        hash: txHash,
        status,
        blockNumber: receipt.blockNumber,
        confirmations,
        gasUsed: receipt.gasUsed.toString()
      };
    } catch (error) {
      console.error('Error getting transaction status:', error);
      return {
        hash: txHash,
        status: 'failed',
        confirmations: 0
      };
    }
  }

  /**
   * Get total number of certificates issued
   */
  async getTotalCertificates(): Promise<number> {
    try {
      const total = await this.contract.getTotalCertificates();
      return Number(total);
    } catch (error) {
      console.error('Error getting total certificates:', error);
      return 0;
    }
  }

  /**
   * Check if the current signer is authorized to issue certificates
   */
  async isAuthorizedIssuer(): Promise<boolean> {
    try {
      const address = await this.signer.getAddress();
      return await this.contract.isAuthorizedIssuer(address);
    } catch (error) {
      console.error('Error checking issuer authorization:', error);
      return false;
    }
  }

  /**
   * Get network information
   */
  async getNetworkInfo(): Promise<{ name: string; chainId: number; blockNumber: number }> {
    try {
      const network = await this.provider.getNetwork();
      const blockNumber = await this.provider.getBlockNumber();
      return {
        name: network.name,
        chainId: Number(network.chainId),
        blockNumber
      };
    } catch (error) {
      console.error('Error getting network info:', error);
      throw error;
    }
  }

  /**
   * Get contract address
   */
  getContractAddressInfo(): string {
    return this.contractAddress;
  }

  /**
   * Format certificate data from contract response
   */
  private formatCertificate(contractResult: any): Certificate {
    return {
      certificateNumber: contractResult[0],
      studentId: contractResult[1],
      courseId: contractResult[2],
      pdfHash: contractResult[3],
      finalScore: Number(contractResult[4]) / 100, // Convert back from contract format
      issuedAt: Number(contractResult[5]),
      expiresAt: Number(contractResult[6]),
      isRevoked: contractResult[7],
      revocationReason: contractResult[8],
      metadata: contractResult[9]
    };
  }
}