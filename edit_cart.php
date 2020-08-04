<?php
//připojení k DB
require 'db.php';

//přístup jen pro přihlášeného uživatele
require 'user_required.php';

if(!empty($_GET)){
    $error = [];
    #start kontrola id

    #1) Kontrolujeme pokud id neni prazdne
    if(!empty($_GET['id'])){
        #2) Kontrolujeme pokud id je cislo
        if(is_numeric($_GET['id'])){
            #3) Kontrolujeme pokud id je v kosiku
            if(array_search($_GET['id'], array_column($_SESSION['cart'], 'id')) !== false){
                $stmt = $db->prepare("SELECT * FROM goods WHERE id=:id LIMIT 1;");
                $stmt->execute([
                    ':id'=>$_GET['id']
                ]);
                #4)Kontrolujeme existence zbozi (asi zbytecne)
                if ($stmt->rowCount()>0){
                    $id = $_GET['id'];
                }else{
                    $errors['id']='This product id does not exist';
                };
            }else{
                $errors['id']='This product is not in your cart';
            }
        }else{
            $errors['id']='Product id must be a number';
        }
    }else{
        $errors['id']='Empty product id';
    }
    #konec kontrola id

    if(!empty($_GET['quantity'])){
        if(is_numeric($qv = $_GET['quantity'])){
            $qv = $_GET['quantity'];
        }else{
            $errors['quantity']='Not a number';
        }
    }else{
        $errors['quantity']='Empty parametr quantity';
    }

    if(trim($_GET['action'])=='Remove'||trim($_GET['action'])=='Add'){
        if($_GET['action']=='Add'){
            $add = true;
        }
        if($_GET['action']=='Remove') {
            $add=false;
        }
    }else{
        $errors['action']='Wrong action type';
    }

    if(@$add&&empty($errors)){
        #pridat
        $key = array_search($id, array_column($_SESSION['cart'], 'id'));
        $_SESSION['cart'][$key] = ['id' => $id, 'qvantity'=> $_SESSION['cart'][$key]['qvantity']+$qv];
        header('Location: cart.php');
    }elseif(!@$add&&empty($errors)){
        #odebrat
        $key = array_search($id, array_column($_SESSION['cart'], 'id'));
        if($_SESSION['cart'][$key]['qvantity']-$qv>0&&empty($errors)){
            #pokud cheme odebrat min, nez mame
            $_SESSION['cart'][$key] = ['id' => $id, 'qvantity'=> $_SESSION['cart'][$key]['qvantity']-$qv];
            header('Location: cart.php');
        }elseif($_SESSION['cart'][$key]['qvantity']-$qv==0 &&empty($errors)){
            #pokud chceme odebrat presne tolik, kolik mame
            unset($_SESSION['cart'][$key]);
            header('Location: cart.php');
        }else{
            $errors['category']='You have at '.$_SESSION['cart'][$key]['qvantity'].' your cart, but try to remove :'.$qv;
        }
    }
}
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<title>Cart edit</title>
<div class="container">
    <div class="text-center mt-5">
            <?php
            foreach ($errors as $error){
                echo '<p class="text-danger">'.$error.'</br></p>';
            }
            ?>
        <button class="btn btn-light"><a href="cart.php">Back</a></button>
    </div>
</div>