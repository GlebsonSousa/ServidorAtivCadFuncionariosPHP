<?php
// ARQUIVO: cadastrar_funcionario.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'conexao.php';

// Pega os dados do formulário
$n_registro = isset($_POST['n_registro']) ? trim($_POST['n_registro']) : null;
$nome_funcionario = isset($_POST['nome_funcionario']) ? trim($_POST['nome_funcionario']) : '';
$data_admissao = isset($_POST['data_admissao']) ? trim($_POST['data_admissao']) : null;
$cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : '';
$salario = isset($_POST['salario']) ? trim($_POST['salario']) : null;

// Validação básica
if (empty($n_registro) || empty($nome_funcionario)) {
    http_response_code(400); // Bad Request
    echo json_encode(['erro' => "Dados incompletos. 'n_registro' e 'nome_funcionario' são obrigatórios."]);
    exit();
}

// Verificar se o n_registro já existe para evitar duplicatas
$sql_verifica = "SELECT n_registro FROM Lista_Usuarios WHERE n_registro = ?";
$stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
mysqli_stmt_bind_param($stmt_verifica, "i", $n_registro); // 'i' for integer
mysqli_stmt_execute($stmt_verifica);
mysqli_stmt_store_result($stmt_verifica);

if (mysqli_stmt_num_rows($stmt_verifica) > 0) {
    http_response_code(409); // Conflict
    echo json_encode(['erro' => "O número de registro '$n_registro' já está cadastrado."]);
    mysqli_stmt_close($stmt_verifica);
    exit();
}
mysqli_stmt_close($stmt_verifica);


// Inserir o novo funcionário
// OBS: Os campos 'inss' e 'salario_liquido' não estão aqui, pois geralmente são calculados depois.
$sql_insere = "INSERT INTO Lista_Usuarios (n_registro, nome_funcionario, data_admissao, cargo, salario) VALUES (?, ?, ?, ?, ?)";
$stmt_insere = mysqli_prepare($conexao, $sql_insere);

// "isssd" -> i=integer, s=string, s=string, s=string, d=double/float
mysqli_stmt_bind_param($stmt_insere, "isssd", $n_registro, $nome_funcionario, $data_admissao, $cargo, $salario);

if (mysqli_stmt_execute($stmt_insere)) {
    http_response_code(201); // Created
    echo json_encode(['msg' => "Funcionário cadastrado com sucesso!"]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['erro' => "Ocorreu um erro no servidor ao cadastrar."]);
}

mysqli_stmt_close($stmt_insere);
mysqli_close($conexao);
?>