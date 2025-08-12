<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido, use GET"]);
    exit;
}

// Pega CPF da query string
$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';
$cpf = preg_replace('/\D/', '', $cpf);

if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(["error" => "CPF inválido"]);
    exit;
}

// URL e token da API original
$url = "https://idomepuxadas.xyz/api/v1/cpf/09adfd94-ef8a-4783-a976-1f67efdcb9b6/" . $cpf;
$token = "4d65acfcd1da251426d90daa55184843e41e18cb6e331f20a3a1a7ec54ab677e";

// Faz a requisição
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Accept: application/json",
    "Content-Type: application/json",
    "Authorization: Bearer {$token}"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao acessar API externa", "detalhe" => $error]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['data'])) {
    http_response_code($http_code);
    echo $response;
    exit;
}

// Retorna apenas os campos desejados
$resultado = [
    "NOME"       => $data['data']['nome'] ?? '',
    "MAE"        => $data['data']['mae'] ?? '',
    "SEXO"       => $data['data']['sexo'] ?? '',
    "NASCIMENTO" => $data['data']['nascimento'] ?? ''
];

http_response_code(200);
echo json_encode($resultado);
