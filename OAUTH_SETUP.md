# 🔐 Hướng dẫn Setup OAuth Authentication (Google & Facebook)

## 📋 Tổng quan

Hệ thống hỗ trợ đăng nhập/đăng ký qua:
- ✅ **Google OAuth 2.0**
- ✅ **Facebook Login**

---

## 🔵 PHẦN 1: GOOGLE OAUTH SETUP

### Bước 1: Tạo Google Cloud Project

1. Truy cập: **https://console.cloud.google.com**
2. Click **"Select a project"** → **"New Project"**
3. Điền thông tin:
   - **Project name**: `CertChain` (hoặc tên bất kỳ)
   - **Organization**: Để trống (nếu không có)
4. Click **"Create"**

### Bước 2: Enable Google+ API

1. Trong project vừa tạo, vào **"APIs & Services"** → **"Library"**
2. Tìm kiếm: `Google+ API`
3. Click vào **"Google+ API"** → Click **"Enable"**

### Bước 3: Tạo OAuth Credentials

1. Vào **"APIs & Services"** → **"Credentials"**
2. Click **"Create Credentials"** → **"OAuth client ID"**
3. Nếu chưa có OAuth consent screen:
   - Click **"Configure Consent Screen"**
   - Chọn **"External"** → Click **"Create"**
   - Điền thông tin:
     - **App name**: `CertChain`
     - **User support email**: Email của bạn
     - **Developer contact**: Email của bạn
   - Click **"Save and Continue"** → **"Save and Continue"** (bỏ qua Scopes)
   - Thêm Test Users nếu cần → Click **"Save and Continue"**
4. Quay lại **"Credentials"** → **"Create Credentials"** → **"OAuth client ID"**
5. Chọn **"Application type"**: **Web application**
6. Điền thông tin:
   - **Name**: `CertChain Web Client`
   - **Authorized JavaScript origins**:
     ```
     http://localhost:8000
     http://localhost:3000
     ```
   - **Authorized redirect URIs**:
     ```
     http://localhost:8000/api/auth/google/callback
     ```
7. Click **"Create"**
8. Copy **Client ID** và **Client Secret**

### Bước 4: Cập nhật Environment Variables

**Backend (`backend/.env`):**
```env
# Frontend URL for OAuth redirect
FRONTEND_URL=http://localhost:3000

# Google OAuth Configuration
GOOGLE_CLIENT_ID=875985002725-xxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

**Lưu ý**: Nếu chưa có trong `backend/.env.example`, thêm các dòng trên vào file để team khác biết.

### Bước 5: Test Google Login

1. Vào: `http://localhost:3000/auth/login` hoặc `http://localhost:3000/auth/register`
2. Click button **"Continue with Google"** (màu xanh Google)
3. Chọn tài khoản Google
4. Cho phép quyền truy cập (`email`, `profile`)
5. Tự động redirect về: `http://localhost:3000/auth/oauth-callback?token=...&user=...`
6. Frontend lưu token vào localStorage và redirect về trang chủ
7. Đăng nhập thành công! 

---

## 🔵 PHẦN 2: FACEBOOK LOGIN SETUP

### Bước 1: Tạo Facebook App

1. Truy cập: **https://developers.facebook.com**
2. Click **"My Apps"** → **"Create App"**
3. Chọn **"Consumer"** → Click **"Next"**
4. Điền thông tin:
   - **App name**: `CertChain`
   - **App contact email**: Email của bạn
5. Click **"Create App"**

### Bước 2: Add Facebook Login Product

1. Trong Dashboard của app, tìm **"Add a product"**
2. Tìm **"Facebook Login"** → Click **"Set up"**
3. Chọn **"Web"** platform
4. Site URL: `http://localhost:3000`
5. Click **"Save"** → **"Continue"**

### Bước 3: Cấu hình Facebook Login Settings

