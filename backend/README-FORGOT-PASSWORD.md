Mục đích
-------
Tài liệu này chỉ hướng dẫn cấu hình nhanh các biến môi trường và dịch vụ cần thiết để tính năng "Forgot / Reset password" hoạt động trong môi trường develop.

Ghi chú chung
-----------
- File này chỉ hướng dẫn cấu hình (setup). Nó không chứa hướng dẫn triển khai chi tiết hay chạy toàn bộ stack.
- Các lệnh artisan/terminal dùng cho kiểm tra được để ở mức tối thiểu (ví dụ: xóa cache config, kiểm tra command). Nếu bạn muốn các lệnh khởi động/kiểm tra tự động, mình có thể thêm sau.

.env — biến cần cấu hình
-----------------------
Thêm / kiểm tra các biến này trong `backend/.env` (chỉ liệt kê các biến liên quan tính năng forgot-password):

Example (thêm vào `.env`):

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com       # địa chỉ email dùng để gửi
MAIL_PASSWORD=your-app-password           # mật khẩu ứng dụng (app password) hoặc SMTP password từ provider
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="Tên ứng dụng"

FRONTEND_URL=http://localhost:3000       # URL frontend, consumer dùng tạo link reset

# RabbitMQ (dùng cho enqueue gửi mail)
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
# (Tùy cấu hình code của bạn có thể đọc thêm tên queue/exchange từ config hoặc .env nếu cần)

Lấy giá trị ở đâu / lưu ý
------------------------
- MAIL_*:
	- Nếu dùng Gmail: tạo "App password" trong tài khoản Google (nếu bật 2FA). Dùng email của bạn làm `MAIL_USERNAME` và app password làm `MAIL_PASSWORD`.
	- Nếu dùng dịch vụ SMTP khác (SendGrid, Mailgun, SMTP của hosting), lấy host/port/username/password từ trang quản trị dịch vụ đó.
	- `MAIL_FROM_ADDRESS` là địa chỉ người gửi hiển thị trong email reset.

- FRONTEND_URL:
	- URL nơi người dùng sẽ mở link reset (ví dụ `http://localhost:3000`). Consumer sẽ ghép token vào FRONTEND_URL để tạo link reset.

- RABBITMQ_*:
	- Nếu bạn chạy toàn bộ dev stack bằng Docker Compose, thường sẽ có một service tên `rabbitmq` (kiểm tra `docker-compose*.yml`).
	- Mặc định RabbitMQ có management UI tại http://localhost:15672 (nếu mapped) — mặc định user/pass thường là `guest`/`guest` trừ khi docker-compose ghi khác.
	- Nếu backend không thể resolve host `rabbitmq`, kiểm tra tên service trong compose và mạng docker.

Thiết lập RabbitMQ (gợi ý nhanh)
--------------------------------
- Nếu bạn dùng Docker Compose project (dev), thường file `docker-compose.dev.yml` hoặc `docker-compose.yml` đã có service RabbitMQ. Kiểm tra file để biết tên service, ports, và credentials.
- Management UI hữu dụng để kiểm tra queue/exchange: nếu port 15672 được map, vào http://localhost:15672 với user/pass trong compose.
- Queue: code hiện tại enqueues message (ví dụ queue `reset_email` hoặc tên tương ứng) — thường không cần tạo thủ công nếu consumer hoặc publisher khai báo/định nghĩa queue khi connect. Nhưng bạn có thể tạo/kiểm tra queue bằng UI.

Consumer (gửi email từ queue)
-----------------------------
- Project có một consumer console class trong `backend/app/Console/Consumers/ResetEmailConsumer.php`.
- Để biết chính xác artisan command để chạy consumer, mở file đó và tìm `protected $signature = '...';` — copy giá trị đó và chạy `php artisan <signature>` trong container/backend để khởi consumer (hoặc cấu hình supervisor/docker service để chạy liên tục).

Kiểm tra nhanh / lệnh hữu ích
-----------------------------
- Xóa/refresh cache config Laravel để đọc `.env` mới:

```powershell
# trong thư mục backend, hoặc trong shell container backend
php artisan config:clear
php artisan cache:clear
```

- Kiểm tra các artisan command (tìm command consumer nếu không chắc):

```powershell
php artisan list
```

- Kiểm tra logs Laravel (nếu lỗi xảy ra):

```powershell
# xem 200 dòng cuối
Get-Content storage/logs/laravel.log -Tail 200
```

