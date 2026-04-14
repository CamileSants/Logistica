<?php
// Configurações iniciais
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Removido GET
header('Access-Control-Allow-Headers: Content-Type');

// Trata requisições OPTIONS do CORS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Verifica se a requisição é POST, já que apenas a inserção é permitida
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido."]);
    exit;
}

// ----------------------------------------------------
// 1. CONFIGURAÇÃO E LEITURA DE DADOS
// ----------------------------------------------------
$host = 'localhost';
$db   = 'feira_tecnica'; 
$user = 'root'; 
$pass = '';  // ⬅️ SUA SENHA AQUI
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
];

// LÊ O JSON ENVIADO PELO JAVASCRIPT (agora esperamos os dados do DB)
$json_data = file_get_contents("php://input");
$db_data = json_decode($json_data, true);

if (empty($db_data) || !isset($db_data['numero_nota'])) {
    http_response_code(400); 
    echo json_encode(["success" => false, "message" => "JSON inválido ou dados da Nota Fiscal insuficientes."]);
    exit;
}

// Mapeamento dos dados do JSON para a inserção no DB
// Usamos os nomes de campos definidos na função storeInvoiceInDB do script.js
$dados_nf_db = [
    'numero_nota'         => $db_data['numero_nota'] ?? 'N/A',
    'chave_acesso'        => $db_data['chave_acesso'] ?? 'N/A',
    'status'              => $db_data['status'] ?? 'REGISTRADA',
    'descricao_servico'   => $db_data['descricao_servico'] ?? 'N/A',
    
    
    // Campos de Cliente
    'cpf_cnpj'            => $db_data['cpf_cnpj'] ?? 'N/A', 
    'nome_cliente'        => $db_data['nome_cliente'] ?? 'Cliente Desconhecido',
    'email_cliente'       => $db_data['email_cliente'] ?? 'N/A',
    'telefone'            => $db_data['telefone'] ?? 'N/A', 
    
    // Campos da Fatura/Envio
    'valor_total'         => floatval($db_data['valor_total'] ?? 0.00), // Valor deve ser float/decimal no DB
    'metodo_transporte'   => $db_data['metodo_transporte'] ?? 'Digital', 
    'data_emissao'        => date('Y-m-d H:i:s'), 
    'data_envio'          => $db_data['data_envio'] ?? date('Y-m-d'), // Usa a data do formulário, se existir
];


// ----------------------------------------------------
// 2. INSERÇÃO NO BANCO DE DADOS (PDO)
// ----------------------------------------------------
$last_id = null;

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 💡 ATENÇÃO: Verifique se sua tabela 'nota_fiscal' contém exatamente estas colunas:
    $sql = "INSERT INTO nota_fiscal (
                numero_nota, chave_acesso, status, descricao_servico,
                cpf_cnpj, nome_cliente, email_cliente, telefone, 
                valor_total, metodo_transporte, data_emissao, data_envio
            ) 
            VALUES (
                :numero_nota, :chave_acesso, :status, :descricao_servico,
                :cpf_cnpj, :nome_cliente, :email_cliente, :telefone, 
                :valor_total, :metodo_transporte, :data_emissao, :data_envio
            )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($dados_nf_db);

    $last_id = $pdo->lastInsertId();

} catch (\PDOException $e) {
    // Em caso de erro no DB
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erro crítico: Falha ao salvar no banco de dados. Detalhe: " . $e->getMessage()]);
    exit;
}


// ----------------------------------------------------
// 3. RESPOSTA FINAL AO FRONT-END
// ----------------------------------------------------

http_response_code(200);
echo json_encode([
    "success" => true,
    "message" => "Nota Fiscal ID #{$last_id} registrada com sucesso!",
    "id" => $last_id
]);

?>