1. Vào **"Facebook Login"** → **"Settings"** (menu bên trái)
2. Điền **Valid OAuth Redirect URIs**:
   ```
   http://localhost:8000/api/auth/facebook/callback
   ```
3. **Client OAuth Login**: Bật (ON)
4. **Web OAuth Login**: Bật (ON)
5. Click **"Save Changes"**

### Bước 4: Lấy App ID và App Secret

1. Vào **"Settings"** → **"Basic"** (menu bên trái)
2. Copy **App ID**
3. Click **"Show"** ở **App Secret** → Copy **App Secret**

### Bước 5: Thêm Privacy Policy URL (Bắt buộc cho Live Mode)

1. Vẫn ở **"Settings"** → **"Basic"**
2. Scroll xuống **"Privacy Policy URL"**:
   ```
   https://certchain.com/privacy-policy
   ```
   (Hoặc URL privacy policy thật của bạn)
3. Upload **App Icon** (1024x1024px, PNG/JPG)
4. Click **"Save Changes"**

### Bước 6: Thêm Test Users (Development Mode)

1. Vào **"Roles"** → **"Test Users"** (menu bên trái)
2. Click **"Add"**
3. Tạo test users hoặc add tài khoản Facebook thật vào:
   - **Administrators**: Full access
   - **Developers**: Can use app in dev mode
   - **Testers**: Can use app in dev mode

### Bước 7: Chuyển App sang Live Mode (Optional)

⚠️ **Chỉ khi sẵn sàng cho public users!**

1. Vào **"App Review"** → **"Permissions and Features"**
2. Request permissions:
   - `email`
   - `public_profile`
3. Submit for review
4. Sau khi approve, toggle **"App Mode"** từ **Development** → **Live** (góc trên)

### Bước 8: Cập nhật Environment Variables

**Backend (`backend/.env`):**
```env
# Frontend URL for OAuth redirect
FRONTEND_URL=http://localhost:3000

# Facebook OAuth Configuration
FACEBOOK_CLIENT_ID=1941942480059023
FACEBOOK_CLIENT_SECRET=ce72e3f7ab21dcbdcfb5d8f191366cd7
FACEBOOK_REDIRECT_URI=http://localhost:8000/api/auth/facebook/callback
```

**Lưu ý**: Nếu chưa có trong `backend/.env.example`, thêm các dòng trên vào file để team khác biết.

### Bước 9: Test Facebook Login

1. Vào: `http://localhost:3000/auth/login` hoặc `http://localhost:3000/auth/register`
2. Click button **"Continue with Facebook"** (màu xanh Facebook)
3. Đăng nhập Facebook bằng:
   - **Development Mode**: Tài khoản Admin/Developer/Test User đã add ở Bước 6
   - **Live Mode**: Bất kỳ tài khoản Facebook nào
4. Cho phép quyền truy cập (`email`, `public_profile`)
5. Tự động redirect về: `http://localhost:3000/auth/oauth-callback?token=...&user=...`
6. Frontend lưu token vào localStorage và redirect về trang chủ
7. Đăng nhập thành công! ✅

---

## 🔧 Kiến trúc OAuth Flow

### Google OAuth Flow:
```
User clicks "Continue with Google" button
    ↓
Frontend redirects: window.location.href = "http://localhost:8000/api/auth/google"
    ↓
Backend redirects to Google OAuth consent screen
    ↓
User approves → Google redirects to http://localhost:8000/api/auth/google/callback
    ↓
Backend receives auth code → Exchange for user info (email, name, avatar, google_id)
    ↓
Create/Update user in database → Generate Sanctum token
    ↓
Backend redirects: http://localhost:3000/auth/oauth-callback?token=xxx&user={...}
    ↓
Frontend reads token from URL query
    ↓
Store in localStorage: auth_token, user
    ↓
Redirect to home: router.push('/')
    ↓
User logged in ✅
```

