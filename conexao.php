<?php
// ARQUIVO: conexao.php (Versão para Render)

// As variáveis de ambiente serão lidas do painel da Render
$host = getenv('HOST');
$db_name = getenv('Database_Name');
$username = getenv('User');
$password = getenv('Password');
$port = getenv('Port');

// Tenta criar a conexão
$conexao = mysqli_connect($host, $username, $password, $db_name, (int)$port);

// Define o charset para utf8 para evitar problemas com acentos
if ($conexao) {
    mysqli_set_charset($conexao, "utf8");
}

// Verifica se a conexão falhou
if (mysqli_connect_error()) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha na conexão com o banco de dados: ' . mysqli_connect_error()]);
    die();
}
?>