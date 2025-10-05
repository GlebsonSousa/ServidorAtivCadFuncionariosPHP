<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once 'conexao.php';

$sql = "SELECT n_registro, nome_funcionario, data_admissao, cargo, salario, inss, salario_liquido FROM Lista_Usuarios";
$resultado = mysqli_query($conexao, $sql);

$funcionarios = [];
if ($resultado) {
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $funcionarios[] = $linha;
    }
}

if (count($funcionarios) > 0) {
    http_response_code(200); 
    echo json_encode($funcionarios);
} else {
    http_response_code(404); 
    echo json_encode(['msg' => 'Nenhum funcionário cadastrado no banco de dados.']);
}

mysqli_close($conexao);

?>