### Facebook OAuth Flow:
```
User clicks "Continue with Facebook" button
    ↓
Frontend redirects: window.location.href = "http://localhost:8000/api/auth/facebook"
    ↓
Backend redirects to Facebook OAuth consent screen
    ↓
User approves → Facebook redirects to http://localhost:8000/api/auth/facebook/callback
    ↓
Backend receives auth code → Exchange for user info (email, name, avatar, facebook_id)
    ↓
Create/Update user in database → Generate Sanctum token
    ↓
Backend redirects: http://localhost:3000/auth/oauth-callback?token=xxx&user={...}
    ↓
Frontend reads token from URL query
    ↓
Store in localStorage: auth_token, user
    ↓
Redirect to home: router.push('/')
    ↓
User logged in ✅
```

---

## 🔄 Code Implementation

### Backend Routes (`backend/routes/api.php`):
```php
// OAuth Routes - Need session middleware for Socialite
Route::middleware(['web'])->prefix('auth')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook']);
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback']);
});
```

### Backend Config (`backend/config/services.php`):
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/api/auth/google/callback'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL').'/api/auth/facebook/callback'),
],
```

### Backend Controller (`backend/app/Http/Controllers/AuthController.php`):

**Google Methods:**
- `redirectToGoogle()`: 
  ```php
  return Socialite::driver('google')->redirect();
  ```
- `handleGoogleCallback()`: 
  - Get user info: `Socialite::driver('google')->user()`
  - Create/update user với `google_id`, `first_name`, `last_name`, `avatar`
  - Generate token: `$user->createToken('auth_token')->plainTextToken`
  - Redirect: `FRONTEND_URL/auth/oauth-callback?token=xxx&user={...}`

**Facebook Methods:**
- `redirectToFacebook()`: 
  ```php
  return Socialite::driver('facebook')->redirect();
  ```
- `handleFacebookCallback()`: 
  - Get user info: `Socialite::driver('facebook')->user()`
  - Create/update user với `facebook_id`, `first_name`, `last_name`, `avatar`
  - Generate token: `$user->createToken('auth_token')->plainTextToken`
  - Redirect: `FRONTEND_URL/auth/oauth-callback?token=xxx&user={...}`

### Frontend Component (`frontend/app/domains/auth/components/SocialAuthButtons.vue`):

Button click handlers:
```javascript
const loginWithGoogle = () => {
  window.location.href = `${backendUrl}/api/auth/google`
}

const loginWithFacebook = () => {
  window.location.href = `${backendUrl}/api/auth/facebook`
}
```

Component được dùng trong:
- `/auth/login` page
- `/auth/register` page

### Frontend OAuth Callback (`frontend/app/domains/auth/pages/auth/oauth-callback.vue`):
```vue
<script setup>
const route = useRoute()
const router = useRouter()

onMounted(async () => {
  try {
    const { token, user } = route.query
    
    if (token) {
      // Store token in localStorage
      localStorage.setItem('auth_token', token)
      
      // Parse and store user data
      if (user) {
        const userData = JSON.parse(decodeURIComponent(user))
        localStorage.setItem('user', JSON.stringify(userData))
      }
      
      // Redirect to home
      await router.push('/')
    } else {
      // No token - redirect to login
      router.push('/auth/login')
    }
  } catch (e) {
    console.error('OAuth callback error:', e)
    router.push('/auth/login')
  }
})
</script>
```

---

## 🐛 Troubleshooting

### Google OAuth Issues:

**Problem**: `redirect_uri_mismatch`
- **Solution**: Kiểm tra **Authorized redirect URIs** trong Google Console phải khớp 100% với `GOOGLE_REDIRECT_URI` trong `.env`

**Problem**: `Access blocked: This app's request is invalid`
- **Solution**: Enable **Google+ API** trong Google Cloud Console

**Problem**: `Error 400: invalid_request`
- **Solution**: Xóa cache browser, thử lại hoặc kiểm tra OAuth consent screen đã setup chưa

### Facebook OAuth Issues:

