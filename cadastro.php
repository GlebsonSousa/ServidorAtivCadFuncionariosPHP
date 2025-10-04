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
$cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : '';

// --- ADICIONADO: Bloco de Normalização do Salário ---
$salario_enviado = isset($_POST['salario']) ? trim($_POST['salario']) : null;
$salario_para_banco = null; // Variável para guardar o valor limpo

if ($salario_enviado !== null) {
    // 1. Remove o ponto de milhar (ex: 1.500,50 -> 1500,50)
    $salario_limpo = str_replace('.', '', $salario_enviado);
    // 2. Troca a vírgula do decimal por um ponto (ex: 1500,50 -> 1500.50)
    $salario_limpo = str_replace(',', '.', $salario_limpo);
    
    // 3. Converte para float, garantindo que seja um número
    $salario_para_banco = (float)$salario_limpo;
}
// --- FIM DO BLOCO DE NORMALIZAÇÃO ---


// Bloco de Cálculo
$inss = null;
$salario_liquido = null;

// Verifica se o salário foi convertido corretamente para fazer o cálculo
if ($salario_para_banco !== null && is_numeric($salario_para_banco)) {
    $taxa_inss = 0.10; // Taxa de 10%
    
    $inss = $salario_para_banco * $taxa_inss;
    $salario_liquido = $salario_para_banco - $inss;
}


$data_admissao_br = isset($_POST['data_admissao']) ? trim($_POST['data_admissao']) : null;
$data_admissao_mysql = null;

if ($data_admissao_br) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $data_admissao_br);
    if ($date_obj) {
        $data_admissao_mysql = $date_obj->format('Y-m-d');
    }
}

// Validação básica
if (empty($n_registro) || empty($nome_funcionario)) {
    http_response_code(400); // Bad Request
    echo json_encode(['erro' => "Dados incompletos. 'n_registro' e 'nome_funcionario' são obrigatórios."]);
    exit();
}

// Verificar se o n_registro já existe
$sql_verifica = "SELECT n_registro FROM Lista_Usuarios WHERE n_registro = ?";
$stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
mysqli_stmt_bind_param($stmt_verifica, "i", $n_registro);
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
$sql_insere = "INSERT INTO Lista_Usuarios (n_registro, nome_funcionario, data_admissao, cargo, salario, inss, salario_liquido) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insere = mysqli_prepare($conexao, $sql_insere);

// Usa a variável normalizada '$salario_para_banco' para salvar no banco
mysqli_stmt_bind_param($stmt_insere, "isssddd", $n_registro, $nome_funcionario, $data_admissao_mysql, $cargo, $salario_para_banco, $inss, $salario_liquido);

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