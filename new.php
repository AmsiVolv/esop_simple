<?php
  //připojení k databázi
  require 'db.php';

  //přístup jen pro admina
  require 'admin_required.php';
	
  if (!empty($_POST)){
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

    if (empty($formErrors)){
      #region uložení zboží do DB
      $stmt = $db->prepare("INSERT INTO goods(name, description, price) VALUES (:name, :description, :price)");
      $stmt->execute([
        ':name'=>$_POST['name'],
        ':description'=>$_POST['description'],
        ':price'=>floatval($_POST['price'])
      ]);
        //přesměrování na homepage
        header('Location: index.php');
        exit();
      #endregion uložení zboží do DB
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
	
	  <h1>New goods</h1>

    <form method="post">
        <?php
        if (!empty($formErrors['name'])){
            echo '<div style="color:red";>Error: "'.$formErrors['name'].'"</div>';
        }else{
            echo '<label for="name">Name</label><br/>';
        }
        ?>
      <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(@$_POST['name']);?>" required placeholder="Name"><br/><br/>

        <?php
        if (!empty($formErrors['price'])){
            echo '<div style="color:red";>Error: "'.$formErrors['price'].'"</div>';
        }else{
            echo '<label for="name">Price</label><br/>';
        }
        ?>
      <input type="number" min="0" name="price" id="price" required value="<?php echo htmlspecialchars(@$_POST['price'])?>" placeholder="Price"><br/><br/>

        <?php
        if (!empty($formErrors['description'])){
            echo '<div style="color:red";>Error: "'.$formErrors['description'].'"</div>';
        }else{
            echo '<label for="name">Description</label><br/>';
        }
        ?>
      <textarea name="description" id="description" required placeholder="Description"><?php echo htmlspecialchars(@$_POST['description'])?></textarea><br/><br/>

      <br/>

      <input type="submit" value="Save"> or <a href="index.php">Cancel</a>
    </form>

  </body>
</html>
