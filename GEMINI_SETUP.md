# Cấu hình Gemini AI cho CertChain

## 1. Lấy API Key từ Google AI Studio

1. Truy cập: https://aistudio.google.com/app/apikey
2. Đăng nhập bằng tài khoản Google
3. Click "Create API Key"
4. Chọn project hoặc tạo project mới
5. Copy API key được tạo

## 2. Cấu hình Backend (Laravel)

### Thêm vào file `.env`:

```env
# Google Gemini AI Configuration
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-1.5-flash
```

**Lưu ý:**
- `gemini-1.5-flash`: Model nhanh, phù hợp cho chatbot thời gian thực
- `gemini-1.5-pro`: Model mạnh hơn nhưng chậm hơn và tốn phí hơn
- Free tier của Gemini cho phép 15 requests/phút và 1 triệu tokens/tháng

### Các model có sẵn:

| Model | Tốc độ | Chất lượng | Rate Limit (Free) |
|-------|--------|------------|-------------------|
| gemini-1.5-flash | ⚡⚡⚡ | ⭐⭐⭐ | 15 req/min |
| gemini-1.5-pro | ⚡⚡ | ⭐⭐⭐⭐⭐ | 2 req/min |
| gemini-1.0-pro | ⚡⚡ | ⭐⭐⭐⭐ | 15 req/min |

## 3. Test Backend API

### Test bằng cURL:

```bash
# Test chat thông thường
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "CertChain là gì?"
  }'

# Test chat với context khóa học
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Khóa học này dạy những gì?",
    "course_id": 1
  }'

# Test với lịch sử hội thoại
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Còn về giá cả thì sao?",
    "course_id": 1,
    "conversation_history": [
      {
        "role": "user",
        "content": "Khóa học này dạy những gì?"
      },
      {
        "role": "assistant",
        "content": "Khóa học này dạy về lập trình web..."
      }
    ]
  }'

# Lấy câu hỏi gợi ý
curl -X GET "http://localhost:8000/api/ai/suggested-questions?course_id=1"

# Kiểm tra trạng thái API
curl -X GET http://localhost:8000/api/ai/status
```

### Test bằng Postman/Insomnia:

1. **Chat với AI:**
   - Method: `POST`
   - URL: `http://localhost:8000/api/ai/chat`
   - Body (JSON):
     ```json
     {
       "message": "Giới thiệu về CertChain",
       "course_id": 1
     }
     ```

2. **Lấy câu hỏi gợi ý:**
   - Method: `GET`
   - URL: `http://localhost:8000/api/ai/suggested-questions?course_id=1`

3. **Kiểm tra status:**
   - Method: `GET`
   - URL: `http://localhost:8000/api/ai/status`

## 4. Chạy và Test Frontend

```bash
cd frontend
npm run dev
# hoặc
pnpm dev
```

Sau khi chạy:
1. Mở trình duyệt: http://localhost:3000
2. Ở góc dưới bên phải sẽ có nút chat AI (màu xanh teal với icon tin nhắn)
3. Click vào nút để mở cửa sổ chat
4. Thử chat với AI!

## 5. Tính năng đã triển khai

### Backend:
- ✅ Service `GeminiChatService` để tương tác với Gemini API
- ✅ Controller `AIChatController` xử lý các request
- ✅ Routes API cho chat, suggested questions, và status check
- ✅ Fine-tuning context dựa trên dữ liệu khóa học
- ✅ Hỗ trợ lịch sử hội thoại
- ✅ Format level và giá tiền sang tiếng Việt
- ✅ Xử lý lỗi và logging

### Frontend:
- ✅ Component `AIChatbot.vue` với UI đẹp mắt
- ✅ Composable `useAIChat` quản lý state và logic
- ✅ Lưu/tải lịch sử chat từ localStorage
- ✅ Animation mượt mà
- ✅ Responsive design
- ✅ Loading states và error handling
- ✅ Câu hỏi gợi ý thông minh
- ✅ Support markdown formatting (bold, italic)
- ✅ Luôn hiển thị ở góc dưới bên phải

## 6. Customize

### Thay đổi vị trí chatbot:

Trong `AIChatbot.vue`, dòng đầu tiên của template:

