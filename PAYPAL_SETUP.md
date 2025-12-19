#  Hướng dẫn Setup & Tích hợp PayPal (Sandbox)

Tài liệu này hướng dẫn cách cấu hình tính năng thanh toán PayPal cho dự án CertChain (Backend Laravel + Frontend Nuxt.js).

---

##  Phần 1: Chuẩn bị Tài khoản PayPal (Developer)

Để test thanh toán mà không mất tiền thật, bạn cần tạo môi trường Sandbox.

1.  **Truy cập Dashboard**: Đăng nhập tại [developer.paypal.com](https://developer.paypal.com).
2.  **Tạo Sandbox Accounts** (Menu trái -> Testing Tools -> Sandbox Accounts):
    *   Tạo 1 tài khoản **Business** (Merchant): Đóng vai trò người bán (nhận tiền).
    *   Tạo 1 tài khoản **Personal** (Buyer): Đóng vai trò người mua (trả tiền test).
    *   *Lưu ý: Chọn Region là **US** để tránh lỗi tiền tệ.*

3.  **Lấy API Credentials**:
    *   Vào menu **Apps & Credentials**.
    *   Chọn tab **Sandbox**.
    *   Nhấn **Create App**.
    *   Đặt tên App (vd: `CertChain Local`) và chọn Sandbox Business Account vừa tạo ở trên.
    *   Sau khi tạo xong, copy 2 thông tin quan trọng:
        *   **Client ID**
        *   **Secret**

---

##  Phần 2: Cấu hình Dự án (Quan trọng)

Bạn cần cấu hình key cho cả **Backend** (để xử lý giao dịch) và **Frontend** (để hiển thị nút thanh toán).

### 1. Cấu hình Backend (Laravel)
Mở file `backend/.env` và thêm/sửa các dòng sau:

```ini
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=paste_client_id_cua_ban_vao_day
PAYPAL_CLIENT_SECRET=paste_secret_cua_ban_vao_day
```

### 2. Cấu hình Frontend (Nuxt.js)
Mở file `frontend/.env` và thêm dòng sau (chỉ cần Client ID):

```ini
PAYPAL_CLIENT_ID=paste_client_id_cua_ban_vao_day
```


---

##  Phần 3: Khởi động lại Server

Vì Nuxt.js đọc biến môi trường lúc khởi động, bạn **BẮT BUỘC** phải restart lại server frontend sau khi sửa file `.env`.

1.  Mở terminal đang chạy frontend.
2.  Nhấn `Ctrl + C` để tắt.
3.  Chạy lại lệnh:
    ```bash
    npm run dev
    ```

---

##  Phần 4: Hướng dẫn Test Thanh toán

1.  Truy cập trang thanh toán trên web (ví dụ: mua khóa học hoặc Membership).
2.  Chọn phương thức **PayPal** -> Nút vàng PayPal sẽ hiện ra.
3.  Click vào nút -> Một cửa sổ popup hiện lên.
4.  Đăng nhập bằng tài khoản **Personal Sandbox** (Buyer) bạn đã tạo ở Phần 1.
5.  Nhấn **Complete Purchase**.
6.  Hệ thống sẽ xử lý và kích hoạt khóa học/membership cho bạn ngay lập tức.

---


