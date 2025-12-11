// scripts/new-domain.mjs
import fs from 'fs';
import path from 'path';

// Lấy tên domain từ lệnh bạn gõ (ví dụ: "blog")
const domainName = process.argv[2];

if (!domainName) {
  console.error('❌ Vui lòng nhập tên domain!');
  console.log('Ví dụ: node scripts/new-domain.mjs blog');
  process.exit(1);
}

// Đường dẫn đến thư mục domains
const domainPath = path.join(domainName);

// Cấu trúc thư mục con bạn muốn tạo
const subfolders = [
  'components',
  'composables',
  'services',
  'types',
  'utils'
];

try {
  // 1. Tạo thư mục domain chính (ví dụ: domains/blog)
  fs.mkdirSync(domainPath, { recursive: true });

  // 2. Tạo các thư mục con
  for (const folder of subfolders) {
    const folderPath = path.join(domainPath, folder);
    fs.mkdirSync(folderPath, { recursive: true });
    
    // 3. Tạo file .gitkeep để giữ thư mục rỗng trong Git
    fs.writeFileSync(path.join(folderPath, '.gitkeep'), '');
  }

  console.log(`✅ Đã tạo domain mới thành công tại: ${domainPath}`);

} catch (err) {
  console.error(`Lỗi khi tạo domain: ${err.message}`);
}