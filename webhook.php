<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND);

// Decodifica o JSON recebido
$data = json_decode($input, true);

// Captura a mensagem e o número do remetente
$mensagem = $data['text']['message'] ?? '';
$telefone = $data['phone'] ?? '';

if ($mensagem && $telefone) {
    $resposta = "Recebido: " . $mensagem;

    $payload = [
        "phone" => $telefone,
        "message" => $resposta
    ];

    // ✅ ID da instância e token reais
    $instanceId = "3E401062FA83E0F253FEBE7C53096139";
    $token = "021056C63BB7C732FB534BCD";

    // ✅ URL correta da Z-API
    $url = "https://v2.z-api.io/instances/$instanceId/token/$token/send-text";

    // ✅ Envio com HTTP context
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($payload)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        file_put_contents("log.txt", "Erro ao enviar requisição para API Z-API" . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents("log.txt", "Resposta da API: " . $result . PHP_EOL, FILE_APPEND);
    }
}
?>

