<?php
// Configurações iniciais
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
header('Access-Control-Allow-Headers: Content-Type');

// Trata requisições OPTIONS do CORS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Verifica se é POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido. Utilize POST."]);
    exit;
}

// ----------------------------------------------------
// 1. CONFIGURAÇÃO DO BANCO DE DADOS (DB)
// ----------------------------------------------------
$host = 'localhost';
$db   = 'feira_tecnica'; // Sua base de dados
$user = 'root'; 
$pass = '';  // ⬅️ SUA SENHA AQUI
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
];

// ----------------------------------------------------
// 2. COLETA E VALIDAÇÃO DOS DADOS DO FRONTEND (JSON)
// ----------------------------------------------------

$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "JSON inválido recebido."]);
    exit;
}

// Mapeamento dos dados do JSON para as colunas do DB
// 💡 As chaves aqui DEVEM ser idênticas às chaves enviadas no objeto JSON do JS
$dados_nf_db = [
    'cpf_cliente'           => $data['cpf_cliente'] ?? 'N/A', 
    'nome_cliente'          => $data['nome_cliente'] ?? 'Cliente Desconhecido',
    'email_cliente'         => $data['email_cliente'] ?? null,    
    'numero_telefone'       => $data['numero_telefone'] ?? 'N/A', 
    'valor_total_produto'   => floatval($data['valor_total_produto'] ?? 0.00), 
    'meio_transporte'       => $data['meio_transporte'] ?? 'Digital', 
    'data_envio'            => $data['data_envio'] ?? date('Y-m-d'), 
    'data_emissao'          => date('Y-m-d H:i:s'), 
    // Outros campos importantes:
    'chave_acesso'          => $data['chave_acesso'] ?? 'N/A',
];

// Validação crucial de E-mail
if (!isset($dados_nf_db['email_cliente']) || !filter_var($dados_nf_db['email_cliente'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "E-mail do cliente inválido ou ausente."]);
    exit;
}

// ----------------------------------------------------
// 3. INSERÇÃO NO BANCO DE DADOS
// ----------------------------------------------------
try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Lista de colunas para o INSERT
    $sql_columns = 'cpf_cliente, nome_cliente, email_cliente, numero_telefone, valor_total_produto, meio_transporte, data_emissao, data_envio, chave_acesso';
    
    // Lista de placeholders para os VALUES
    $sql_placeholders = ':cpf_cliente, :nome_cliente, :email_cliente, :numero_telefone, :valor_total_produto, :meio_transporte, :data_emissao, :data_envio, :chave_acesso';

    $sql = "INSERT INTO nota_fiscal ($sql_columns) VALUES ($sql_placeholders)";
    
    $stmt = $pdo->prepare($sql);
    
    // Prepara os parâmetros para a execução (remove chaves que não estão no SQL se for o caso, mas aqui incluímos todas)
    $params = $dados_nf_db;
    
    $stmt->execute($params);

    $last_id = $pdo->lastInsertId();

    // ----------------------------------------------------
    // 4. RESPOSTA FINAL AO FRONT-END
    // ----------------------------------------------------
    
    http_response_code(200);
    echo json_encode([
        "success" => true, 
        "message" => "✅ Nota Fiscal ID #$last_id registrada com sucesso no banco de dados.",
        "id" => $last_id
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "❌ Erro no banco de dados: Falha ao inserir dados. Detalhe: " . $e->getMessage()]);
    exit;
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "❌ Erro interno: " . $e->getMessage()]);
    exit;
}

?>