```vue
<!-- Bottom right (default) -->
<div class="fixed bottom-6 right-6 z-[9999]">

<!-- Bottom left -->
<div class="fixed bottom-6 left-6 z-[9999]">

<!-- Top right -->
<div class="fixed top-20 right-6 z-[9999]">
```

### Thay đổi màu sắc:

Tìm và thay thế các class `teal` bằng màu khác:
- `from-teal-500 to-teal-600` → `from-blue-500 to-blue-600`
- `bg-teal-500` → `bg-blue-500`
- `text-teal-600` → `text-blue-600`

### Thay đổi kích thước cửa sổ chat:

```vue
<!-- Default: 400px × 650px -->
<div class="w-[400px] h-[650px]">

<!-- Larger -->
<div class="w-[500px] h-[700px]">

<!-- Smaller -->
<div class="w-[350px] h-[550px]">
```

### Chỉ hiển thị chatbot trên trang cụ thể:

Thay vì thêm vào `default.vue`, thêm vào page cụ thể:

```vue
<!-- frontend/app/domains/courses/pages/courses/[id]/index.vue -->
<template>
  <div>
    <!-- Course content -->
    
    <!-- AI Chatbot specific to this course -->
    <AIChatbot :courseId="courseId" />
  </div>
</template>

<script setup lang="ts">
import AIChatbot from '~/components/AIChatbot.vue'

const route = useRoute()
const courseId = route.params.id as string
</script>
```

## 7. Troubleshooting

### Lỗi "API key invalid":
- Kiểm tra API key trong `.env`
- Đảm bảo không có khoảng trắng thừa
- Kiểm tra quota tại: https://aistudio.google.com/app/apikey

### Lỗi "Rate limit exceeded":
- Free tier: 15 requests/phút
- Đợi 1 phút rồi thử lại
- Hoặc upgrade lên paid plan

### Chatbot không hiển thị:
- Kiểm tra console browser (F12)
- Kiểm tra API backend có chạy không
- Kiểm tra `z-index` (phải là `z-[9999]`)

### Chat không hoạt động:
- Mở Network tab trong DevTools
- Kiểm tra request đến `/api/ai/chat`
- Kiểm tra response có lỗi gì không

## 8. Giới hạn và Best Practices

### Rate Limits (Free tier):
- 15 requests/phút (gemini-1.5-flash)
- 2 requests/phút (gemini-1.5-pro)
- 1 triệu tokens/tháng
- 1,500 requests/ngày

### Best Practices:
- ✅ Giới hạn độ dài tin nhắn (đã set max 2000 characters)
- ✅ Lưu lịch sử chat vào localStorage để tránh mất data
- ✅ Hiển thị loading state khi đang chờ response
- ✅ Handle errors gracefully
- ✅ Không gửi quá nhiều context (đã giới hạn 10 tin nhắn gần nhất)
- ⚠️ Cân nhắc implement rate limiting ở backend
- ⚠️ Monitor usage để tránh vượt quota

## 9. Nâng cấp trong tương lai

### Có thể thêm:
- 🔄 Streaming responses (real-time typing effect)
- 💾 Lưu chat history vào database
- 🔐 Yêu cầu đăng nhập để chat
- 📊 Analytics về câu hỏi thường gặp
- 🌐 Multi-language support
- 🎤 Voice input/output
- 📎 Upload ảnh để hỏi về nội dung
- 🤖 Multiple AI models (GPT-4, Claude, etc.)
- 💬 Chat với nhiều agents khác nhau

## 10. Cost Estimation

### Free Tier:
- ✅ 15 requests/phút (Flash model)
- ✅ 1 triệu tokens/tháng
- ✅ Đủ cho ~1000-2000 conversations/tháng

### Paid Tier (nếu cần):
- gemini-1.5-flash: $0.075 / 1M input tokens, $0.30 / 1M output tokens
- gemini-1.5-pro: $1.25 / 1M input tokens, $5.00 / 1M output tokens

Ước tính: ~$5-20/tháng cho 10,000-50,000 messages

## Tài liệu tham khảo

- Gemini API Docs: https://ai.google.dev/gemini-api/docs
- Get API Key: https://aistudio.google.com/app/apikey
- Pricing: https://ai.google.dev/pricing
- Models: https://ai.google.dev/gemini-api/docs/models/gemini
