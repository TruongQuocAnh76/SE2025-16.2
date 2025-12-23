# **Certchain \- nền tảng học tập trực tuyến**
Link demo: http://34.172.129.94/

Link Figma: https://www.figma.com/design/BQg0K7wF3kNGfa25L6iQtI/E-Learning-Site-Ver-3.0
# Mục tiêu và Phạm vi dự án

## Goal

Phát triển **Certchain**, một nền tảng học trực tuyến tích hợp blockchain, cho phép người dùng đăng ký khóa học, hoàn thành bài kiểm tra, và **nhận chứng chỉ số có thể xác thực trên chuỗi một cách minh bạch và bất biến**. Hệ thống này nhằm nâng cao trải nghiệm học tập, đảm bảo chứng chỉ không thể bị giả mạo, và hỗ trợ triển khai dễ dàng trong môi trường đa dịch vụ.

## Business/Deliverable Objectives

### 1) Mục tiêu chức năng
* Xây dựng giao diện người dùng responsive với **Vue.js + Nuxt** cho các chức năng: đăng ký/đăng nhập, duyệt khóa học, xem bài học và theo dõi tiến độ.
* Thiết kế và triển khai **API backend Laravel** xử lý xác thực, quản lý khóa học, bài kiểm tra và phát hành chứng chỉ.
* Tích hợp lớp **blockchain** để lưu trữ các hash chứng chỉ và hỗ trợ truy vấn xác thực chứng chỉ từ blockchain.

### 2) Mục tiêu chất lượng & kỹ thuật
* Thiết lập môi trường phát triển & chạy thử với **Docker Compose** chứa frontend, backend, PostgreSQL, Redis và node blockchain để đảm bảo mọi thành phần hoạt động đồng bộ.
* Thực hiện kiểm thử end‑to‑end bao gồm: đăng ký người dùng, hoàn thành khóa học, phát hành chứng chỉ lên blockchain, và xác thực chứng chỉ.
* Đạt các chỉ số chất lượng (Metrics) để đo lường hiệu năng và độ tin cậy trước khi nộp báo cáo:
  * Thời gian phản hồi API trung bình dưới **500 ms** dưới tải 20 người dùng.
  * Đạt tối thiểu **95% coverage** trên các test tự động cho các endpoint chính.
  * Chứng chỉ được ghi lên blockchain trong vòng **15 giây** từ lúc hoàn thành khóa học.

---

# Tài liệu Người dùng

## Tính năng chính

### Dành cho Học viên
- **Duyệt và Đăng ký Khóa học**: Duyệt các khóa học có sẵn, xem thông tin chi tiết và đăng ký tham gia
- **Theo dõi tiến độ học tập**: Theo dõi tiến độ học qua các bài học và module với chỉ báo trực quan
- **Bài học Video & Streaming HLS**: Xem các bài học video chất lượng cao với streaming thích ứng
- **Bài kiểm tra & Đánh giá**: Tham gia các bài kiểm tra để đánh giá kiến thức với hỗ trợ chấm điểm tự động
- **Chứng chỉ Blockchain**: Nhận chứng chỉ số chống giả mạo được lưu trữ trên blockchain khi hoàn thành khóa học
- **Xác thực Chứng chỉ**: Xác thực tính xác thực của chứng chỉ bằng công cụ xác minh công khai
- **Thanh toán tích hợp**: Thanh toán khóa học qua Stripe hoặc PayPal
- **Gói Premium**: Đăng ký để truy cập nội dung premium

### Dành cho Giảng viên
- **Tạo khóa học**: Tạo và quản lý khóa học với các module, bài học và bài kiểm tra
- **Quản lý học viên**: Xem học viên đã đăng ký và theo dõi tiến độ học
- **Quản lý bài kiểm tra**: Tạo các bài kiểm tra với nhiều loại câu hỏi và chấm điểm
- **Cấp chứng chỉ**: Cấp chứng chỉ được xác thực trên blockchain cho học viên hoàn thành khóa học
- **Bảng điều khiển phân tích**: Xem thống kê về khóa học và mức độ tương tác của học viên

