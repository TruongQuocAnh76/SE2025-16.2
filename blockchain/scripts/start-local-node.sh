#!/bin/bash

# Start local Hardhat node and deploy contracts

echo "🚀 Starting Hardhat local blockchain node..."

# Start hardhat node in background
npx hardhat node &
HARDHAT_PID=$!

echo "⏱️  Waiting for Hardhat node to start..."
sleep 5

echo "📋 Deploying CertificateRegistry contract..."
npx hardhat ignition deploy ./ignition/modules/CertificateRegistry.ts --network localhost

echo "✅ Local blockchain setup complete!"
echo "🔗 Hardhat node running on http://localhost:8545"
echo "📄 Contract deployed - check the output above for the address"
echo ""
echo "To stop the node, run: kill $HARDHAT_PID"
echo "Or press Ctrl+C to stop this script and the node"

# Keep script running
wait $HARDHAT_PID