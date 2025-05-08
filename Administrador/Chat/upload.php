<?php
$response = ['success' => false];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $_FILES['image']['type'];

    if (in_array($fileType, $allowedTypes)) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . '.' . $ext;
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $uploadPath = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $response['success'] = true;
            $response['imageUrl'] = $uploadPath;
        }
    }
}

echo json_encode($response);
