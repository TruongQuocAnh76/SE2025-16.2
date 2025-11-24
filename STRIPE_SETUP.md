# 🎯 Hướng dẫn Setup Stripe Payment

## 📋 Bước 1: Đăng ký Stripe Account (MIỄN PHÍ)

1. Truy cập: **https://stripe.com**
2. Click "Start now" hoặc "Sign up"
3. Điền thông tin:
   - Email
   - Tên
   - Quốc gia: **America** (hoặc bất kỳ)
   - Mật khẩu

## 🔑 Bước 2: Lấy API Keys (Test Mode)

1. Sau khi đăng nhập, bạn sẽ ở **Dashboard**
2. Đảm bảo đang ở chế độ **"Test mode"** (toggle ở góc trên bên phải)
3. Click vào **"Developers"** menu → **"API keys"**
4. Bạn sẽ thấy 2 keys:

```
Publishable key: pk_test_51... (dùng cho frontend)
Secret key: sk_test_51... (dùng cho backend)
```

5. Click "Reveal test key" để xem Secret key
6. Copy cả 2 keys

## ⚙️ Bước 3: Cập nhật Environment Variables

### Backend (.env file)
```env
STRIPE_SECRET_KEY=sk_test_51xxxxxxxxxxxxx  # Secret key từ Stripe
STRIPE_PUBLISHABLE_KEY=pk_test_51xxxxxxxxx  # Publishable key từ Stripe
```

### Frontend (nuxt.config.ts)
Thêm vào `runtimeConfig.public`:
```typescript
stripePublishableKey: process.env.STRIPE_PUBLISHABLE_KEY || 'pk_test_...'
```

## 🧪 Bước 4: Test Cards (Dùng trong Test Mode)

Stripe cung cấp các test card numbers để test:

### ✅ Thành công (Successful Payment)
```
Card Number: 4242 4242 4242 4242
Expiry: 12/34 (bất kỳ ngày trong tương lai)
CVC: 123 (bất kỳ 3 số)
ZIP: 12345 (bất kỳ)
```

### ❌ Card bị từ chối (Declined)
```
Card Number: 4000 0000 0000 0002
Expiry: 12/34
CVC: 123
```

### 🔒 Yêu cầu 3D Secure (Authentication Required)
```
Card Number: 4000 0027 6000 3184
Expiry: 12/34
CVC: 123
```

### 💰 Không đủ tiền (Insufficient Funds)
```
Card Number: 4000 0000 0000 9995
Expiry: 12/34
CVC: 123
```

## 🚀 Bước 5: Test Payment Flow

1. Vào trang payment: `http://localhost:3000/payment?type=MEMBERSHIP`
2. Chọn **Visa** hoặc **Mastercard**
3. Nhập thông tin:
   ```
   Card Number: 4242 4242 4242 4242
   Expiry Date: 12/34
   CVC: 123
   Name: Test User
   ```
4. Click "Confirm Payment"
5. Xem console log để kiểm tra payment intent
6. Payment sẽ thành công và redirect về membership page

## 📊 Bước 6: Xem Payments trong Stripe Dashboard

1. Vào **Stripe Dashboard** → **Payments**
2. Bạn sẽ thấy danh sách các test payments
3. Click vào từng payment để xem chi tiết:
   - Amount
   - Customer info
   - Card type
   - Metadata (payment_id, user_id, etc.)

## 🎉 Chuyển sang Live Mode (Production)

Khi sẵn sàng deploy production và nhận tiền thật:

### Bước 1: Hoàn thành Business Verification
1. Toggle sang **"Live mode"** trong Stripe Dashboard (góc trên bên phải)
2. Stripe sẽ yêu cầu **Business verification**:
   - Business/Personal information
   - Tax ID (hoặc SSN cho cá nhân)
   - Bank account để nhận tiền
   - Business documents (nếu là công ty)
3. Submit và đợi Stripe approve (thường 1-2 ngày)

### Bước 2: Lấy Live API Keys
1. Sau khi được approve, vào **Developers** → **API keys**
2. Đảm bảo đang ở **Live mode** (không phải Test mode)
3. Copy 2 keys:
   ```
   Publishable key: pk_live_51... (dùng cho frontend)
   Secret key: sk_live_51... (dùng cho backend)
   ```

### Bước 3: Cập nhật Environment Variables

**Backend (.env file):**
```env
# Thay test keys bằng live keys
STRIPE_SECRET_KEY=sk_live_51xxxxxxxxxxxxx  # Live secret key
STRIPE_PUBLISHABLE_KEY=pk_live_51xxxxxxxxx  # Live publishable key
```

