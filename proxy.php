<?php
// Permite CORS para qualquer origem
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Verifica se é POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido"]);
    exit;
}

// Lê o corpo da requisição
$input = json_decode(file_get_contents("php://input"), true);

// Validação básica
if (!isset($input["cpf"]) || empty($input["cpf"])) {
    http_response_code(400);
    echo json_encode(["error" => "CPF é obrigatório"]);
    exit;
}

$cpf = preg_replace('/\D/', '', $input["cpf"]);
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(["error" => "CPF inválido"]);
    exit;
}

// Prepara payload
$payload = json_encode(["cpf" => $cpf]);

// Chamada via cURL para API externa
$ch = curl_init("https://comunica-virtual.com/api/fetch-user-data");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Content-Length: " . strlen($payload)
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Se falhou
if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao acessar a API externa", "detalhe" => $error]);
    exit;
}

// Encaminha a resposta da API original
http_response_code($http_code);
echo $response;
