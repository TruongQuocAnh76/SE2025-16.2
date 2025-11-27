# HLS Adaptive Streaming Implementation

## Overview

This project implements HLS (HTTP Live Streaming) adaptive streaming for course videos, allowing automatic quality switching based on user's network conditions and device capabilities.

## Features

- **Multiple Quality Levels**: 360p, 480p, 720p, 1080p
- **Adaptive Bitrate**: Automatic quality switching based on network conditions
- **6-second Segments**: Optimized for fast startup and smooth streaming
- **Async Processing**: Background video processing using Laravel jobs
- **S3 Storage**: All HLS files stored in S3-compatible storage (LocalStack for development)

## Architecture

### 1. Video Upload Flow

1. **Upload Original Video**: Frontend uploads original video file
2. **Store Original**: Video stored in S3 for backup/reprocessing
3. **Queue Processing**: HLS processing job queued for background execution
4. **Generate HLS**: Video transcoded into multiple qualities with HLS segments
5. **Update Database**: Lesson updated with master playlist URL

### 2. HLS Structure

```
courses/hls/{course-id}/modules/{module-id}/lessons/{lesson-id}/
├── master.m3u8                 # Master playlist (entry point)
├── 360p/
│   ├── playlist.m3u8          # 360p quality playlist
│   ├── segment_000.ts         # Video segments
│   ├── segment_001.ts
│   └── ...
├── 480p/
│   ├── playlist.m3u8
│   └── segments...
├── 720p/
│   ├── playlist.m3u8
│   └── segments...
└── 1080p/
    ├── playlist.m3u8
    └── segments...
```

## Quality Settings

| Quality | Resolution | Video Bitrate | Audio Bitrate | Target Use Case |
|---------|------------|---------------|---------------|----------------|
| 360p    | 640×360    | 800 kbps      | 96 kbps       | Mobile/slow connection |
| 480p    | 854×480    | 1.4 Mbps      | 128 kbps      | Standard mobile |
| 720p    | 1280×720   | 2.8 Mbps      | 128 kbps      | Desktop/good connection |
| 1080p   | 1920×1080  | 5.0 Mbps      | 192 kbps      | High-quality viewing |

## API Endpoints

### Upload Video
```http
POST /api/courses/upload-lesson-video
Content-Type: multipart/form-data

{
    "lesson_id": "uuid",
    "video_path": "courses/original-videos/{course-id}/modules/{module-id}/lessons/{lesson-id}.mp4",
    "hls_base_path": "courses/hls/{course-id}/modules/{module-id}/lessons/{lesson-id}",
    "video_file": [file]
}
```

### Check Processing Status
```http
GET /api/courses/lesson/{lesson-id}/hls-status
```

Response:
```json
{
    "status": "completed|processing|pending|error",
    "message": "Status description",
    "hls_url": "https://s3.endpoint/bucket/courses/hls/.../master.m3u8"
}
```

## Services

### HlsVideoService
Main service responsible for video processing:
- `processVideoToHls()`: Main processing method
- `processQualityLevel()`: Process individual quality
- `generateThumbnail()`: Extract video thumbnail
- `getVideoInfo()`: Get video duration and metadata

### ProcessVideoToHlsJob
Background job for async processing:
- Handles long-running video transcoding
- Automatic retry on failure (3 attempts)
- Progress logging and error handling
- Cleanup of temporary files

## Configuration

### Laravel FFMpeg Config
File: `config/laravel-ffmpeg.php`
```php
return [
    'ffmpeg' => [
        'binaries' => env('FFMPEG_BINARIES', 'ffmpeg'),
        'threads' => 12,
    ],
    'ffprobe' => [
        'binaries' => env('FFPROBE_BINARIES', 'ffprobe'),
    ],
    'timeout' => 3600, // 1 hour
];
```

### Environment Variables
```env
# FFMpeg Configuration
FFMPEG_BINARIES=ffmpeg
FFPROBE_BINARIES=ffprobe

# AWS S3 Configuration (LocalStack for dev)
AWS_ENDPOINT=http://localstack:4566
AWS_BUCKET=certchain-storage
```

## Frontend Integration

### Video Player
Use HLS.js or native HLS support:

```javascript
// Using HLS.js
import Hls from 'hls.js';

const video = document.getElementById('video');
const hlsUrl = 'https://s3.endpoint/bucket/courses/hls/.../master.m3u8';

if (Hls.isSupported()) {
    const hls = new Hls();
    hls.loadSource(hlsUrl);
    hls.attachMedia(video);
} else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    // Native HLS support (Safari)
    video.src = hlsUrl;
}
```

### Upload Flow
```javascript
// 1. Upload video
const formData = new FormData();
formData.append('lesson_id', lessonId);
formData.append('video_path', originalVideoPath);
formData.append('hls_base_path', hlsBasePath);
formData.append('video_file', videoFile);

const uploadResponse = await fetch('/api/courses/upload-lesson-video', {
    method: 'POST',
    body: formData
});

// 2. Poll processing status
const checkStatus = async () => {
    const response = await fetch(`/api/courses/lesson/${lessonId}/hls-status`);
    const data = await response.json();
    
    if (data.status === 'completed') {
        // Video ready for streaming
        loadVideo(data.hls_url);
    } else if (data.status === 'processing') {
        // Still processing, check again in 10 seconds
        setTimeout(checkStatus, 10000);
    }
};
```

## Development Setup

### Docker Requirements
FFMpeg is automatically installed in the Docker containers. The following packages are included:
- `ffmpeg`: Main binary
- `ffmpeg-dev`: Development headers

### Queue Worker
Ensure queue worker is running for background job processing:
```bash
php artisan queue:work --tries=3
```

### LocalStack S3
HLS files are stored in LocalStack S3 for development. Ensure the bucket exists and is configured properly.

## Monitoring and Debugging

### Logs
- HLS processing logs: `storage/logs/laravel.log`
- Job failures: Monitor failed jobs table
- FFMpeg errors: Check job logs for detailed error messages

### Common Issues
1. **FFMpeg not found**: Ensure FFMpeg is installed in container
2. **S3 upload failures**: Check AWS credentials and LocalStack configuration
3. **Processing timeouts**: Increase job timeout for large videos
4. **Quality issues**: Adjust bitrate settings in `HlsVideoService`

## Performance Considerations

- **Processing Time**: ~1-2 minutes per minute of video content
- **Storage Usage**: ~4x original file size (multiple qualities)
- **CPU Usage**: High during processing, consider queue workers scaling
- **Network**: HLS provides automatic quality adaptation for optimal streaming
