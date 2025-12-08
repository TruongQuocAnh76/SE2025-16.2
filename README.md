# **Certchain \- nền tảng học tập trực tuyến**

## **1\. Tổng quan dự án**

### **1.1 Mục đích**

Xây dựng nền tảng học trực tuyến cho phép học viên tham gia khóa học, làm bài kiểm tra, và nhận chứng chỉ/kỹ năng được lưu trữ và xác thực trên blockchain để đảm bảo tính toàn vẹn và dễ kiểm chứng.

### **1.2 Mục tiêu**

Hệ thống cho phép phát hành chứng chỉ on-chain cho mỗi học viên hoàn thành khóa học, đảm bảo tính minh bạch và không thể giả mạo.

---

## **2\. Phạm vi dự án (Scope)**

### **2.1 MVP (Minimum Viable Product)**

#### **Chức năng chung**

* Đăng ký / Đăng nhập (Email/Password \+ OAuth)  
* Phân quyền người dùng (Student, Teacher, Admin)  
* Dashboard theo vai trò  
* Logging & Monitoring cơ bản

#### **Student (Học viên)**

* Duyệt và tìm kiếm khóa học  
* Đăng ký học khóa học  
* Xem nội dung khóa học (video, PDF, tài liệu)  
* Theo dõi tiến độ học tập  
* Làm bài kiểm tra/quiz  
* Nhận chứng chỉ số sau khi hoàn thành  
* Xem chứng chỉ đã đạt được với link blockchain để xác thực  
* Trang cá nhân (profile) hiển thị:  
  * Thông tin cá nhân  
  * Danh sách khóa học đã tham gia  
  * Chứng chỉ đã nhận (với Transaction ID blockchain)  
  * Tiến độ học tập

#### **Teacher (Giảng viên)**

* Tạo và quản lý khóa học  
* Upload nội dung khóa học:  
  * Video bài giảng  
  * Tài liệu PDF  
  * Bài tập/quiz  
* Chỉnh sửa nội dung khóa học  
* Xem danh sách học viên đã đăng ký  
* Theo dõi tiến độ học viên  
* Dashboard giảng viên:  
  * Thống kê số lượng học viên  
  * Tỷ lệ hoàn thành khóa học  
  * Đánh giá từ học viên

#### **Admin (Quản trị viên)**

* Quản lý người dùng (Student, Teacher)  
* Phê duyệt/từ chối khóa học mới  
* Phát hành chứng chỉ (manual trigger nếu cần)  
* Chỉnh sửa thông tin chứng chỉ  
* Xem và kiểm tra tất cả transaction blockchain  
* Dashboard admin:  
  * Thống kê tổng quan hệ thống  
  * Quản lý khóa học  
  * Quản lý người dùng  
  * Logs và monitoring  
  * Quản lý blockchain transactions

#### **Blockchain Integration**

* Lưu hash chứng chỉ \+ metadata minimal on-chain  
* Mỗi chứng chỉ có Transaction ID duy nhất  
* API verify chứng chỉ qua blockchain  
* Lưu file PDF chứng chỉ off-chain (S3) với link hashed

### **2.2 Ngoài MVP (Future Features)(làm được bao nhiêu thì làm)** 

* Thanh toán phức tạp (subscription billing nâng cao)  
* Chat realtime giữa Teacher và Student  
* Lớp học trực tiếp (live classroom với video conference)  
* Hệ thống điểm tích lũy/phần thưởng  
* Hệ thống đánh giá và review khóa học nâng cao  
* Gamification (badges, leaderboard)  
* Social learning features (forum, discussion board)  
* AI gợi ý khóa học, chatbot cá nhân

  ---

## **3\. Tech Stack**

### **3.1 Frontend**

* **Framework**: Vue.js \+ Nuxt

### **3.2 Backend**

* **Framework**: PHP Laravel

### **3.3 Database**

* **Primary Database**: PostgreSQL (quan hệ)  
* **Cache**: Redis (cache)

### **3.4 Blockchain Layer**

* **Network**: Private Ethereum / Polygon / Hyperledger (tùy yêu cầu chi phí & quyền riêng tư)  
* **Storage Strategy**:  
  * On-chain: Certificate hash \+ metadata minimal  
  * Off-chain: File/PII lưu trên S3 với link hashed

### **3.5 Storage**

* **File Storage**: S3 / MinIO (cho file PDF chứng chỉ)

### **3.6 CI/CD**

* **Pipeline**: GitHub Actions / GitLab CI

---

## **4\. Installation & Setup**

### **4.1 Prerequisites**

Before running this project, make sure you have the following installed:

* **Docker & Docker Compose** (version 20.10+)
* **Git** (for cloning the repository)
* **Node.js** (version 18+ recommended, for local development)
* **pnpm** (package manager for frontend)

### **4.2 Clone the Repository**

```bash
git clone https://github.com/TruongQuocAnh76/SE2025-16.2.git
cd SE2025-16.2
```

