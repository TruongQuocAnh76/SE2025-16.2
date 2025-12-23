<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 flex flex-col h-full">
    <div class="relative">
      <div class="h-48 flex items-center justify-center bg-gray-50 rounded-t-3xl overflow-hidden p-4">
        <img
          :src="course.thumbnail || '/placeholder-course.jpg'"
          :alt="course.name"
          class="max-h-full max-w-full object-contain rounded-3xl"
        />
      </div>
    </div>

    <div class="p-4 flex flex-col flex-grow">
      <div class="flex justify-between items-start mb-2">
        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 flex-1 mr-2">
          {{ course.name }}
        </h3>
        <span class="text-sm text-gray-500 whitespace-nowrap">
          {{ course.duration }}
        </span>
      </div>

      <p class="text-gray-600 text-sm mb-4 line-clamp-1">
        {{ course.description }}
      </p>

      <div class="flex justify-between items-center mt-auto">
        <span class="text-sm text-gray-500">
          By {{ course.author }}
        </span>
        
        <span class="text-lg font-bold text-brand-primary">
          <template v-if="hasValidDiscount">
            <span class="line-through text-gray-400 mr-2 text-sm">
              ${{ formatPrice(course.price) }}
            </span>
            <span class="text-600">
              ${{ formatPrice(discountedPrice) }}
            </span>
          </template>
          
          <template v-else>
            {{ course.price ? `$${formatPrice(course.price)}` : 'Free' }}
          </template>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface CourseCardProps {
  thumbnail?: string
  duration: string
  name: string
  description: string
  author: string
  price?: number | string // Chấp nhận cả string đề phòng input form chưa convert
  discount?: number | string // Chấp nhận cả string
}

const props = defineProps<{
  course: CourseCardProps
}>()

// 1. Computed kiểm tra xem có giảm giá hợp lệ không
const hasValidDiscount = computed(() => {
  const price = Number(props.course.price);
  const discount = Number(props.course.discount);

  return (
    !isNaN(price) && 
    price > 0 && 
    !isNaN(discount) && 
    discount > 0 && 
    discount < 100
  );
});

// 2. Computed tính giá sau khi giảm
const discountedPrice = computed(() => {
  const price = Number(props.course.price || 0);
  const discount = Number(props.course.discount || 0);

  if (hasValidDiscount.value) {
    // Công thức: Giá gốc * (1 - %giảm/100)
    return price * (1 - discount / 100);
  }
  return price;
});

// 3. Hàm helper để format số (nếu cần hiển thị đẹp, ví dụ 19.99 thay vì 19.99999)
const formatPrice = (value: number | string | undefined) => {
  if (value === undefined) return '0';
  const num = Number(value);
  // Sử dụng Math.round như ý bạn, hoặc toFixed(2) nếu muốn lấy số thập phân
  return Math.round(num); 
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
