<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course;

class RestoreCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Replace the course title below with any sample courses you want to restore
        $sampleCurriculum = <<<'CURR'
Nội dung Học phần:
1. Giới thiệu tổng quan
1.1. Thị giác máy tính (CV) là gì
1.2. CV - Các lĩnh vực ứng dụng
1.3. Xử lý ảnh (IP) và thị giác máy tính (CV)
1.4. Các bước chính trong IP & CV
1..5. Các bài toán chính của CV
2. Biểu diễn ảnh số và các dạng dữ liệu thị giác
2.1. Ảnh số - Các loại ảnh số
2.2. Dữ liệu thị giác nhiều chiều
3. Trích xuất đặc trưng ảnh
3.1. Khái niệm đặc trưng của hình ảnh
3.2. Đặc trưng toàn cục (Global) và đặc trưng cục bộ (Local Feature)
3.3. Một số phương pháp trích xuất đặc trưng không dùng học sâu
3.4. Ứng dụng trích xuất đặc trưng trong đối sánh ảnh
3.5. Các ứng dụng đặc trưng cục bộ - Image matching & Image alignment
4. Mạng CNN và bài toán phân loại ảnh
4.1. Phân loại ảnh & các phương pháp không dựa trên học sâu
4.2. Mạng CNN cho bài toán phân loại
4.3. Một số mạng CNN hiện đại
4.4. Tích chập 3D - Mạng 3D CNN
4.5. Phân loại video - Bài toán nhận dạng hành động
4.5. Ứng dụng 
5. Phát hiện đối tượng trong ảnh
5.1. Phát hiện đối tượng  - Object Detection
5.2. Các phương pháp dạng 2 bước (Two stages object detection models)
5.3. Các phương pháp dạng 1 bước (One stage object detection models)
5.4. Ứng dụng
5.5. Phát hiện và theo vết đối tượng từ dữ liệu Video (Tracking)
6. Phân đoạn đối tượng ảnh - Object Segmentation
6.1. Một số mô hình phân đoạn ảnh
6.2. Ứng dụng
6.3. Ước lượng độ sâu từ ảnh phẳng (Depth estimation)
7. Mô tả hình ảnh
7.1. Bài toán mô tả hình ảnh (Visual - Text)
7.2. Một số mô hình xử lý ngôn ngữ
7.3. Các kiến trúc sinh mô tả ảnh dựa trên học sâu
8. Cơ chế chú ý và mô hình transformer
8.1. Cơ chế chú ý
8.2. Mô hình Transformer cho thị giác máy
8.3. Ứng dụng
9. Sinh dữ liệu hình ảnh bằng mô hình GAN
9.1. Mô hình GAN
9.2. Sinh dữ liệu ảnh
9.3. Ứng dụng
CURR;

        // List of course titles to update (adjust names as necessary)
        $titles = [
            'Complete Vue.js 3 Development Course',
            'Complete Vue.js Development Course',
            'Vue.js Fundamentals (Module 1)'
        ];

        foreach ($titles as $title) {
            $course = Course::where('title', $title)->first();
            if ($course) {
                $course->curriculum = $sampleCurriculum;
                $course->save();
                echo "Updated curriculum for: {$title}\n";
            } else {
                echo "Course not found: {$title}\n";
            }
        }
    }
}
