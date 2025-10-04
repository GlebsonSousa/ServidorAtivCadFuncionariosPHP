<?php
// ARQUIVO: teste.php

// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Define o código de resposta como 200 (OK)
http_response_code(200);

// Cria a mensagem de resposta
$resposta = [
    "status" => 200,
    "mensagem" => "A API está funcionando corretamente. Aguardando cadastro ou busca.",
    "timestamp" => date('Y-m-d H:i:s')
];

// Envia a resposta em formato JSON
echo json_encode($resposta);
?>