**Problem**: `URL Blocked: This redirect failed because the redirect URI is not whitelisted`
- **Solution**: Thêm `http://localhost:8000/api/auth/facebook/callback` vào **Valid OAuth Redirect URIs** trong Facebook App Settings

**Problem**: `App Not Setup: This app is still in development mode`
- **Solution**: Thêm tài khoản test vào **Roles** → **Test Users** hoặc chuyển app sang **Live Mode**

**Problem**: `Can't Load URL: The domain of this URL isn't included in the app's domains`
- **Solution**: Trong Facebook Login Settings, thêm domain vào **App Domains** và **Site URL**

---

## 📊 Environment Variables Summary

### Backend `.env`:
```env
# Laravel App
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost:8000

# Frontend URL - QUAN TRỌNG cho OAuth redirect!
FRONTEND_URL=http://localhost:3000

# Google OAuth
GOOGLE_CLIENT_ID=875985002725-xxxxxxxxxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxxx
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=1941942480059023
FACEBOOK_CLIENT_SECRET=ce72e3f7ab21dcbdcfb5d8f191366cd7
FACEBOOK_REDIRECT_URI=http://localhost:8000/api/auth/facebook/callback
```

### Backend `.env.example` (Template cho team):
```env
FRONTEND_URL=http://localhost:3000

# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Facebook OAuth Configuration
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/api/auth/facebook/callback
```

**Lưu ý**: Frontend không cần .env cho OAuth vì chỉ redirect tới backend endpoint!

---

## ✅ Testing Checklist

### Google OAuth:
- [ ] Tạo Google Cloud Project thành công
- [ ] Enable Google+ API
- [ ] Tạo OAuth credentials (Client ID + Secret)
- [ ] Thêm Authorized JavaScript origins: `http://localhost:8000`, `http://localhost:3000`
- [ ] Thêm Authorized redirect URI: `http://localhost:8000/api/auth/google/callback`
- [ ] Copy Client ID và Client Secret
- [ ] Update `backend/.env` với Google credentials
- [ ] Update `backend/.env.example` (template cho team)
- [ ] Thêm `FRONTEND_URL=http://localhost:3000` vào `.env`
- [ ] Restart backend: `docker compose -f docker-compose.dev.yml restart backend`
- [ ] Test login: Click "Continue with Google" button
- [ ] Redirect về `/auth/oauth-callback` với token
- [ ] Token được lưu vào `localStorage.auth_token`
- [ ] User data được lưu vào `localStorage.user`
- [ ] Redirect về trang chủ thành công
- [ ] User hiển thị đúng (avatar, name, email)

### Facebook OAuth:
- [ ] Tạo Facebook App thành công
- [ ] Add Facebook Login product
- [ ] Cấu hình Site URL: `http://localhost:3000`
- [ ] Thêm Valid OAuth Redirect URI: `http://localhost:8000/api/auth/facebook/callback`
- [ ] Enable Client OAuth Login và Web OAuth Login
- [ ] Copy App ID và App Secret
- [ ] Update `backend/.env` với Facebook credentials
- [ ] Update `backend/.env.example` (template cho team)
- [ ] Thêm `FRONTEND_URL=http://localhost:3000` vào `.env`
- [ ] Add Test Users (Development Mode) hoặc Admin role
- [ ] Restart backend: `docker compose -f docker-compose.dev.yml restart backend`
- [ ] Test login với Test User: Click "Continue with Facebook" button
- [ ] Redirect về `/auth/oauth-callback` với token
- [ ] Token được lưu vào `localStorage.auth_token`
- [ ] User data được lưu vào `localStorage.user`
- [ ] Redirect về trang chủ thành công
- [ ] User hiển thị đúng (avatar, name, email)

---

## 🚀 Production Deployment

### Google OAuth Production:

**Bước 1**: Update **Authorized JavaScript origins** với production domain:
```
https://certchain.com
https://api.certchain.com
```

**Bước 2**: Update **Authorized redirect URIs** với production domain:
```
https://api.certchain.com/api/auth/google/callback
```

