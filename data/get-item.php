<?php
header('Content-Type: application/json');

$group_id = $_GET['group_id'] ?? 0;
$id = $_GET['id'] ?? 0;

switch ($id) {
    case 1:
        $data = [
            ['id' => 1, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 2, 'file' => '/assets/files/2.jpg', 'name' => 'Animals'],
            ['id' => 3, 'file' => '/assets/files/3.jpg', 'name' => 'Business'],
        ];
    break;

    case 2:
        $data = [
            ['id' => 1, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 2, 'file' => '/assets/files/2.jpg', 'name' => 'Animals'],
            ['id' => 3, 'file' => '/assets/files/3.jpg', 'name' => 'Business'],
            ['id' => 4, 'file' => '/assets/files/4.jpg', 'name' => 'Tech'],
            ['id' => 7, 'file' => '/assets/files/7.jpg', 'name' => 'None'],
        ];
    break;

    case 3:
        $data = [
            ['id' => 6, 'file' => '/assets/files/6.jpg', 'name' => 'Wedding'],
            ['id' => 7, 'file' => '/assets/files/7.jpg', 'name' => 'None'],
        ];
    break;

    default:
        $data = [
            ['id' => 1, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 2, 'file' => '/assets/files/2.jpg', 'name' => 'Animals'],
            ['id' => 3, 'file' => '/assets/files/3.jpg', 'name' => 'Business'],
            ['id' => 4, 'file' => '/assets/files/4.jpg', 'name' => 'Tech'],
            ['id' => 5, 'file' => '/assets/files/5.jpg', 'name' => 'Wedding'],
            ['id' => 6, 'file' => '/assets/files/6.jpg', 'name' => 'None'],
            ['id' => 7, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 8, 'file' => '/assets/files/2.jpg', 'name' => 'None'],
            ['id' => 9, 'file' => '/assets/files/3.jpg', 'name' => 'None'],
            ['id' => 10, 'file' => '/assets/files/4.jpg', 'name' => 'None'],
            ['id' => 11, 'file' => '/assets/files/5.jpg', 'name' => 'None'],
            ['id' => 12, 'file' => '/assets/files/6.jpg', 'name' => 'None'],
            ['id' => 13, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 14, 'file' => '/assets/files/2.jpg', 'name' => 'None'],
            ['id' => 15, 'file' => '/assets/files/3.jpg', 'name' => 'None'],
            ['id' => 16, 'file' => '/assets/files/4.jpg', 'name' => 'None'],
            ['id' => 17, 'file' => '/assets/files/5.jpg', 'name' => 'None'],
            ['id' => 18, 'file' => '/assets/files/6.jpg', 'name' => 'None'],
            ['id' => 19, 'file' => '/assets/files/1.jpg', 'name' => 'None'],
            ['id' => 20, 'file' => '/assets/files/2.jpg', 'name' => 'None'],
            ['id' => 21, 'file' => '/assets/files/3.jpg', 'name' => 'None'],
            ['id' => 22, 'file' => '/assets/files/4.jpg', 'name' => 'None'],
            ['id' => 23, 'file' => '/assets/files/5.jpg', 'name' => 'None'],
            ['id' => 24, 'file' => '/assets/files/6.jpg', 'name' => 'None'],
            ['id' => 25, 'file' => '/assets/files/7.jpg', 'name' => 'None'],
        ];
    break;
}

echo json_encode($data);