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

$resultado = []; // aqui vamos acumulando as respostas

// -------------------------------------------------
// Consulta CPF (se informado)
// -------------------------------------------------
if (!empty($_GET['cpf'])) {
    $cpf = preg_replace('/\D/', '', $_GET['cpf']);

    if (strlen($cpf) !== 11) {
        $resultado['cpf'] = ["error" => "CPF inválido"];
    } else {
        $url = "https://idomepuxadas.xyz/api/v1/cpf/09adfd94-ef8a-4783-a976-1f67efdcb9b6/" . $cpf;
        $token = "4d65acfcd1da251426d90daa55184843e41e18cb6e331f20a3a1a7ec54ab677e";

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
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $resultado['cpf'] = ["error" => "Erro ao acessar API externa", "detalhe" => $error];
        } else {
            $data = json_decode($response, true);
            if (!isset($data['data'])) {
                $resultado['cpf'] = ["error" => "Resposta inesperada da API", "detalhe" => $data];
            } else {
                $resultado['cpf'] = [
                    "NOME"      => $data['data']['nome'] ?? '',
                    "NOME_MAE"  => $data['data']['mae'] ?? '',
                    "SEXO"      => $data['data']['sexo'] ?? '',
                    "NASC"      => $data['data']['nascimento'] ?? ''
                ];
            }
        }
    }
}

// -------------------------------------------------
// Consulta CEP (se informado)
// -------------------------------------------------
if (!empty($_GET['cep'])) {
    $cep = preg_replace('/\D/', '', $_GET['cep']);

    if (strlen($cep) !== 8) {
        $resultado['cep'] = ["error" => "CEP inválido"];
    } else {
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $resultado['cep'] = ["error" => "Erro ao consultar ViaCEP", "detalhe" => $error];
        } else {
            $data = json_decode($response, true);

            if (isset($data['erro'])) {
                $resultado['cep'] = ["error" => "CEP não encontrado"];
            } else {
                $resultado['cep'] = [
                    "CEP"        => $data['cep'] ?? '',
                    "LOGRADOURO" => $data['logradouro'] ?? '',
                    "BAIRRO"     => $data['bairro'] ?? '',
                    "CIDADE"     => $data['localidade'] ?? '',
                    "UF"         => $data['uf'] ?? ''
                ];
            }
        }
    }
}

// -------------------------------------------------
// Se não mandou nada
// -------------------------------------------------
if (empty($resultado)) {
    http_response_code(400);
    echo json_encode(["error" => "Informe pelo menos cpf ou cep"]);
    exit;
}

// Retorno final
http_response_code(200);
echo json_encode($resultado);
