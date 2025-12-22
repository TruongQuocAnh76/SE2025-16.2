
export const useMedia = () => {
    const config = useRuntimeConfig()
    const token = useCookie('auth_token')

    const getAuthHeaders = () => {
        if (!token.value) {
            throw new Error('No authentication token found')
        }
        return {
            'Authorization': `Bearer ${token.value}`,
            'Accept': 'application/json',
        }
    }

    /**
     * Get a pre-signed URL for uploading a file
     */
    const getPresignedUrl = async (folder: string, extension: string, contentType: string) => {
        try {
            const data = await $fetch<{ upload_url: string; path: string; url: string }>('/api/media/presigned-url', {
                baseURL: config.public.backendUrl as string,
                method: 'POST',
                headers: {
                    ...getAuthHeaders(),
                    'Content-Type': 'application/json'
                },
                body: {
                    folder,
                    extension,
                    content_type: contentType
                }
            })
            return data
        } catch (error) {
            console.error('Failed to get presigned URL:', error)
            throw error
        }
    }

    /**
     * Upload file to the pre-signed URL
     */
    const uploadToPresignedUrl = async (uploadUrl: string, file: File) => {
        try {
            const response = await fetch(uploadUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': file.type
                },
                body: file,
            })

            if (!response.ok) {
                throw new Error(`Upload failed with status: ${response.status}`)
            }

            return true
        } catch (error) {
            console.error('Failed to upload file to S3/Storage:', error)
            throw error
        }
    }

    /**
     * High-level function to handle the entire upload process
     */
    const uploadFile = async (file: File, folder: string = 'avatars') => {
        try {
            // 1. Get presigned URL
            const extension = file.name.split('.').pop() || 'jpg'
            const { upload_url, path, url } = await getPresignedUrl(folder, extension, file.type)

            // 2. Upload to S3/MinIO
            await uploadToPresignedUrl(upload_url, file)

            // 3. Return the public/accessible URL and path
            return { path, url }
        } catch (error) {
            console.error('File upload failed:', error)
            throw error
        }
    }

    return {
        getPresignedUrl,
        uploadToPresignedUrl,
        uploadFile
    }
}