### **4.3 Environment Setup**

#### **Backend Configuration**

1. Navigate to the backend directory:
```bash
cd backend
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Update the `.env` file with your configuration (database credentials, etc.)

#### **Frontend Configuration**

1. Navigate to the frontend directory:
```bash
cd ../frontend
```

2. Install dependencies:
```bash
pnpm install
```

### **4.4 Running the Development Environment**

#### **Using Docker Compose (Recommended)**

1. From the project root directory, start all services:
```bash
pnpm dev
```

This command will:
- Build and start the frontend (Nuxt.js) on `http://localhost:3000`
- Build and start the backend (Laravel) on `http://localhost:8000`
- Start PostgreSQL database on `localhost:5432`
- Start Redis for caching

#### **Alternative: Manual Setup**

If you prefer to run services individually:

**Frontend:**
```bash
cd frontend
pnpm dev
```

**Backend:**
```bash
cd backend
php artisan serve
```

**Database:**
```bash
docker-compose -f docker-compose.dev.yml up -d db redis
```

### **4.5 Database Setup**

#### **Run Migrations**

After the containers are running, execute the database migrations:

```bash
docker-compose -f docker-compose.dev.yml exec backend php artisan migrate
```

**Note**: If you're using database sessions, cache, or queues (configured in `.env`), you may need to create additional tables:

```bash
# Create sessions table (if SESSION_DRIVER=database)
docker-compose -f docker-compose.dev.yml exec backend php artisan session:table

# Create cache table (if CACHE_STORE=database)  
docker-compose -f docker-compose.dev.yml exec backend php artisan cache:table

# Create jobs table (if QUEUE_CONNECTION=database)
docker-compose -f docker-compose.dev.yml exec backend php artisan queue:table

# Then run migrations again
docker-compose -f docker-compose.dev.yml exec backend php artisan migrate
```

#### **Seed the Database (Optional)**

To populate the database with sample data:

```bash
docker-compose -f docker-compose.dev.yml exec backend php artisan db:seed
```

This will create sample users, courses, and other test data.

### **4.6 Accessing the Application**

Once everything is running:

* **Frontend Application**: `http://localhost:3000`
* **Backend API**: `http://localhost:8000`
* **Database**: `localhost:5432` (PostgreSQL)
* **Redis**: `localhost:6379`

### **4.7 Default Credentials**

After seeding the database, you can use these default accounts:

* **Admin User**:
  - Email: `admin@certchain.com`
  - Password: `password`

* **Teacher User**:
  - Email: `teacher@certchain.com`
  - Password: `password`

* **Student User**:
  - Email: `student@certchain.com`
  - Password: `password`

### **4.8 Development Commands**

#### **Backend Commands**

```bash
# Run migrations
docker-compose -f docker-compose.dev.yml exec backend php artisan migrate

# Run seeders
docker-compose -f docker-compose.dev.yml exec backend php artisan db:seed

# Clear cache
docker-compose -f docker-compose.dev.yml exec backend php artisan cache:clear

# Generate application key
docker-compose -f docker-compose.dev.yml exec backend php artisan key:generate
```

#### **Frontend Commands**

```bash
cd frontend

# Install dependencies
pnpm install

# Run development server
pnpm dev

# Build for production
pnpm build

# Run linting
pnpm lint
```

#### **Docker Commands**

```bash
# Start all services
pnpm dev

# Stop all services
docker-compose -f docker-compose.dev.yml down

# View logs
docker-compose -f docker-compose.dev.yml logs -f

# Rebuild specific service
docker-compose -f docker-compose.dev.yml up --build frontend
```

### **4.9 Troubleshooting**

#### **Common Issues**

1. **Port conflicts**: Make sure ports 3000, 8000, 5432, and 6379 are available
2. **Permission issues**: Ensure Docker has proper permissions
3. **Database connection**: Wait for PostgreSQL to fully start before running migrations
4. **Node modules**: If frontend fails, try `rm -rf node_modules && pnpm install`

#### **Reset Everything**

To completely reset the development environment:

```bash
# Stop and remove all containers
docker-compose -f docker-compose.dev.yml down -v

# Remove Docker volumes (this will delete database data)
docker volume prune

# Rebuild everything
pnpm dev
```

---

## **5\. Project Structure**

```
SE2025-16.2/
├── frontend/          # Nuxt.js frontend application
│   ├── components/    # Vue components
│   ├── pages/         # Application pages
│   ├── layouts/       # Page layouts
│   ├── plugins/       # Nuxt plugins
│   └── Dockerfile*    # Docker configurations
├── backend/           # Laravel backend API
│   ├── app/           # Application code
│   ├── database/      # Migrations and seeders
│   ├── routes/        # API routes
│   └── Dockerfile*    # Docker configurations
├── docker-compose*.yml # Docker Compose configurations
└── README.md          # This file
```

---

## **6\. Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---