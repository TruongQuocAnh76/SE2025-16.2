import { ethers } from "ethers";
import hre from "hardhat";

async function main() {
  console.log("Deploying CertificateRegistry contract...");

  // Get the contract factory using hre.ethers
  const CertificateRegistry = await hre.ethers.getContractFactory("CertificateRegistry");
  
  // Deploy the contract
  const certificateRegistry = await CertificateRegistry.deploy();
  
  // Wait for deployment to complete
  await certificateRegistry.waitForDeployment();
  
  const contractAddress = await certificateRegistry.getAddress();
  
  console.log("CertificateRegistry deployed to:", contractAddress);
  console.log("Deployer (owner):", await certificateRegistry.owner());
  
  // Verify the contract is working by checking if deployer is authorized
  const deployerAddress = (await hre.ethers.getSigners())[0].address;
  const isAuthorized = await certificateRegistry.isAuthorizedIssuer(deployerAddress);
  console.log("Deployer is authorized issuer:", isAuthorized);
  
  return contractAddress;
}

// We recommend this pattern to be able to use async/await everywhere
// and properly handle errors.
main()
  .then((address) => {
    console.log("Deployment successful. Contract address:", address);
    process.exit(0);
  })
  .catch((error) => {
    console.error("Deployment failed:", error);
    process.exitCode = 1;
  });