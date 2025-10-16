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