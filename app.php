<?php
$authFilePath = __DIR__ . '/a.json';
$notesFilePath = __DIR__ . '/words.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get list
    $words = file_exists($notesFilePath) ? json_decode(file_get_contents($notesFilePath)) : [];
    header('Content-Type: application/json');
    echo json_encode(['words' => $words]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = isset($data['action']) ? $data['action'] : null;
    $p = isset($data['p']) ? $data['p'] : null;

    // Read code from json
    $authData = file_exists($authFilePath) ? json_decode(file_get_contents($authFilePath)) : [];
    $expectedPassword = $authData->p ?? '';

    if ($p !== $expectedPassword) {
        echo "Authentication failed.";
        exit;
    }

    switch ($action) {
        case 'add_word':
            $words = file_exists($notesFilePath) ? json_decode(file_get_contents($notesFilePath)) : [];
            array_push($words,
                ['EnglishWord' => $data['EnglishWord'], 
                'SpanishWord' => $data['SpanishWord'], 
                'RussianWord' => $data['RussianWord']]);
            file_put_contents($notesFilePath, json_encode($words)
            );
            echo "This added successfully!";
            break;
        case 'delete_note':
            $words = file_exists($notesFilePath) ? json_decode(file_get_contents($notesFilePath)) : [];
            foreach ($words as $key => $word)
                if ($word->EnglishWord == $data['EnglishWord']) 
                    unset($words[$key]); // delete array element
            file_put_contents($notesFilePath, json_encode(array_values($words))); // new indexes for array
            echo "This deleted successfully!";
            break;
    }
} else {
    http_response_code(405); // method is not supported
    echo "Method not allowed";
}
?>