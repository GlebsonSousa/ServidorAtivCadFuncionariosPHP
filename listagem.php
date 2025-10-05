<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'conexao.php';

$termo_busca = isset($_GET['termo']) ? trim($_GET['termo']) : '';

if (empty($termo_busca)) {
    http_response_code(400); // Bad Request
    echo json_encode(['erro' => 'Nenhum termo de busca fornecido.']);
    exit();
}

$sql = "SELECT n_registro, nome_funcionario, data_admissao, cargo, salario, inss, salario_liquido FROM Lista_Usuarios WHERE nome_funcionario LIKE ? OR n_registro = ?";
$stmt = mysqli_prepare($conexao, $sql);

$termo_like = $termo_busca . "%";

mysqli_stmt_bind_param($stmt, "si", $termo_like, $termo_busca);

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

$funcionarios = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $funcionarios[] = $linha;
}

if (count($funcionarios) > 0) {
    http_response_code(200); 
    echo json_encode($funcionarios);
} else {
    http_response_code(404); 
    echo json_encode(['msg' => 'Nenhum funcionário encontrado para o termo informado.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

?>