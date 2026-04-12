<?php
    // CORS headers para permitir requests do frontend SPA
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: https://unli.com.br');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Responder preflight OPTIONS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Apenas POST permitido
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
        exit;
    }

    // Validação dos campos obrigatórios
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $assunto = isset($_POST['assunto']) ? trim($_POST['assunto']) : '';
    $mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';

    if (empty($email) || empty($assunto) || empty($mensagem)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Campos obrigatórios: email, assunto, mensagem']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'E-mail inválido']);
        exit;
    }

    // Sanitização
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $assunto = htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8');
    $mensagem = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    $telefone = htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8');

    // Destinatário fixo
    $to = "contato@unli.com.br";
    $subject = mb_encode_mimeheader($assunto, "UTF-8");
    $message = $mensagem . "\n\n" . "Telefone de contato: " . $telefone . "\n" . "E-mail: " . $email;

    // Headers do e-mail - usar endereço fixo no From para evitar rejeição por SPF
    $headers = "From: contato@unli.com.br\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion() . "\r\n" .
               "Content-Type: text/plain; charset=utf-8\r\n" .
               "Content-Transfer-Encoding: 8bit";

    $enviado = mail($to, $subject, $message, $headers);

    if ($enviado) {
        echo json_encode(['success' => true, 'message' => 'E-mail enviado com sucesso']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Falha ao enviar e-mail. Tente novamente.']);
    }
?>