**Bước 3**: Update production `.env`:
```env
APP_URL=https://api.certchain.com
FRONTEND_URL=https://certchain.com
GOOGLE_REDIRECT_URI=https://api.certchain.com/api/auth/google/callback
```

**Bước 4**: Test trên production domain

---

### Facebook OAuth Production:

**Bước 1**: Hoàn thành **App Review** cho permissions:
- Request `email` permission
- Request `public_profile` permission
- Submit for Facebook review (có thể mất vài ngày)

**Bước 2**: Upload **Privacy Policy** và **Terms of Service** (BẮT BUỘC):
- Tạo Privacy Policy page: `https://certchain.com/privacy-policy`
- Tạo Terms of Service page: `https://certchain.com/terms`
- Upload vào Facebook App Settings → Basic

**Bước 3**: Upload **App Icon** (1024x1024px, PNG/JPG)

**Bước 4**: Chuyển App Mode từ **Development** → **Live**:
- Vào App Dashboard → Settings (góc trên)
- Toggle từ "Development" sang "Live"

**Bước 5**: Update **Site URL** và **App Domains**:
```
Site URL: https://certchain.com
App Domains: certchain.com
```

**Bước 6**: Update **Valid OAuth Redirect URIs**:
```
https://api.certchain.com/api/auth/facebook/callback
```

**Bước 7**: Update production `.env`:
```env
APP_URL=https://api.certchain.com
FRONTEND_URL=https://certchain.com
FACEBOOK_REDIRECT_URI=https://api.certchain.com/api/auth/facebook/callback
```

**Bước 8**: Test trên production domain với real Facebook accounts

---

## ⚠️ Security Best Practices

1. **KHÔNG BAO GIỜ** commit Client Secret lên GitHub
   - Client Secret phải ở trong `.env` (đã có trong `.gitignore`)
   - Chỉ commit `.env.example` với placeholder values

2. **Dùng environment variables** cho tất cả credentials
   - Backend: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`
   - Frontend: Không cần env vars (chỉ redirect tới backend)

3. **Validate redirect URIs nghiêm ngặt**
   - Google Console và Facebook App Settings phải match 100% với `.env`
   - Development: `http://localhost:8000/api/auth/{provider}/callback`
   - Production: `https://api.certchain.com/api/auth/{provider}/callback`

4. **Limit OAuth scopes** chỉ những gì cần thiết
   - Google: `email`, `profile` (default)
   - Facebook: `email`, `public_profile`

5. **CSRF protection** (đã có sẵn trong Laravel)
   - Laravel tự động handle CSRF với session middleware

6. **Use HTTPS cho production** (BẮT BUỘC!)
   - OAuth providers yêu cầu HTTPS cho production
   - Development có thể dùng HTTP

7. **Rotate secrets định kỳ**
   - Đổi Client Secret mỗi 3-6 tháng
   - Regenerate trong Google Console / Facebook Settings

8. **Monitor failed login attempts**
   - Log tất cả OAuth errors trong `storage/logs/laravel.log`
   - Check `Log::error()` trong AuthController

9. **Rate limiting** cho OAuth endpoints
   - Laravel throttle middleware (nếu cần thêm)

10. **Log tất cả OAuth activities**
    - Success: User created/logged in với provider nào
    - Error: Log exception message và redirect về login page

---

## 📚 Resources

### Google OAuth:
- Google Cloud Console: https://console.cloud.google.com
- OAuth 2.0 Docs: https://developers.google.com/identity/protocols/oauth2
- Scopes: https://developers.google.com/identity/protocols/oauth2/scopes

### Facebook Login:
- Facebook Developers: https://developers.facebook.com
- Login Docs: https://developers.facebook.com/docs/facebook-login
- App Review: https://developers.facebook.com/docs/app-review

### Laravel Socialite:
- Documentation: https://laravel.com/docs/socialite
- GitHub: https://github.com/laravel/socialite
