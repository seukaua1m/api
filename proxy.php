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

// Se tiver query string, pega o que vem depois do "?"
$cpf = '';
if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
    $cpf = $_SERVER['QUERY_STRING']; // Pega exatamente como foi enviado
} else {
    // Caso esteja no path
    $request_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $cpf = basename(trim($request_path, "/"));
}

// Sanitiza
$cpf = preg_replace('/\D/', '', $cpf);

// Valida tamanho
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(["error" => "CPF inválido"]);
    exit;
}

// Monta a URL da API original
$url = "https://idomepuxadas.xyz/api/v1/cpf/09adfd94-ef8a-4783-a976-1f67efdcb9b6/" . $cpf;
$token = "4d65acfcd1da251426d90daa55184843e41e18cb6e331f20a3a1a7ec54ab677e";

// Chamada cURL
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

http_response_code($http_code);
echo $response;
