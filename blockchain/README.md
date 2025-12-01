# Blockchain Certificate Service# Sample Hardhat 3 Beta Project (`mocha` and `ethers`)



A Node.js microservice for managing educational certificates on blockchain networks using smart contracts.This project showcases a Hardhat 3 Beta project using `mocha` for tests and the `ethers` library for Ethereum interactions.



## FeaturesTo learn more about the Hardhat 3 Beta, please visit the [Getting Started guide](https://hardhat.org/docs/getting-started#getting-started-with-hardhat-3). To share your feedback, join our [Hardhat 3 Beta](https://hardhat.org/hardhat3-beta-telegram-group) Telegram group or [open an issue](https://github.com/NomicFoundation/hardhat/issues/new) in our GitHub issue tracker.



- **Certificate Issuance**: Issue certificates on blockchain with metadata## Project Overview

- **Certificate Verification**: Verify certificates using PDF hash validation

- **Certificate Revocation**: Revoke certificates with reason trackingThis example project includes:

- **Multi-Network Support**: Supports Ethereum, Polygon, BSC, and test networks

- **Transaction Tracking**: Monitor transaction status and confirmations- A simple Hardhat configuration file.

- **Health Monitoring**: Health checks and Prometheus metrics- Foundry-compatible Solidity unit tests.

- **RESTful API**: Complete REST API for certificate management- TypeScript integration tests using `mocha` and ethers.js

- Examples demonstrating how to connect to different types of networks, including locally simulating OP mainnet.

## API Endpoints

## Usage

### Certificate Management

- `POST /v1/certs/issue` - Issue a new certificate### Running Tests

- `GET /v1/certs/:certId` - Get certificate details

- `GET /v1/certs/owner/:ownerAddr` - List certificates by owner (paginated)To run all the tests in the project, execute the following command:

- `POST /v1/certs/revoke` - Revoke a certificate

- `POST /v1/certs/verify` - Verify a certificate```shell

npx hardhat test

### Transaction Management```

- `GET /v1/tx/:txHash` - Get transaction status and confirmations

You can also selectively run the Solidity or `mocha` tests:

### Webhooks

- `POST /v1/webhook/tc` - Transaction confirmation webhook```shell

npx hardhat test solidity

### Systemnpx hardhat test mocha

- `GET /v1/health` - Service health check```

- `GET /v1/metrics` - Prometheus metrics (JSON)

- `GET /v1/metrics/prometheus` - Prometheus metrics (text format)### Make a deployment to Sepolia



## Quick StartThis project includes an example Ignition module to deploy the contract. You can deploy this module to a locally simulated chain or to Sepolia.



### PrerequisitesTo run the deployment to a local chain:

- Node.js 20+

- pnpm```shell

- Docker (optional)npx hardhat ignition deploy ignition/modules/Counter.ts

```

### Local Development

To run the deployment to Sepolia, you need an account with funds to send the transaction. The provided Hardhat configuration includes a Configuration Variable called `SEPOLIA_PRIVATE_KEY`, which you can use to set the private key of the account you want to use.

1. **Install dependencies**

   ```bashYou can set the `SEPOLIA_PRIVATE_KEY` variable using the `hardhat-keystore` plugin or by setting it as an environment variable.

   pnpm install

   ```To set the `SEPOLIA_PRIVATE_KEY` config variable using `hardhat-keystore`:



2. **Set up environment**```shell

   ```bashnpx hardhat keystore set SEPOLIA_PRIVATE_KEY

   cp .env.example .env```

   # Edit .env with your configuration

   ```After setting the variable, you can run the deployment with the Sepolia network:



3. **Start local blockchain**```shell

   ```bashnpx hardhat ignition deploy --network sepolia ignition/modules/Counter.ts

   pnpm run hardhat:node```

   ```

4. **Deploy contracts**
   ```bash
   pnpm run deploy:local
   ```

5. **Start the service**
   ```bash
   pnpm run dev
   ```

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `PORT` | Service port | 3001 |
| `NODE_ENV` | Environment | development |
| `BLOCKCHAIN_NETWORK` | Active blockchain network | hardhat |
| `HARDHAT_RPC_URL` | Hardhat network RPC URL | http://localhost:8545 |
| `POLYGON_RPC_URL` | Polygon network RPC URL | https://polygon-rpc.com |
| `PRIVATE_KEY` | Deployment/transaction private key | - |
| `CERTIFICATE_CONTRACT_ADDRESS_*` | Contract addresses per network | - |

### Supported Networks

- **Hardhat** (local development): Chain ID 1337
- **Polygon Mainnet**: Chain ID 137
- **Polygon Mumbai** (testnet): Chain ID 80001

## API Usage Examples

### Issue a Certificate

```bash
curl -X POST http://localhost:3001/v1/certs/issue \
  -H "Content-Type: application/json" \
  -d '{
    "certificateNumber": "CERT-WEB-2024-001",
    "studentId": "uuid-student-id",
    "studentAddress": "0x1234567890123456789012345678901234567890",
    "courseId": "uuid-course-id",
    "pdfHash": "sha256-hash-of-pdf",
    "finalScore": 88.5,
    "metadata": "{\"courseName\":\"Web Development\"}"
  }'
```

### Verify a Certificate

```bash
curl -X POST http://localhost:3001/v1/certs/verify \
  -H "Content-Type: application/json" \
  -d '{
    "certificateNumber": "CERT-WEB-2024-001",
    "pdfHash": "sha256-hash-of-pdf"
  }'
```

### Check Service Health

```bash
curl http://localhost:3001/v1/health
```

## Development Commands

```bash
# Development
pnpm run dev                 # Start with hot reload
pnpm run build              # Build TypeScript
pnpm start                  # Start production build

# Blockchain
pnpm run compile            # Compile smart contracts
pnpm run hardhat:node       # Start local blockchain
pnpm run deploy:local       # Deploy to local network
pnpm run deploy:polygon     # Deploy to Polygon
pnpm run deploy:mumbai      # Deploy to Mumbai testnet

# Testing
pnpm test                   # Run tests
```

## Integration with Main Application

The blockchain service integrates with the main Laravel backend through:

1. **REST API calls** for certificate operations
2. **Webhook notifications** for transaction confirmations
3. **Shared environment variables** for configuration
4. **Docker networking** for service communication