// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

import "@openzeppelin/contracts/access/Ownable.sol";
import "@openzeppelin/contracts/utils/ReentrancyGuard.sol";

/**
 * @title CertificateRegistry
 * @dev Smart contract for managing educational certificates on blockchain
 * This contract stores certificate metadata and handles issuance and revocation
 */
contract CertificateRegistry is Ownable, ReentrancyGuard {
    
    uint256 private _certificateIdCounter;
    
    struct Certificate {
        string certificateNumber;    // Human-readable certificate number (e.g., CERT-WEB-2024-001)
        string studentId;           // UUID of the student from off-chain database
        string courseId;            // UUID of the course from off-chain database
        string pdfHash;             // Hash of the PDF certificate for verification
        uint256 finalScore;         // Final score (stored as integer, divide by 100 for percentage)
        uint256 issuedAt;          // Timestamp when certificate was issued
        uint256 expiresAt;         // Expiration timestamp (0 for non-expiring)
        bool isRevoked;            // Whether certificate has been revoked
        string revocationReason;   // Reason for revocation (if applicable)
        string metadata;           // Additional JSON metadata
    }
    
    // Mapping from certificate ID to certificate data
    mapping(uint256 => Certificate) public certificates;
    
    // Mapping from certificate number to certificate ID for quick lookup
    mapping(string => uint256) public certificateNumberToId;
    
    // Mapping from student address to array of certificate IDs
    mapping(address => uint256[]) public studentCertificates;
    
    // Mapping from student ID (UUID) to wallet address
    mapping(string => address) public studentIdToAddress;
    
    // Mapping to track authorized issuers (backend services)
    mapping(address => bool) public authorizedIssuers;
    
    // Events
    event CertificateIssued(
        uint256 indexed certificateId,
        string indexed certificateNumber,
        string indexed studentId,
        address studentAddress,
        string courseId,
        uint256 finalScore,
        string pdfHash
    );
    
    event CertificateRevoked(
        uint256 indexed certificateId,
        string indexed certificateNumber,
        string reason
    );
    
    event StudentAddressUpdated(
        string indexed studentId,
        address indexed oldAddress,
        address indexed newAddress
    );
    
    event IssuerAuthorized(address indexed issuer);
    event IssuerRevoked(address indexed issuer);
    
    constructor() Ownable(msg.sender) {
        // Authorize the deployer as the first issuer
        authorizedIssuers[msg.sender] = true;
        emit IssuerAuthorized(msg.sender);
    }
    
    modifier onlyAuthorizedIssuer() {
        require(authorizedIssuers[msg.sender], "Not authorized to issue certificates");
        _;
    }
    
    /**
     * @dev Issue a new certificate
     * @param _certificateNumber Human-readable certificate number
     * @param _studentId Student's UUID from database
     * @param _studentAddress Student's wallet address
     * @param _courseId Course UUID from database
     * @param _pdfHash Hash of the certificate PDF
     * @param _finalScore Final score (multiplied by 100, e.g., 8850 for 88.50%)
     * @param _expiresAt Expiration timestamp (0 for non-expiring)
     * @param _metadata Additional metadata as JSON string
     */
    function issueCertificate(
        string memory _certificateNumber,
        string memory _studentId,
        address _studentAddress,
        string memory _courseId,
        string memory _pdfHash,
        uint256 _finalScore,
        uint256 _expiresAt,
        string memory _metadata
    ) external onlyAuthorizedIssuer nonReentrant {
        require(bytes(_certificateNumber).length > 0, "Certificate number cannot be empty");
        require(bytes(_studentId).length > 0, "Student ID cannot be empty");
        require(bytes(_courseId).length > 0, "Course ID cannot be empty");
        require(_studentAddress != address(0), "Invalid student address");
        require(certificateNumberToId[_certificateNumber] == 0, "Certificate number already exists");
        
        _certificateIdCounter++;
        uint256 certificateId = _certificateIdCounter;
        
        certificates[certificateId] = Certificate({
            certificateNumber: _certificateNumber,
            studentId: _studentId,
            courseId: _courseId,
            pdfHash: _pdfHash,
            finalScore: _finalScore,
            issuedAt: block.timestamp,
            expiresAt: _expiresAt,
            isRevoked: false,
            revocationReason: "",
            metadata: _metadata
        });
        
        certificateNumberToId[_certificateNumber] = certificateId;
        studentCertificates[_studentAddress].push(certificateId);
        studentIdToAddress[_studentId] = _studentAddress;
        
        emit CertificateIssued(
            certificateId,
            _certificateNumber,
            _studentId,
            _studentAddress,
            _courseId,
            _finalScore,
            _pdfHash
        );
    }
    
    /**
     * @dev Revoke a certificate
     * @param _certificateNumber Certificate number to revoke
     * @param _reason Reason for revocation
     */
    function revokeCertificate(
        string memory _certificateNumber,
        string memory _reason
    ) external onlyAuthorizedIssuer {
        uint256 certificateId = certificateNumberToId[_certificateNumber];
        require(certificateId != 0, "Certificate does not exist");
        require(!certificates[certificateId].isRevoked, "Certificate already revoked");
        
        certificates[certificateId].isRevoked = true;
        certificates[certificateId].revocationReason = _reason;
        
        emit CertificateRevoked(certificateId, _certificateNumber, _reason);
    }
    
    /**
     * @dev Get certificate by certificate number
     * @param _certificateNumber Certificate number to lookup
     */
    function getCertificateByCertNumber(string memory _certificateNumber) 
        external 
        view 
        returns (Certificate memory) 
    {
        uint256 certificateId = certificateNumberToId[_certificateNumber];
        require(certificateId != 0, "Certificate does not exist");
        return certificates[certificateId];
    }
    
    /**
     * @dev Get certificate by ID
     * @param _certificateId Certificate ID to lookup
     */
    function getCertificateById(uint256 _certificateId) 
        external 
        view 
        returns (Certificate memory) 
    {
        require(_certificateId > 0 && _certificateId <= _certificateIdCounter, "Invalid certificate ID");
        return certificates[_certificateId];
    }
    
    /**
     * @dev Get all certificates for a student address
     * @param _studentAddress Student's wallet address
     */
    function getCertificatesByStudent(address _studentAddress) 
        external 
        view 
        returns (Certificate[] memory) 
    {
        uint256[] memory certIds = studentCertificates[_studentAddress];
        Certificate[] memory result = new Certificate[](certIds.length);
        
        for (uint256 i = 0; i < certIds.length; i++) {
            result[i] = certificates[certIds[i]];
        }
        
        return result;
    }
    
    /**
     * @dev Get all certificates for a student by student ID
     * @param _studentId Student's UUID from database
     */
    function getCertificatesByStudentId(string memory _studentId) 
        external 
        view 
        returns (Certificate[] memory) 
    {
        address studentAddress = studentIdToAddress[_studentId];
        require(studentAddress != address(0), "Student address not found");
        return this.getCertificatesByStudent(studentAddress);
    }
    
    /**
     * @dev Verify a certificate by certificate number and PDF hash
     * @param _certificateNumber Certificate number
     * @param _pdfHash Expected PDF hash
     */
    function verifyCertificate(string memory _certificateNumber, string memory _pdfHash) 
        external 
        view 
        returns (bool isValid, bool isRevoked, uint256 issuedAt, uint256 expiresAt) 
    {
        uint256 certificateId = certificateNumberToId[_certificateNumber];
        if (certificateId == 0) {
            return (false, false, 0, 0);
        }
        
        Certificate memory cert = certificates[certificateId];
        bool hashMatches = keccak256(bytes(cert.pdfHash)) == keccak256(bytes(_pdfHash));
        bool notExpired = cert.expiresAt == 0 || block.timestamp <= cert.expiresAt;
        
        return (
            hashMatches && notExpired && !cert.isRevoked,
            cert.isRevoked,
            cert.issuedAt,
            cert.expiresAt
        );
    }
    
    /**
     * @dev Update student's wallet address
     * @param _studentId Student's UUID
     * @param _newAddress New wallet address
     */
    function updateStudentAddress(string memory _studentId, address _newAddress) 
        external 
        onlyAuthorizedIssuer 
    {
        require(_newAddress != address(0), "Invalid address");
        
        address oldAddress = studentIdToAddress[_studentId];
        studentIdToAddress[_studentId] = _newAddress;
        
        // Move certificates to new address if old address exists
        if (oldAddress != address(0) && oldAddress != _newAddress) {
            uint256[] memory certIds = studentCertificates[oldAddress];
            delete studentCertificates[oldAddress];
            studentCertificates[_newAddress] = certIds;
        }
        
        emit StudentAddressUpdated(_studentId, oldAddress, _newAddress);
    }
    
    /**
     * @dev Authorize an address to issue certificates
     * @param _issuer Address to authorize
     */
    function authorizeIssuer(address _issuer) external onlyOwner {
        require(_issuer != address(0), "Invalid issuer address");
        authorizedIssuers[_issuer] = true;
        emit IssuerAuthorized(_issuer);
    }
    
    /**
     * @dev Revoke issuer authorization
     * @param _issuer Address to revoke
     */
    function revokeIssuer(address _issuer) external onlyOwner {
        authorizedIssuers[_issuer] = false;
        emit IssuerRevoked(_issuer);
    }
    
    /**
     * @dev Get total number of certificates issued
     */
    function getTotalCertificates() external view returns (uint256) {
        return _certificateIdCounter;
    }
    
    /**
     * @dev Check if an address is authorized to issue certificates
     */
    function isAuthorizedIssuer(address _issuer) external view returns (bool) {
        return authorizedIssuers[_issuer];
    }
}