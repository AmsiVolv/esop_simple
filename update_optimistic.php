<?php
//připojení k databázi
require 'db.php';

//přístup jen pro admina
require 'admin_required.php';

#region načtení zboží k aktualizaci
$stmt = $db->prepare('SELECT * FROM goods WHERE id=:id');
$stmt->execute([':id'=>@$_REQUEST['id']]);
$goods = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$goods){
    //pokud zboří neexistuje (např. bylo mezitím smazáno), nepokračujeme dál - i když chyba by určitě mohla být vypsána hezčeji :)
    die("Unable to find goods!");
}

$name=$goods['name'];
$description=$goods['description'];
$price=$goods['price'];
#endregion načtení zboží k aktualizaci

if (!empty($_POST)) {
    $formErrors=[];

    #kontrola start

        #kontrola textu
    $name=trim(@$_POST['name']);
    if(empty($name)){
        $formErrors['name']='Name of goods is required!';
    }

    $description=trim(@$_POST['description']);
    if(empty($description)){
        $formErrors['description']='Description of goods is required!';
    }
        #kontola textu end
        #kontrola cisel
    $price=floatval($_POST['price']);
    if(!is_numeric($price)){
        $formErrors['price']='Number is invalid!';
    }
        #kontrola cisel end
    #kontrola end
    //TODO tady by měly být nějaké kontroly odeslaných dat, že :)


    /*
     * OPTIMISTIC LOCK:
     * Před uložením si vytáhneme z DB čas poslední změny. Pokud se tento liší od času předaného z formuláře (tj. času na začátku editace), znamená to, že se záznam mezitím v pozadí změnil. (Jiný uživatel provedl update. Mohl to být ale i ten samý uživatel např. v jiném okně prohlížeče.)
     * V případě, že se záznam v mezičase změnil, je nutné se NĚJAK zachovat. Je možné uživatele varovat, nabídnout přeuložení, sloučení změn atd., nebo prostě jen umřít s hláškou, že záznam byl změněn.
     *
     * Proměnnou $_POST['last_updated_at'] si předáváme ve formuláři jako hidden pole.
     */

    if ($_POST['last_updated_at'] != $goods['last_updated_at']) {
        //Upravte řešení optimistického zamykání záznamů (v souboru update_optimistic.php) tak, aby aplikace při zjištění konfliktu zobrazila změněná data a zeptala se uživatele, zda si je přeje přepsat daty svými.
        $stmt = $db->prepare('SELECT * FROM goods WHERE id=:id;');
        $stmt->execute([
            ':id'=>$goods['id']
        ]);
        $lastEditInfo=$stmt->fetch();
        echo '<p class="text-danger ml-2 mt-2 text-center">We found that someone changed the data before you. Do you want to rewrite them?</p>';
        $timestamp = strtotime(htmlspecialchars($lastEditInfo['last_updated_at']));
        echo '
<title>Confirmation page</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<form method="post" action="update_optimistic.php">
<input type="hidden" name="id" value="'.$goods['id'].'">
<input type="hidden" name="last_updated_at" value="'.$goods['last_updated_at'].'">
<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">Last edit was '.date('d.m.Y H:i', $timestamp).'</th>
      <th scope="col">Information from DB</th>
      <th scope="col">New info</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Name</th>
      <td>'.htmlspecialchars($lastEditInfo['name']).'</td>
      <td>'.htmlspecialchars($name).'</td>
      <input type="hidden" name="name" value="'.htmlspecialchars($name).'" readonly>
    </tr>
    <tr>
      <th scope="row">Price</th>
      <td>'.htmlspecialchars($lastEditInfo['price']).'</td>
      <td>'.htmlspecialchars($price).'</td>
      <input type="hidden" name="price" value="'.htmlspecialchars($price).'">
    </tr>
    <tr>
      <th scope="row">Description</th>
      <td>'.htmlspecialchars($lastEditInfo['description']).'</td>
      <td>'.htmlspecialchars($description).'</td>
      <input type="hidden" name="description" value="'.htmlspecialchars($description).'">
    </tr>
  </tbody>
</table>
<div class="text-center">
    <button type="button" class="btn btn-danger"><a style="color: white; text-decoration: none;" href="index.php">Cansel</a></button>
    <input  type="submit" name="BTN_name" class="btn btn-success" value="Save"></button>
</div>  
</form>
';
        exit();
    }

    //pokud se časy poslední editace záznam a čas z formuláře rovnají, tj. záznam nebyl mezitím změněn, můžeme provést update - tedy pokud formulář neobsahuje jiné chyby
    //nakonec také zaktualizujeme čas poslední aktualizace uložený u daného zboží
    if (empty($formErrors) or isset($_POST['BTN_name'] )){
        $errors = [];
        if(isset($_POST['BTN_name'])){
            //Jelikož to dělám přes hidden formulář, tak měl by provést jednu kontrolu navíc, pokud uživatel bude se snažit manipulovat s HTML, resp. atributem value a formuláře.
            //Avšak je to hloupé řešení, protože tato část kódu se duplikuje s první kontrolou. Vím určité, že to se dá udělat mnohem víc elegantněji, ale pokusím se to opravit již po odevzdaní(no time)
            $name=trim(@$_POST['name']);
            if(empty($name)){
                $errors['name']='Name of goods is required!';
            }
            $description=trim(@$_POST['description']);
            if(empty($description)){
                $errors['description']='Description of goods is required!';
            }
            #kontola textu end
            #kontrola cisel
            $price=floatval($_POST['price']);
            if(!is_numeric($price)){
               $errors['price']='Number is invalid!';
            }
        }
            if(empty($errors)){
                #srartregion uložení zboží do DB
                $stmt = $db->prepare('UPDATE goods SET name=:name, description=:description, price=:price, last_updated_at=now() WHERE id=:id LIMIT 1;');
                $stmt->execute([
                    ':name'=> $name,
                    ':description'=> $description,
                    ':price'=>$price,
                    ':id'=> $goods['id']
                ]);
                #endregion uložení zboží do DB
                //přesměrování na homepage
                header('Location: index.php');
                exit();
            }else{
                    echo '<div style="color:red">Oops, you tried to edit HTML</div>';
                    echo '<a href="index.php">Back</a>';
                    exit(var_dump($errors));
            }
        }

}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>PHP Shopping App</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
<?php include 'navbar.php' ?>
<h1>Update goods</h1>

