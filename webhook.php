<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND);

$data = json_decode($input, true);

// Captura a mensagem e o telefone do remetente
$mensagem = $data['text']['message'] ?? '';
$telefone = $data['phone'] ?? '';

if ($mensagem && $telefone) {
    $resposta = "Recebido: " . $mensagem;

    // Prepara o payload
    $payload = [
        'phone' => $telefone,
        'message' => $resposta
    ];

    // URL correta da Z-API (com /send-message)
    $url = "https://api.z-api.io/instances/3E401062FA83E0F253FEBE7C53096139/token/021056C63BB7C732FB534BCD/send-message";

    // Envia o POST para a Z-API
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($payload)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    // Salva a resposta da API para debug
    file_put_contents("log.txt", "Resposta da API: " . $result . PHP_EOL, FILE_APPEND);
}
?>
