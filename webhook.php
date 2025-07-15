<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND); // para debug

$data = json_decode($input, true);
$mensagem = $data['text']['message'] ?? '';
$numero = $data['phone'] ?? '';

if ($mensagem && $numero) {
    $url = "https://api.z-api.io/instances/3E401062FA83E0F253FEBE7C53096139/send-text";

    $body = [
        "phone" => $numero,
        "message" => "Recebido com sucesso: \"$mensagem\""
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Client-Token: 021056C63BB7C732FB534BCD'  // cabeçalho com token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents("log.txt", "Resposta API: " . $response . PHP_EOL, FILE_APPEND);
}
?>