### Dành cho Quản trị viên
- **Quản lý người dùng**: Quản lý người dùng, vai trò và quyền hạn
- **Xử lý đăng ký giảng viên**: Xem xét các đơn đăng ký giảng viên
- **Tổng quan chứng chỉ**: Giám sát tất cả chứng chỉ đã cấp và trạng thái trên blockchain
- **Nhật ký hệ thống & Kiểm tra**: Truy cập nhật ký hệ thống để giám sát và xử lý sự cố

---

## Hướng dẫn cài đặt / Bắt đầu

### Yêu cầu trước

- **Docker** và **Docker Compose** (v2.0+)
- **pnpm** (tùy chọn, cho phát triển cục bộ mà không cần Docker)
- **Git**

### Bắt đầu nhanh với Docker (Khuyến nghị)

1. **Clone repository**
   ```bash
   git clone <repository-url>
   cd SE2025-16.2

2. **Sao chép các file môi trường**

   ```bash
   cp backend/.env.example backend/.env
   cp frontend/.env.example frontend/.env
   cp blockchain/.env.example blockchain/.env
   ```

3. **Tạo khóa ứng dụng Laravel**

   ```bash
   docker compose -f docker-compose.dev.yml run --rm backend php artisan key:generate
   ```

4. **Khởi chạy tất cả dịch vụ ở chế độ phát triển**

   ```bash
   pnpm dev
   # Hoặc dùng trực tiếp docker-compose:
   docker compose -f docker-compose.dev.yml up --build
   ```

5. **Chạy migration cơ sở dữ liệu** (mở terminal mới)

   ```bash
   docker compose -f docker-compose.dev.yml exec backend php artisan migrate --seed
   ```

6. **Truy cập ứng dụng**

   * **Frontend**: [http://localhost:3000](http://localhost:3000)
   * **Backend API**: [http://localhost:8000](http://localhost:8000)
   * **Dịch vụ Blockchain**: [http://localhost:3001](http://localhost:3001)
   * **MinIO Console**: [http://localhost:9001](http://localhost:9001) (login: `minioadmin`/`minioadmin`)
   * **RabbitMQ Management**: [http://localhost:15672](http://localhost:15672) (login: `guest`/`guest`)

### Cấu hình môi trường

#### Backend (.env)

| Biến                     | Mô tả                  | Mặc định                 |
| ------------------------ | ---------------------- | ------------------------ |
| `APP_URL`                | URL backend            | `http://localhost:8000`  |
| `FRONTEND_URL`           | URL frontend           | `http://localhost:3000`  |
| `DB_HOST`                | Host PostgreSQL        | `db`                     |
| `DB_DATABASE`            | Tên cơ sở dữ liệu      | `certchain`              |
| `BLOCKCHAIN_SERVICE_URL` | URL dịch vụ blockchain | `http://blockchain:3001` |
| `STRIPE_SECRET_KEY`      | Khóa bí mật Stripe     | -                        |
| `PAYPAL_CLIENT_ID`       | Client ID PayPal       | -                        |

#### Frontend (.env)

| Biến                     | Mô tả                 | Mặc định                |
| ------------------------ | --------------------- | ----------------------- |
| `BACKEND_URL`            | URL API backend       | `http://localhost:8000` |
| `STORAGE_ENDPOINT`       | Endpoint MinIO/S3     | `http://localhost:9002` |
| `STRIPE_PUBLISHABLE_KEY` | Khóa công khai Stripe | -                       |

#### Blockchain (.env)

| Biến                 | Mô tả        | Mặc định         |
| -------------------- | ------------ | ---------------- |
| `PORT`               | Cổng dịch vụ | `3001`           |
| `BLOCKCHAIN_NETWORK` | Mạng sử dụng | `hardhat`        |
| `PRIVATE_KEY`        | Khóa ký      | Mặc định Hardhat |

### Dừng các dịch vụ

```bash
pnpm dev:down
# Hoặc: docker compose -f docker-compose.dev.yml down
```

### Triển khai Production

```bash
# Build image production
pnpm build:prod

# Khởi chạy dịch vụ production
pnpm prod

# Dừng production services
pnpm prod:down
```

---

## Hướng dẫn sử dụng

### Đăng ký & Đăng nhập

