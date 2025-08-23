<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('save_image')) {
    /**
     * Lưu ảnh vào folder với tên duy nhất
     * @param array|string $image $_FILES['file'] hoặc path tạm
     * @param string $save_path folder lưu ảnh, ví dụ: FCPATH . 'uploads/wi/'
     * @return string full path của ảnh vừa lưu
     * @throws Exception nếu ảnh quá 20MB hoặc không lưu được
     */
    function save_image($image, $save_path) {
        // Kiểm tra folder có tồn tại, nếu chưa có thì tạo
        if (!is_dir($save_path)) {
            if (!mkdir($save_path, 0755, true)) {
                throw new Exception("Không tạo được folder: $save_path");
            }
        }

        // Nếu là $_FILES upload
        if (is_array($image) && isset($image['tmp_name'])) {
            $tmp_file = $image['tmp_name'];
            $original_name = $image['name'];
            $size = $image['size'];
        } else if (is_string($image)) {
            // nếu truyền path tạm
            if (!file_exists($image)) {
                throw new Exception("File không tồn tại: $image");
            }
            $tmp_file = $image;
            $original_name = basename($image);
            $size = filesize($image);
        } else {
            throw new Exception("Tham số truyền vào không hợp lệ.");
        }

        // Kiểm tra size <= 20MB
        if ($size > 20 * 1024 * 1024) {
            throw new Exception("File quá lớn (max 20MB)");
        }

        // Lấy đuôi file
        $ext = pathinfo($original_name, PATHINFO_EXTENSION);

        // Tạo tên file duy nhất
        $random_str = bin2hex(random_bytes(5)); // 10 ký tự hex
        $new_name = date('Ymd_His') . '_' . $random_str . ($ext ? '.' . $ext : '');

        $full_path = rtrim($save_path, '/') . '/' . $new_name;

        if (!move_uploaded_file($tmp_file, $full_path)) {
            // Nếu là path tạm copy
            if (!copy($tmp_file, $full_path)) {
                throw new Exception("Không lưu được file vào: $full_path");
            }
        }

            // 👉 Trả về relative path thay vì full path
    // Ví dụ: "uploads/working_instructions/20250822_070000_ab12cd34ef.png"
    $relative = str_replace(FCPATH, '', $full_path);
    return str_replace('\\', '/', $relative); // normalize cho Windows
    }
}

if (!function_exists('save_images')) {
    /**
     * Lưu nhiều ảnh vào folder với tên duy nhất
     * @param array $files $_FILES['fieldname'] (mảng các file)
     * @param string $save_path folder lưu ảnh
     * @return array ['uploaded' => [paths], 'errors' => [messages]]
     */
    function save_images($files, $save_path) {
        $uploaded_paths = [];
        $errors = [];

        // Process each uploaded file
        foreach ($files['name'] as $key => $name) {
            // Create a single file array for each uploaded file
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];

            // Skip if file has error
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading file {$name}: " . upload_error_message($file['error']);
                continue;
            }

            try {
                // Upload single file
                $path = save_image($file, $save_path);
                $uploaded_paths[] = $path;
            } catch (Exception $e) {
                $errors[] = "Error uploading file {$name}: " . $e->getMessage();
            }
        }

        return [
            'uploaded' => $uploaded_paths,
            'errors' => $errors
        ];
    }
}

if (!function_exists('upload_error_message')) {
    /**
     * Get upload error message
     * @param int $error_code
     * @return string
     */
    function upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}