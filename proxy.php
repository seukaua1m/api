<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => 405, "message" => "Método não permitido"]);
    exit;
}

if (!isset($_POST["cpf"]) || empty($_POST["cpf"])) {
    echo json_encode(["status" => 400, "message" => "CPF é obrigatório"]);
    exit;
}

$cpf = preg_replace("/\D/", "", $_POST["cpf"]);

if (strlen($cpf) !== 11) {
    echo json_encode(["status" => 400, "message" => "CPF inválido"]);
    exit;
}

$api_url = "https://apela.tech?user=ff287045-51cb-4539-bc32-77ac4c3089f1&cpf=" . urlencode($cpf);
$response = file_get_contents($api_url);

if ($response === false) {
    echo json_encode(["status" => 500, "message" => "Erro ao se conectar à API"]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data["status"]) || $data["status"] !== 200) {
    echo json_encode(["status" => 400, "message" => "CPF não encontrado"]);
    exit;
}

$_SESSION['nome'] = $data['nome'];
$_SESSION['cpf'] = $data['cpf'];

echo json_encode($data);
