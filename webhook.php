<?php
// webhook.php - recebe mensagens da Z-API e responde com ChatGPT

$openai_api_key = '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';
$zapi_instance = 'https://api.z-api.io/instances/3E401062FA83E0F253FEBE7C53096139/token/021056C63BB7C732FB534BCD/send-text';
$zapi_token = '021056C63BB7C732FB534BCD';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$message = $data['message'] ?? '';
$number = $data['phone'] ?? '';

if (!$message || !$number) exit;

$resposta = enviarParaChatGPT($message, $openai_api_key);
enviarMensagemWhatsApp($number, $resposta, $zapi_instance, $zapi_token);

function enviarParaChatGPT($msg, $api_key) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'Você é um assistente financeiro.'],
            ['role' => 'user', 'content' => $msg]
        ]
    ];
    $headers = [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    $r = json_decode($res, true);
    return $r['choices'][0]['message']['content'] ?? 'Erro na resposta da IA';
}

function enviarMensagemWhatsApp($num, $msg, $inst, $token) {
    $url = "https://api.z-api.io/instances/$inst/token/$token/send-text";
    $data = ['phone' => $num, 'message' => $msg];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];
    file_get_contents($url, false, stream_context_create($options));
}


