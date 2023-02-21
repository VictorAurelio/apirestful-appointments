<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>API Appointments</title>
  <!--HTML Simples apenas para apresentação copiado do site w3school-->
  <style>
    #header {
        background-color:black;
        color:white;
        text-align:center;
        padding:5px;
    }
    #nav {
        line-height:30px;
        background-color:#eeeeee;
        height:300px;
        width:100px;
        float:left;
        padding:5px;	      
    }
    #section {
        width:350px;
        float:left;
        padding:10px;	 	 
    }
    #footer {
        background-color:black;
        color:white;
        clear:both;
        text-align:center;
       padding:5px;	 	 
    }
    </style>
</head>
<body>
  <div id="header">
    <h1>APIRestful Appointments</h1>
    </div>
    
    <div id="nav">
    <a href="https://github.com/VictorAurelio">GitHub</a><br>
    <a href="https://www.linkedin.com/in/victor-aurelio-a8700b17a/">LinkedIn</a><br>
    <a href="https://www.instagram.com/_victoraurelio/">Instagram</a><br>
    <a href="https://wa.me/5562983314425">WhatsApp</a>
    </div>
    
    <div id="section">
    <h2>Sobre o projeto</h2>
    <p>API desenvolvida com o framework Laravel, com o objetivo de permitir um CRUD de locais e agendamentos.
      Utilizando métodos de autenticação e verificações de segurança em todos os procedimentos.
    </p>
    <p>Para utilizar, copie o projeto para a pasta que desejar e abra o editor de sua preferência;<br>Renomeie o
      arquivo .env.example para .env na raíz do diretório;<br>Rode o comando "composer install" e aguarde;<br>
      Rode o comando "php artisan migrate" para criar o banco de dados e rodar as migrations;<br>
      Após a criação do banco de dados, basta rodar o comando "php artisan serve" para iniciar o servidor e começar os testes.<br>
      <b>Atenciosamente, Víctor.</b>
    </p>
    </div>
    
    <div id="footer">
    APIRest Appointments &copy; 20-02-2023
    </div>
</body>
</html>