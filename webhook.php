<?php
$input = file_get_contents("php://input");

// Salva o conteúdo bruto para depuração (opcional)
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND);

// Converte JSON em array associativo
$data = json_decode($input, true);

// Extrai a mensagem do campo correto
$mensagem = $data['text']['message'] ?? '';
$numero = $data['connectedPhone'] ?? '';

if ($mensagem && $numero) {
    // Monta os dados para enviar a resposta via Z-API
    $url = "https://api.z-api.io/instances/3E401062FA83E0F253FEBE7C53096139/token/021056C63BB7C732FB534BCD/send-text";

    $body = [
        "phone" => $numero,
        "message" => "Recebido com sucesso: \"$mensagem\""
    ];

    // Envia POST com cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    curl_close($ch);

    // Salva resposta da API (opcional)
    file_put_contents("log.txt", "Resposta da API: " . $response . PHP_EOL, FILE_APPEND);
}
?>
