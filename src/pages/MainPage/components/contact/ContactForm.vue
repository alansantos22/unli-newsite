<template>
    <div class="containerContact allCenter">
        <div class="box">
            <h2>Entre em contato com a gente!</h2>
            <form action="https://unli.com.br/PHPMail/phpmail.php" method="post">
                <h3>Envie um e-mail agora</h3>
                <div class="inputBox">
                    <label for="email">Seu e-mail</label>
                    <input type="email" name="email" id="email" placeholder="Digite seu e-mail aqui">
                </div>
                <div class="inputBox">
                    <label for="assunto">Seu telefone de contato</label>
                    <input type="text" name="telefone" id="telefone" placeholder="Digite o seu contato aqui">
                </div>
                <div class="inputBox">
                    <label for="assunto">Seu assunto</label>
                    <input type="text" name="assunto" id="assunto" placeholder="Digite o assunto aqui">
                </div>
                <div class="inputBox">
                    <label for="mensagem">Descreva o que precisa</label>
                    <textarea name="mensagem" id="mensagem" cols="30" rows="10" placeholder="Descreva o que precisa aqui..."></textarea>
                </div>
                <input type="submit" value="Enviar" class="button3">
            </form>
        </div>
        <div class="border"></div>
    </div>
</template>

<script src="./Component-Contact.js"></script>

<style lang="scss" src="./Component-Contact.scss"></style>