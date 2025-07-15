<?php
// Lê o conteúdo recebido
$input = file_get_contents("php://input");

// Para debug: envia a entrada para o console do Railway
error_log("Recebido: " . $input);

// Decodifica o JSON recebido
$data = json_decode($input, true);

// Extrai mensagem e número
$mensagem = $data['message']['text'] ?? '';
$numero = $data['message']['from'] ?? '';

// Se houver mensagem e número, envia resposta
if ($mensagem && $numero) {
    // Monta resposta
    $resposta = "Recebemos sua mensagem: \"$mensagem\" — em breve responderemos!";

    // URL da sua instância Z-API (com ID e token)
    $url = 'https://api.z-api.io/instances/3E401062FA83E0F253FEBE7C53096139/token/021056C63BB7C732FB534BCD/send-text';

    // Monta os dados do POST
    $postData = [
        'phone' => $numero,
        'message' => $resposta
    ];

    // Envia via cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $result = curl_exec($ch);
    curl_close($ch);

    // Log da resposta da API Z-API
    error_log("Resposta da API: " . $result);
}
?>
