<?php
$authFilePath = __DIR__ . '/a.json';
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) exit;

$action = $input['action'];
$dict = $input['dict'];

$filename = $dict === 'es' ? 'es.json' : 'ru.json';
$transKey = $dict === 'es' ? 'spanish' : 'russian';

if (!file_exists($filename)) file_put_contents($filename, '[]');
$data = json_decode(file_get_contents($filename), true);
if (!is_array($data)) $data = [];

if ($action === 'load') {
    echo json_encode($data);
    exit;
}

// Read code from json
$authData = file_exists($authFilePath) ? json_decode(file_get_contents($authFilePath)) : [];
$expectedPassword = $authData->p ?? '';
$p = isset($input['p']) ? $input['p'] : null;
if ($p !== $expectedPassword) {
    echo json_encode(['error' => 'Authentication failed']);
    exit;
}

$new_en = $input['new_en'] ?? '';
$new_tr = $input['new_tr'] ?? '';
$old_en = $input['old_en'] ?? '';
$old_tr = $input['old_tr'] ?? '';

if ($action === 'add') {
    $data[] = ['english' => $new_en, $transKey => $new_tr];
    file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
}

if ($action === 'edit') {
    if (!$old_en || !$old_tr) {
        echo json_encode(['error' => 'At first, choose the word from the list']);
        exit;
    }
    
    foreach ($data as &$item) {
        // Поиск по паре слов (ключу)
        if ($item['english'] === $old_en && $item[$transKey] === $old_tr) {
            // Изменяем текущую запись, не создавая новую
            $item['english'] = $new_en;
            $item[$transKey] = $new_tr;
            break;
        }
    }
    file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
}

if ($action === 'delete') {
    // При удалении используем данные из полей ввода как пару-ключ
    foreach ($data as $key => $item) {
        if ($item['english'] === $new_en && $item[$transKey] === $new_tr) {
            unset($data[$key]);
            break;
        }
    }
    $data = array_values($data); // Переиндексация массива
    file_put_contents($filename, json_encode($data, JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
}
?>