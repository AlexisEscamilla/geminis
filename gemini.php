<?php
// Configura tu clave de API de Google Cloud Platform
$apiKey = 'AIzaSyBykGN4rgcY3cpRThCkt-8mSLoyYlmznk0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $consulta = isset($_POST['consulta']) ? $_POST['consulta'] : '';
    $imageFile = isset($_FILES['imagen']['tmp_name']) ? $_FILES['imagen']['tmp_name'] : null;

    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

    $data = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $consulta
                    ]
                ]
            ]
        ]
    ];

    if ($imageFile) {
        $imageData = base64_encode(file_get_contents($imageFile));
        $data['contents'][0]['parts'][] = [
            'inline_data' => [
                'mime_type' => 'image/jpeg',
                'data' => $imageData
            ]
        ];
    }

    $dataJson = json_encode($data);

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $dataJson,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
        ],
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
        $result = [
            'error' => 'Error en la solicitud cURL: ' . $err
        ];
    } else {
        $responseData = json_decode($response, true);

        // Agregar registro de depuración para la respuesta
        file_put_contents('debug.log', print_r($responseData, true), FILE_APPEND);

        if ($httpCode >= 200 && $httpCode < 300) {
            $generatedText = isset($responseData['candidates'][0]['content']['parts'][0]['text']) ? $responseData['candidates'][0]['content']['parts'][0]['text'] : 'Respuesta no válida';
            $result = [
                'result' => $generatedText
            ];
        } else {
            $result = [
                'error' => 'Error en la respuesta de la API: ' . json_encode($responseData)
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
?>

