<?php
  //připojení k DB
  require 'db.php';

  //přístup jen pro přihlášeného uživatele
  require 'user_required.php';



  // session pole pro košík (pokud v košíku nic není, definujeme jej jako prázdné pole)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }
if(is_numeric($_GET['id'])) {
    //načteme dané zboží z DB - POZOR: ačkoliv očekáváme, že id zboží bude číslo, musíme počítat s rizikem, že se uživatel ve svém požadavku pokusí o sql injection!
    $stmt = $db->prepare("SELECT * FROM goods WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $goods = $stmt->fetch();
}
if (!$goods){
    die("Unable to find goods!");
}

if(empty($_SESSION['cart'])){
    $_SESSION['cart'][] = ['id' => $goods["id"], 'qvantity'=> 1];
}else{
    $inArray=false;
    for ($i = 0; $i <= @count($_SESSION['cart']); $i++) {
        if(@$_SESSION['cart'][$i]['id']==$goods['id']){
            $inArray = true;
            break;
        }else{
            $inArray = false;
        }
    }
    if($inArray){
        $key = array_search($goods['id'], array_column($_SESSION['cart'], 'id'));
        $_SESSION['cart'][$key] = ['id' => $goods['id'], 'qvantity'=> $_SESSION['cart'][$key]['qvantity']+1];
    }else{
        $_SESSION['cart'][] = ['id' => $goods["id"], 'qvantity'=> 1];
    }
}
header('Location: cart.php');


