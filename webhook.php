<?php
$input = file_get_contents("php://input");
file_put_contents("log.txt", $input . PHP_EOL, FILE_APPEND);

// Você pode adicionar resposta automática aqui, ex:
$data = json_decode($input, true);
$mensagem = $data['message']['text'] ?? '';

if ($mensagem) {
    // Exemplo de resposta (depende da API que você usa)
    // Aqui você deveria fazer um POST de volta para responder
}
?>