**Frontend (.env file):**
```env
# Thay test key bằng live key
STRIPE_PUBLISHABLE_KEY=pk_live_51xxxxxxxxx  # Live publishable key
```

### Bước 4: Bỏ Test Mode trong Code

**Frontend (`frontend/app/domains/payment/pages/payment.vue`):**
```typescript
// Đổi từ true sang false
const STRIPE_TEST_MODE = false  // Enable production mode
```

### Bước 5: Test với Real Cards
⚠️ **LƯU Ý**: Ở Live mode, dùng **REAL credit cards** sẽ **CHARGE TIỀN THẬT**!

Khuyến nghị:
- Test với card có balance thấp trước
- Hoặc dùng card của chính bạn để test
- Có thể refund sau nếu cần

### Bước 6: Deploy & Monitor
1. Deploy code lên production server
2. Monitor payments trong **Stripe Dashboard** (Live mode)
3. Setup webhook endpoints cho production domain
4. Test toàn bộ flow một lần nữa

## 📊 So sánh Test Mode vs Live Mode

| Feature | Test Mode | Live Mode |
|---------|-----------|-----------|
| **API Keys** | `pk_test_...` / `sk_test_...` | `pk_live_...` / `sk_live_...` |
| **Cards** | Test cards (4242...) | Real credit cards |
| **Money** | ❌ Không charge tiền thật | ✅ Charge tiền thật |
| **Dashboard** | Separate test data | Real transactions |
| **Verification** | ❌ Không cần | ✅ Cần business verification |
| **Refunds** | Fake refunds | Real refunds |
| **Webhooks** | Test webhooks | Production webhooks |

## 🔧 Code Changes for Production

### 1. Frontend Changes
**File: `frontend/app/domains/payment/pages/payment.vue`**
```diff
- const STRIPE_TEST_MODE = true   // Test mode
+ const STRIPE_TEST_MODE = false  // Production mode
```

### 2. Environment Variables
**File: `backend/.env`**
```diff
- STRIPE_SECRET_KEY=sk_test_51xxxxx...
- STRIPE_PUBLISHABLE_KEY=pk_test_51xxxxx...
+ STRIPE_SECRET_KEY=sk_live_51xxxxx...
+ STRIPE_PUBLISHABLE_KEY=pk_live_51xxxxx...
```

**File: `frontend/.env`**
```diff
- STRIPE_PUBLISHABLE_KEY=pk_test_51xxxxx...
+ STRIPE_PUBLISHABLE_KEY=pk_live_51xxxxx...
```

### 3. Restart Services
```bash
docker compose -f docker-compose.yml restart backend frontend
```

## ⚠️ PRODUCTION CHECKLIST

Trước khi deploy production, đảm bảo:

- [ ] ✅ Stripe account đã được verify
- [ ] ✅ Đã lấy Live API keys (pk_live_... và sk_live_...)
- [ ] ✅ Đã update tất cả .env files với live keys
- [ ] ✅ Đã đổi `STRIPE_TEST_MODE = false` trong code
- [ ] ✅ **KHÔNG** commit live keys lên GitHub
- [ ] ✅ Đã test payment flow trên staging environment
- [ ] ✅ Đã setup monitoring/logging cho payments
- [ ] ✅ Đã setup webhook endpoints cho production domain
- [ ] ✅ Đã thông báo team về live deployment
- [ ] ✅ Sẵn sàng handle customer support cho payment issues

## 📝 Testing Checklist

- [ ] Đăng ký Stripe account thành công
- [ ] Lấy được test API keys
- [ ] Cập nhật .env với secret key
- [ ] Test payment với card `4242 4242 4242 4242` thành công
- [ ] Xem được payment trong Stripe Dashboard
- [ ] Test payment failed với card `4000 0000 0000 0002`
- [ ] Enrollment tự động sau khi thanh toán course
- [ ] Membership được activate sau khi thanh toán

## 🔗 Resources

- Stripe Dashboard: https://dashboard.stripe.com
- Test Cards: https://stripe.com/docs/testing
- API Docs: https://stripe.com/docs/api
- Webhooks: https://stripe.com/docs/webhooks

## ⚠️ Important Notes

1. **KHÔNG BAO GIỜ** commit secret key lên GitHub!
2. Luôn dùng **Test Mode** khi development
3. Test cards chỉ hoạt động trong Test Mode
4. Live Mode cần verification và charge tiền thật
5. Có thể dùng test mode mãi mãi (không giới hạn)
