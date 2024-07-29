<?php
    $email = $_POST['email'];
    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];
    $telefone = $_POST['telefone'];

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Endereço de e-mail fixo como remetente
    $from = $email;
    $to = "alanreis@unli.com.br";
    $subject = mb_encode_mimeheader($assunto, "UTF-8");
    $message = $mensagem . "\n\n" . "Telefone de contato: " . $telefone;

    // Define o charset como UTF-8 nos cabeçalhos
    $headers = "From: " . $from . "\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion() . "\r\n" .
               "Content-Type: text/plain; charset=utf-8\r\n" .
               "Content-Transfer-Encoding: 8bit";

    mail($to, $subject, $message, $headers);

    echo "O e-mail foi enviado com sucesso!";
    
    header("Location: https://unli.com.br");
    die();
?>

<!-- $email = $_POST['email'];
    $assunto = $_POST['assunto'];
    $mensagem = $_POST['mensagem'];

    ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );

    $from = $email;
    $to = "alanreis@unli.com.br";
    $subject = $assunto;
    $message = $mensagem;
    $headers = "De:" . $from;

    mail($to,$subject,$message, $headers);

    echo "O e-mail foi enviado com sucesso!";
    header("Location: https://unli.com.br");
    die(); -->