<form method="post">
        <?php
        if (!empty($formErrors['name'])){
            echo '<div style="color:red";>Error: "'.$formErrors['name'].'"</div>';
        }else{
            echo '<label for="name">Name</label><br/>';
        }
        ?>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(@$name);?>" required><br/><br/>
    <?php
    if (!empty($formErrors['price'])){
        echo '<div style="color:red";>Error: "'.$formErrors['price'].'"</div>';
    }else{
        echo '<label for="name">Price</label><br/>';
    }
    ?>
        <input type="number" min="0" name="price" id="price" required value="<?php echo htmlspecialchars(@$price)?>"><br/><br/>

    <?php
    if (!empty($formErrors['description'])){
        echo '<div style="color:red";>Error: "'.$formErrors['description'].'"</div>';
    }else{
        echo '<label for="name">Description</label><br/>';
    }
    ?>
        <textarea name="description" id="description"><?php echo htmlspecialchars(@$description)?></textarea><br/><br/>

        <br/>

        <input type="hidden" name="id" value="<?php echo $goods['id']; ?>" />

        <input type="hidden" name="last_updated_at" value="<?php echo $goods['last_updated_at']; ?>">
        <!--hidden pole používáme pro předání informace o čase poslední změny záznamu-->

        <input type="submit" value="Save" /> or <a href="index.php">Cancel</a>

</form>

</body>
</html>
