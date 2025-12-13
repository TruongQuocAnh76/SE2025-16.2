# Hướng dẫn Fine-tune Gemini AI cho CertChain

## Lỗi hiện tại và Giải pháp

### Lỗi: "Đã có lỗi xảy ra - Xin lỗi, tôi không thể trả lời câu hỏi này lúc này"

Có thể do một trong các nguyên nhân sau:

## 1. Kiểm tra Backend Server

```powershell
# Vào thư mục backend
cd D:\GIT\SE2025-16.2\backend

# Clear cache config
php artisan config:clear

# Khởi động server (nếu chưa chạy)
php artisan serve
```

Backend phải chạy trên: `http://localhost:8000`

## 2. Kiểm tra API Key

### Xác minh API Key đã được cấu hình:
```powershell
# Kiểm tra file .env
Get-Content backend\.env | Select-String "GEMINI"
```

Kết quả phải có:
```
GEMINI_API_KEY=AIzaSyD0bUf6l0OuxOYlD4WUizZCBOpYkFy4_rg
GEMINI_MODEL=gemini-1.5-flash
```

### Test API Key trực tiếp:
```powershell
# Test với PowerShell
$body = @{
    message = "Xin chào"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://localhost:8000/api/ai/chat" `
    -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body $body `
    -UseBasicParsing | Select-Object -ExpandProperty Content
```

### Kiểm tra status:
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/api/ai/status" `
    -UseBasicParsing | Select-Object -ExpandProperty Content