1. Truy cập [http://localhost:3000](http://localhost:3000)
2. Nhấn **Sign Up** để tạo tài khoản
3. Điền email, tên và mật khẩu
4. Hoặc đăng ký bằng OAuth Google hoặc Facebook
5. Sau khi đăng ký, đăng nhập bằng thông tin đã tạo

### Duyệt & Đăng ký Khóa học

1. Trên trang chủ, duyệt các khóa học nổi bật hoặc sử dụng chức năng **Tìm kiếm**
2. Nhấn vào thẻ khóa học để xem chi tiết:

   * Mô tả và mục tiêu khóa học
   * Cấu trúc module và bài học
   * Thông tin giảng viên
   * Giá và đánh giá
3. Nhấn **Enroll** để đăng ký
4. Khóa học trả phí: thanh toán qua Stripe hoặc PayPal
5. Thành viên Premium: có thể đăng ký khóa học miễn phí nếu được phép

### Học tập & Theo dõi tiến độ

1. Sau khi đăng ký, truy cập khóa học tại **My Courses**
2. Duyệt các module và bài học qua sidebar
3. Xem video bài học với streaming HLS thích ứng
4. Đánh dấu bài học đã hoàn thành để theo dõi tiến độ
5. Xem tổng tiến độ khóa học trên trang tổng quan

### Làm Bài kiểm tra

1. Truy cập phần quiz trong khóa học
2. Nhấn **Start Quiz** để bắt đầu
3. Trả lời tất cả câu hỏi trong thời gian cho phép
4. Nộp bài để được chấm điểm
5. Xem điểm và kiểm tra đáp án đúng/sai
6. Làm lại quiz nếu khóa học cho phép

### Nhận Chứng chỉ

1. Hoàn thành tất cả bài học và vượt qua quiz
2. Chứng chỉ được phát hành và ghi trên blockchain
3. Tải chứng chỉ dưới dạng PDF
4. Chia sẻ chứng chỉ bằng link xác thực duy nhất

### Xác thực Chứng chỉ

1. Truy cập trang **Verify Certificate**
2. Nhập số chứng chỉ hoặc tải PDF lên
3. Hệ thống sẽ xác thực chứng chỉ trên blockchain
4. Xem kết quả xác thực:

   * Tên học viên và khóa học hoàn thành
   * Ngày phát hành và thông tin người cấp
   * Hash giao dịch blockchain và trạng thái xác thực

### Trở thành Giảng viên

1. Truy cập **Teacher Registration**
2. Nộp đơn với:

   * Bằng cấp và kinh nghiệm
   * Kinh nghiệm giảng dạy
   * Chứng chỉ/tài liệu cần thiết
3. Chờ phê duyệt từ quản trị viên
4. Sau khi được phê duyệt, truy cập dashboard giảng viên để tạo khóa học

### Tạo Khóa học (Giảng viên)

1. Trong dashboard, nhấn **Create Course**
2. Điền thông tin khóa học:

   * Tiêu đề, mô tả, thumbnail
   * Thể loại và tags
   * Giá (hoặc miễn phí)
3. Thêm module và bài học:

   * Tạo cấu trúc module
   * Upload video hoặc thêm nội dung văn bản
   * Sắp xếp thứ tự bài học
4. Tạo quiz:

   * Thêm câu hỏi nhiều lựa chọn hoặc văn bản
   * Cài đặt điểm đạt và số lần thử
5. Nộp khóa học để admin phê duyệt
6. Sau khi phê duyệt, khóa học được công bố

### Dashboard Quản trị viên

1. Truy cập `/admin`
2. **Dashboard**: Xem thống kê nền tảng và các mục chờ xử lý
3. **Người dùng**: Quản lý tài khoản và vai trò
4. **Khóa học**: Xem xét và phê duyệt khóa học chờ duyệt
5. **Đơn đăng ký**: Xử lý đơn đăng ký giảng viên
6. **Chứng chỉ**: Giám sát phát hành chứng chỉ và trạng thái blockchain
7. **Nhật ký hệ thống**: Xem hoạt động hệ thống

---

## FAQ / Xử lý sự cố

### Vấn đề phổ biến

#### Docker container không khởi động

**Vấn đề**: Dịch vụ không chạy hoặc crash ngay lập tức

**Giải pháp**:

```bash
# Dọn dẹp Docker và build lại
pnpm clean
pnpm dev
```

#### Lỗi kết nối cơ sở dữ liệu

**Vấn đề**: Backend không kết nối được PostgreSQL

**Giải pháp**:

1. Đảm bảo container `db` đang chạy: `docker compose -f docker-compose.dev.yml ps`
2. Chờ vài giây để PostgreSQL khởi tạo hoàn tất
3. Kiểm tra `DB_HOST=db` trong `backend/.env`

#### Dịch vụ Blockchain không phản hồi

**Vấn đề**: Phát hành chứng chỉ lỗi do blockchain

**Giải pháp**:

1. Kiểm tra logs blockchain: `docker compose -f docker-compose.dev.yml logs blockchain`
2. Đảm bảo node Hardhat chạy trên port 8545
3. Kiểm tra hợp đồng đã được deploy thành công
4. Kiểm tra `BLOCKCHAIN_SERVICE_URL` trong backend `.env`

#### Upload video không hoạt động

**Vấn đề**: Không upload được bài học video

**Giải pháp**:

1. Kiểm tra MinIO đang chạy: `docker compose -f docker-compose.dev.yml ps minio`
2. Kiểm tra bucket MinIO tại [http://localhost:9001](http://localhost:9001)
3. Đảm bảo `STORAGE_ENDPOINT` và credentials chính xác
4. Kiểm tra giới hạn kích thước file trong cấu hình nginx

#### Xác thực chứng chỉ lỗi

**Vấn đề**: Chứng chỉ hiển thị "unverified"

**Giải pháp**:

1. Đảm bảo blockchain service đang chạy
2. Kiểm tra chứng chỉ đã được phát hành lên blockchain
3. Đảm bảo hash chứng chỉ khớp với blockchain
4. Thử phát hành lại từ admin panel

#### Lỗi thanh toán

**Vấn đề**: Thanh toán Stripe hoặc PayPal lỗi

**Giải pháp**:

1. Kiểm tra API key trong `.env`
2. Stripe: dùng test key khi phát triển
3. PayPal: dùng sandbox credentials
4. Kiểm tra console trình duyệt cho lỗi JS
5. Tham khảo [STRIPE_SETUP.md](STRIPE_SETUP.md) cho cấu hình chi tiết

#### OAuth không hoạt động

**Vấn đề**: Đăng nhập Google/Facebook thất bại

**Giải pháp**:

1. Cấu hình OAuth trong developer console tương ứng
2. Đặt redirect URI đúng:

   * Google: `http://localhost:8000/api/auth/google/callback`
   * Facebook: `http://localhost:8000/api/auth/facebook/callback`
3. Cập nhật `.env` với client ID và secret

### Vấn đề hiệu năng

#### Trang tải chậm

* Xóa cache trình duyệt và cache Laravel: `php artisan cache:clear`
* Đảm bảo Redis chạy cho session và cache
* Kiểm tra phân bổ tài nguyên Docker (memory/CPU)

#### Video streaming lỗi

* Kiểm tra HLS processing cho video hoàn tất
* Kiểm tra endpoint/CDN lưu trữ
* Đảm bảo băng thông đủ cho streaming

### Hỗ trợ thêm

* Kiểm tra tài liệu có sẵn trong repository
* Xem logs container: `docker compose -f docker-compose.dev.yml logs <service>`
* Mở issue trong repo nếu gặp bug hoặc đề xuất tính năng

---

## Developer Documentation

Detailed developer and operational documentation lives in the `docs/` folder. Please consult the following files before contributing or deploying:

- **Architecture overview**: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- **Code conventions & contribution guide**: [docs/CONTRIBUTING.md](docs/CONTRIBUTING.md)
- **Database schema & ERD**: [docs/DATABASE_SCHEMA.md](docs/DATABASE_SCHEMA.md)
- **Deployment runbook**: [docs/DEPLOYMENT_RUNBOOK.md](docs/DEPLOYMENT_RUNBOOK.md)

If you are preparing a PR, follow the guidelines in `docs/CONTRIBUTING.md` and run the tests described there.
