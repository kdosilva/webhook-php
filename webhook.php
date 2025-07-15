<?php
// Exibe erros (útil para debug, desabilite em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexão
require_once 'conexao.php';

// Recebe o JSON do webhook
$input = json_decode(file_get_contents('php://input'), true);
file_put_contents("webhook_log.txt", json_encode($input, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

// Sanitiza
$telefone = $input['phone'] ?? '';
$mensagem = strtolower(trim($input['text']['message'] ?? ''));
$resposta = "Olá! Envie sua despesa ou receita. Ex: 'Gastei R$20 em lanche e R$15 em ônibus'.";
$data = date('Y-m-d H:i:s');

if ($telefone && $mensagem) {
    // Busca cliente
    $stmt = $conexao->prepare("SELECT id, nome FROM clientes WHERE telefone = ?");
    $stmt->bind_param("s", $telefone);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $cliente = $resultado->fetch_assoc();
        $cliente_id = $cliente['id'];

        // Prompt para IA
        $prompt = "A mensagem abaixo pode conter transações financeiras ou informações de renda fixa.
Extraia os dados como um array JSON com os seguintes formatos:

Se for transações:
[
  { \"valor\": 50, \"categoria\": \"Alimentação\", \"descricao\": \"lanche\", \"tipo\": \"saida\" },
  { \"valor\": 30, \"categoria\": \"Transporte\", \"descricao\": \"ônibus\", \"tipo\": \"saida\" }
]

Se for uma renda fixa:
{ \"tipo\": \"renda_fixa\", \"valor\": 1000, \"dia\": 2, \"descricao\": \"Salário mensal\" }

Mensagem: \"$mensagem\"";

        // Chave OpenAI via variável de ambiente
        $openai_key = getenv("OPENAI_API_KEY");

        $payload = [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                ["role" => "system", "content" => "Você é um assistente financeiro. Extraia as informações corretamente em JSON."],
                ["role" => "user", "content" => $prompt]
            ]
        ];

        // Chamada à OpenAI
        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $openai_key",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resposta_api = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($resposta_api, true);
        $conteudo = json_decode($json['choices'][0]['message']['content'] ?? '', true);

        if (is_array($conteudo)) {
            foreach ($conteudo as $t) {
                if (!isset($t['valor'], $t['categoria'], $t['descricao'], $t['tipo'])) continue;

                $valor = floatval($t['valor']);
                $categoria = $t['categoria'];
                $descricao = $t['descricao'];
                $tipo = $t['tipo'];

                $stmt = $conexao->prepare("INSERT INTO transacoes (cliente_id, tipo, categoria, descricao, valor, data) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssds", $cliente_id, $tipo, $categoria, $descricao, $valor, $data);
                $stmt->execute();
            }
            $resposta = "Transações registradas com sucesso!";
        } elseif (isset($conteudo['tipo']) && $conteudo['tipo'] === 'renda_fixa') {
            $valor = floatval($conteudo['valor']);
            $dia = intval($conteudo['dia']);
            $descricao = $conteudo['descricao'];

            $stmt = $conexao->prepare("INSERT INTO rendas_fixas (cliente_id, valor, dia_pagamento, descricao) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idis", $cliente_id, $valor, $dia, $descricao);
            $stmt->execute();

            $resposta = "Renda fixa registrada com sucesso: R$ {$valor} todo dia {$dia}.";
        } else {
            $resposta = "Não consegui entender sua mensagem. Tente novamente com: 'Gastei R$20 com lanche'.";
        }
    } else {
        $resposta = "Olá! Seu número não está cadastrado. Acesse Konektoos para se registrar.";
    }

    // Envia resposta pela Z-API
    $instance = getenv("3E401062FA83E0F253FEBE7C53096139");
    $clientToken = getenv("021056C63BB7C732FB534BCD");

    $url = "https://v2.z-api.io/instances/$instance/send-text";
    $send_payload = json_encode([
        "chatId" => $telefone . "@s.whatsapp.net",
        "message" => $resposta
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Client-Token: $clientToken"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $send_payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $envio = curl_exec($ch);

    if ($envio === false) {
        $envio = 'Erro cURL: ' . curl_error($ch);
    }

    curl_close($ch);
    file_put_contents("resposta_envio.txt", $envio ?: 'Erro ao enviar');
}
?>

