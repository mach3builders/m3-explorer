<?php
$group = $_POST['group'] ?? 0;
$value = $_POST['value'] ?? null;

if ($group && $value) {
    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => mt_rand(100, 99999999),
            'name' => $value
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'error'
    ]);
}