```

Kết quả mong đợi:
```json
{
  "status": "active",
  "message": "Gemini AI is ready"
}
```

## 3. Kiểm tra Logs

```powershell
# Xem log lỗi mới nhất
Get-Content backend\storage\logs\laravel.log -Tail 50
```

Tìm các dòng có `Gemini` hoặc `AI Chat Error`

## 4. Test từng bước

### Bước 1: Test API trực tiếp với curl (nếu có Git Bash)

```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d "{\"message\":\"Xin chào\"}"
```

### Bước 2: Test với course_id

```powershell
$body = @{
    message = "Khóa học này dạy gì?"
    course_id = 1
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://localhost:8000/api/ai/chat" `
    -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body $body `
    -UseBasicParsing | Select-Object -ExpandProperty Content
```

### Bước 3: Test frontend

1. Mở browser: http://localhost:3000
2. Mở DevTools (F12) → Network tab
3. Click nút AI Chat ở góc dưới phải
4. Gửi tin nhắn "Xin chào"
5. Xem request/response trong Network tab

## 5. Fine-tune Context cho Khóa học

Context đã được fine-tune trong `GeminiChatService.php`:

```php
private function buildCourseContext(?int $courseId): string
{
    // Nếu có courseId → Load full data: title, description, modules, lessons, quizzes
    // Gemini sẽ trả lời dựa trên context này
    
    // Format tiếng Việt cho:
    // - Level (Beginner → Người mới bắt đầu)
    // - Price (với discount)
    // - Module và lesson details
}
```

### Để tùy chỉnh response, sửa trong `buildCourseContext()`:

```php
// Thêm hướng dẫn cụ thể
$context .= "\n=== HƯỚNG DẪN TRẢ LỜI ===\n";
$context .= "- Luôn trả lời bằng tiếng Việt\n";
$context .= "- Thân thiện, nhiệt tình và chuyên nghiệp\n";
$context .= "- Sử dụng emoji phù hợp\n";
// ... thêm rules khác
```

## 6. Tùy chỉnh Model và Parameters

Trong `GeminiChatService.php` → method `chat()`:

```php
'generationConfig' => [
    'temperature' => 0.7,        // 0.0-1.0: cao = sáng tạo, thấp = chính xác
    'topK' => 40,                // Số từ top xem xét
    'topP' => 0.95,              // Xác suất tích lũy
    'maxOutputTokens' => 1024,   // Độ dài response tối đa
],
```

### Ví dụ tùy chỉnh:

**Để AI trả lời ngắn gọn hơn:**
```php
'maxOutputTokens' => 512,
'temperature' => 0.5,
```

**Để AI sáng tạo hơn:**
```php
'temperature' => 0.9,
'topK' => 60,
```

**Để AI chính xác hơn (ít hallucination):**
```php
'temperature' => 0.3,
'topK' => 20,
'topP' => 0.9,
```

## 7. Debug Mode

Thêm logging để debug:

```php
// Trong GeminiChatService.php → chat()
Log::info('AI Chat Request', [
    'message' => $message,
    'course_id' => $courseId,
    'context_length' => strlen($context)
]);

// Sau khi nhận response
Log::info('AI Chat Response', [
    'success' => true,
    'message_length' => strlen($text),
    'usage' => $data['usageMetadata'] ?? null
]);
```

Xem log:
```powershell
Get-Content backend\storage\logs\laravel.log -Tail 100 | Select-String "AI Chat"
```

## 8. Troubleshooting thông thường

### Lỗi: "Cannot assign null to property"
✅ Đã fix: Thay đổi API key name trong `.env`

### Lỗi: "Failed to get response from Gemini AI"
**Nguyên nhân:**
- API key sai
- Vượt rate limit (15 req/min)
- Network issue

**Giải pháp:**
1. Verify API key tại: https://aistudio.google.com/app/apikey
2. Đợi 1 phút rồi thử lại
3. Check internet connection

### Lỗi: "404 Not Found" khi gọi API
**Nguyên nhân:** Backend chưa chạy hoặc URL sai

**Giải pháp:**
```powershell
# Kiểm tra backend có chạy không
curl http://localhost:8000/api

# Nếu không có response → start backend
cd backend
php artisan serve
```

### Frontend không kết nối được backend
**Kiểm tra CORS:**

```php
// backend/config/cors.php
'allowed_origins' => ['http://localhost:3000'],
```

**Kiểm tra backend URL trong frontend:**
```typescript
// frontend/nuxt.config.ts
export default defineNuxtConfig({
  runtimeConfig: {
    public: {
      backendUrl: 'http://localhost:8000'
    }
  }
})
```

## 9. Test Production-like

### Với nhiều messages liên tiếp:
```powershell
# Message 1
$body1 = @{
    message = "Khóa học có gì?"
    course_id = 1
} | ConvertTo-Json

$response1 = Invoke-WebRequest -Uri "http://localhost:8000/api/ai/chat" `
    -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body $body1 `
    -UseBasicParsing

# Message 2 với history
$body2 = @{
    message = "Giá bao nhiêu?"
    course_id = 1
    conversation_history = @(
        @{
            role = "user"
            content = "Khóa học có gì?"
        },
        @{
            role = "assistant"
            content = "Khóa học này bao gồm..."
        }
    )
} | ConvertTo-Json -Depth 10

$response2 = Invoke-WebRequest -Uri "http://localhost:8000/api/ai/chat" `
    -Method POST `
    -Headers @{"Content-Type"="application/json"} `
    -Body $body2 `
    -UseBasicParsing

$response2.Content
```

## 10. Checklist trước khi test

- [ ] Backend đang chạy (`php artisan serve`)
- [ ] Frontend đang chạy (`pnpm dev`)
- [ ] `GEMINI_API_KEY` đã được set trong `.env`
- [ ] Đã chạy `php artisan config:clear`
- [ ] API key hợp lệ (test tại Google AI Studio)
- [ ] Có khóa học trong database (ít nhất 1 course)
- [ ] Network không bị block Gemini API
- [ ] Đã check logs không có lỗi nghiêm trọng

## 11. Command nhanh để debug

```powershell
# All-in-one check
cd D:\GIT\SE2025-16.2

# Check backend
Write-Host "=== Backend Status ===" -ForegroundColor Green
Get-Process | Where-Object {$_.ProcessName -like "*php*"} | Select-Object Id, ProcessName

Write-Host "`n=== Gemini Config ===" -ForegroundColor Green
Get-Content backend\.env | Select-String "GEMINI"

Write-Host "`n=== Test API Status ===" -ForegroundColor Green
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/ai/status" -UseBasicParsing -TimeoutSec 3
    $response.Content
} catch {
    Write-Host "Backend not responding!" -ForegroundColor Red
}

Write-Host "`n=== Recent Errors ===" -ForegroundColor Green
Get-Content backend\storage\logs\laravel.log -Tail 20 | Select-String "ERROR"
```

Lưu script này vào file `debug-ai.ps1` và chạy:
```powershell
.\debug-ai.ps1
```

## Kết luận

Nếu vẫn gặp lỗi sau khi làm theo hướng dẫn trên:

1. Copy toàn bộ output từ các lệnh test
2. Copy nội dung từ `laravel.log` (20-50 dòng cuối)
3. Copy screenshot từ DevTools → Network tab
4. Gửi cho tôi để debug chi tiết hơn
