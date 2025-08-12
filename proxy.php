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

// Captura CPF da query string (?12345678901) ou path
$cpf = '';
if (!empty($_SERVER['QUERY_STRING'])) {
    $cpf = $_SERVER['QUERY_STRING'];
} else {
    $request_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $cpf = basename(trim($request_path, "/"));
}

$cpf = preg_replace('/\D/', '', $cpf);

if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(["error" => "CPF inválido"]);
    exit;
}

// Monta URL e token
$url = "https://idomepuxadas.xyz/api/v1/cpf/09adfd94-ef8a-4783-a976-1f67efdcb9b6/" . $cpf;
$token = "4d65acfcd1da251426d90daa55184843e41e18cb6e331f20a3a1a7ec54ab677e";

// Faz requisição à API original
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

// Converte JSON
$data = json_decode($response, true);

// Se a API não retornou os dados esperados
if (!isset($data['data'])) {
    http_response_code($http_code);
    echo json_encode($data);
    exit;
}

// Monta resposta no formato que o frontend espera
$resultado = [
    "NOME"      => $data['data']['nome'] ?? '',
    "NOME_MAE"  => $data['data']['mae'] ?? '',
    "SEXO"      => $data['data']['sexo'] ?? '',
    "NASC"      => $data['data']['nascimento'] ?? ''
];

http_response_code(200);
echo json_encode($resultado);
