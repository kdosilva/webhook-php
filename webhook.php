<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND); // salva o log

$data = json_decode($input, true);

// Verifica se veio uma mensagem da Z-API
if (isset($data['event']) && $data['event'] === 'message') {
    $mensagem = $data['message']['text'] ?? '';
    $numero = $data['message']['from'] ?? '';

    if ($mensagem && $numero) {
        // Dados da sua instância da Z-API
        $instance = '3E401062FA83E0F253FEBE7C53096139';
        $token = '021056C63BB7C732FB534BCD';

        // Prepara a mensagem de resposta
        $resposta = [
            'phone' => $numero,
            'message' => "Você disse: $mensagem"
        ];

        $url = "https://api.z-api.io/instances/$instance/token/$token/send-messages";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resposta));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        file_put_contents("log.txt", "RESPOSTA: $res" . PHP_EOL, FILE_APPEND); // salva resposta da API
    }
}
?>
