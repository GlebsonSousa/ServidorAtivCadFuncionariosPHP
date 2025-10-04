<?php
// ARQUIVO: buscar_funcionario.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET"); // Ideal para buscas

require_once 'conexao.php';

// Pega o termo de busca da URL (ex: buscar_funcionario.php?termo=glebson)
$termo_busca = isset($_GET['termo']) ? trim($_GET['termo']) : '';

if (empty($termo_busca)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nenhum termo de busca fornecido.']);
    exit();
}

// Prepara a query SQL para buscar por nome OU por número de registro
// O operador LIKE com '%' busca por partes de um nome
$sql = "SELECT n_registro, nome_funcionario, data_admissao, cargo, salario FROM Lista_Usuarios WHERE nome_funcionario LIKE ? OR n_registro = ?";
$stmt = mysqli_prepare($conexao, $sql);

// Adiciona os '%' para a busca com LIKE
$termo_like = "%" . $termo_busca . "%";

// "si" -> s=string (para o nome), i=integer (para o registro)
mysqli_stmt_bind_param($stmt, "ss", $termo_like, $termo_busca);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$funcionarios = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $funcionarios[] = $linha;
}

if (count($funcionarios) > 0) {
    http_response_code(200); // OK
    echo json_encode($funcionarios);
} else {
    http_response_code(404); // Not Found
    echo json_encode(['msg' => 'Nenhum funcionário encontrado para o termo informado.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);
?>