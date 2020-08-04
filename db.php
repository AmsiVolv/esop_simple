<?php
  //pripojeni do db na serveru eso.vse.cz
  //TODO v následujícím řádku uveďte vlastní jméno a heslo k DB
$db = new PDO('mysql:host=127.0.0.1;dbname=volv02;charset=utf8', 'volv02', 'eo3pian9ieyahLi7va');

  //vyhazuje vyjimky v pripade neplatneho SQL vyrazu
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
