import hardhatToolboxMochaEthersPlugin from "@nomicfoundation/hardhat-toolbox-mocha-ethers";
import { defineConfig } from "hardhat/config";
import 'dotenv/config';

export default defineConfig({
  plugins: [hardhatToolboxMochaEthersPlugin],
  paths: {
    cache: "./blockchain-data/cache",
    artifacts: "./blockchain-data/artifacts",
    sources: "./contracts",
    tests: "./test",
  },
  typechain: {
    outDir: "./blockchain-data/types",
  },
  solidity: {
    profiles: {
      default: {
        version: "0.8.20", // Updated to match contract version
      },
      production: {
        version: "0.8.20",
        settings: {
          optimizer: {
            enabled: true,
            runs: 200,
          },
        },
      },
    },
  },
  networks: {
    // Local Hardhat network
    hardhat: {
      type: "edr-simulated",
      chainId: 31337,
    },
    localhost: {
      type: "http",
      url: process.env.HARDHAT_RPC_URL || "http://127.0.0.1:8545",
      chainId: 31337,
    },
    
    // Polygon networks for production
    polygon: {
      type: "http",
      url: "https://polygon-rpc.com",
      accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : [],
      chainId: 137,
    },
    polygonMumbai: {
      type: "http", 
      url: "https://rpc-mumbai.maticvigil.com",
      accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : [],
      chainId: 80001,
    },
    sepolia: {
      type: "http",
      url: process.env.RPC_URL,
      accounts: [process.env.PRIVATE_KEY],
      chainId: 11155111,
    }
  },
});
