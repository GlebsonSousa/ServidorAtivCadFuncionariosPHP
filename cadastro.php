<?php
// ARQUIVO: cadastrar_funcionario.php (Versão Corrigida)

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'conexao.php';

// --- 1. RECEBIMENTO E NORMALIZAÇÃO DOS DADOS ---
$n_registro = isset($_POST['n_registro']) ? trim($_POST['n_registro']) : null;
$nome_funcionario = isset($_POST['nome_funcionario']) ? trim($_POST['nome_funcionario']) : '';
$cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : '';
$data_admissao_br = isset($_POST['data_admissao']) ? trim($_POST['data_admissao']) : null;
$qtd_salarios_minimos_str = isset($_POST['qtd_salarios_minimos']) ? trim($_POST['qtd_salarios_minimos_str']) : null;

// Normaliza a entrada para float (troca vírgula por ponto)
$qtd_salarios_float = null;
if ($qtd_salarios_minimos_str) {
    $qtd_salarios_float = (float)str_replace(',', '.', $qtd_salarios_minimos_str);
}

// Normalização da Data (DD/MM/YYYY para YYYY-MM-DD)
$data_admissao_mysql = null;
if ($data_admissao_br) {
    $date_obj = DateTime::createFromFormat('d/m/Y', $data_admissao_br);
    if ($date_obj) {
        $data_admissao_mysql = $date_obj->format('Y-m-d');
    }
}

// --- 2. CÁLCULOS DOS VALORES ---
define('SALARIO_MINIMO', 1412.00);
$salario_bruto = null;
$inss = null;
$salario_liquido = null;

if ($qtd_salarios_float !== null && $qtd_salarios_float > 0) {
    // Calcula o salário bruto
    $salario_bruto = $qtd_salarios_float * SALARIO_MINIMO;

    // Aplica a regra do imposto INSS
    if ($salario_bruto > 1550.00) {
        $inss = $salario_bruto * 0.11; // Alíquota de 11%
    } else {
        $inss = 0.0; // Isenção
    }

    // Calcula o salário líquido
    $salario_liquido = $salario_bruto - $inss;
}

// --- 3. VALIDAÇÃO E INSERÇÃO NO BANCO ---
if (empty($n_registro) || empty($nome_funcionario)) {
    http_response_code(400); // Bad Request
    echo json_encode(['erro' => "Dados incompletos. 'n_registro' e 'nome_funcionario' são obrigatórios."]);
    exit();
}

// Verifica se o registro já existe
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

$sql_insere = "INSERT INTO Lista_Usuarios (n_registro, nome_funcionario, data_admissao, cargo, salario, inss, salario_liquido) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt_insere = mysqli_prepare($conexao, $sql_insere);

// O bind_param foi ajustado para corresponder à nova query: "isssddd"
mysqli_stmt_bind_param($stmt_insere, "isssddd", $n_registro, $nome_funcionario, $data_admissao_mysql, $cargo, $salario_bruto, $inss, $salario_liquido);

if (mysqli_stmt_execute($stmt_insere)) {
    http_response_code(201); // Created
    echo json_encode(['msg' => "Funcionário cadastrado com sucesso!"]);
} else {
    http_response_code(500); // Internal Server Error
    // Adiciona o erro do banco para facilitar a depuração
    echo json_encode(['erro' => "Ocorreu um erro no servidor ao cadastrar.", 'db_error' => mysqli_error($conexao)]);
}

mysqli_stmt_close($stmt_insere);
mysqli_close($conexao);
?>