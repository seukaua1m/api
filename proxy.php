<?php
// Permite qualquer origem
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

// Responde requisições de preflight (OPTIONS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Aceita somente GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido, use GET"]);
    exit;
}

// Verifica se o parâmetro cpf existe
if (!isset($_GET["cpf"]) || empty($_GET["cpf"])) {
    http_response_code(400);
    echo json_encode(["error" => "CPF é obrigatório"]);
    exit;
}

// Sanitiza o CPF
$cpf = preg_replace('/\D/', '', $_GET["cpf"]);
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(["error" => "CPF inválido"]);
    exit;
}

// URL da API original
$url = "https://api.dataget.site/api/v1/cpf/" . urlencode($cpf);

// Token fixo (igual ao do código original)
$token = "7e459f1bc8e1267c1ef4dca2091de42b3dad77786b0b98602a83d0da1f106a39";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// Simula um navegador comum para evitar bloqueio
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0 Safari/537.36");

// Envia headers com o token
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: Bearer {$token}"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Trata erro de conexão
if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao acessar a API externa", "detalhe" => $error]);
    exit;
}

http_response_code($http_code);
echo $response;
