import { buildModule } from "@nomicfoundation/hardhat-ignition/modules";

const CertificateRegistryModule = buildModule("CertificateRegistryModule", (m) => {
  // Deploy the CertificateRegistry contract
  const certificateRegistry = m.contract("CertificateRegistry");

  return { certificateRegistry };
});

export default CertificateRegistryModule;