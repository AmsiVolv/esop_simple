<?php
  //načteme připojení k databázi
  require 'db.php';

  //přístup jen pro přihlášené uživatele
  require 'user_required.php';

//načteme IDčka zboží, které máme v košíku (pročpak je tu asi ten zavináč :-))
$ids=[];
for ($i = 0; $i < @count($_SESSION['cart']); $i++) {
    array_push($ids, @$_SESSION['cart'][$i]['id']);
}
  if (is_array($ids) && count($ids)>0) {
    //vygenerujeme si řetězec s otazníky o takové délce, kolik máme kusů zboží v košíku;pokud mam treba v ids 1,2,3, vrati mi ?,?,?
    $question_marks = str_repeat('?,', count($ids) - 1).'?';

    //připravíme si prepared statement pro načtení zboží podle jeho IDček
    $stmt = $db->prepare("SELECT * FROM goods WHERE id IN ($question_marks) ORDER BY name");
    //naplníme statement IDčky; funkce array_values vrací hodnoty z pole bez ohledu na jeho původní indexy (výsledkem je pole normálně indexované od 0, což je vyžadováno pro naplnění nepojmenovaných parametrů označených ?)
    $stmt->execute(array_values($ids));
    //všechny produkty načteme
    $goods = $stmt->fetchAll();
  }
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>My shopping cart - PHP Shopping App</title>
    <link rel="stylesheet" type="text/css" href="./styles.css">
  </head>
  <body>
	
	  <?php include 'navbar.php' ?>
		
	  <h1>My shopping cart</h1>
	
    Total goods selected: <strong><?php echo (!empty($goods)?count($goods):'0'); ?></strong>
	
	  <br/><br/>
	
    <a href="index.php">Back to the goods</a>
	
	  <br/><br/>

	  <?php
      if (!empty($goods)){
        #region výpis zboží v košíku
        //tentokrát vypisujeme tabulku rovnou z PHP, ale určitě by šlo stejně jako např. na index.php výpis PHP přerušit a vložit do něj HTML
        //sami si vyberte, která varianta vám přijde přehlednější :)
        $sum=0;
        echo '<table>
                <thead>
                  <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Remove/add</th>
                  </tr>
                </thead>
                <tbody>';
        #region výpis jednotlivých položek v košíku
        foreach ($goods as $good){
          $key = array_search($good['id'], array_column($_SESSION['cart'], 'id'));
          $qv = $_SESSION['cart'][$key]['qvantity'];
          echo '  <tr xmlns="http://www.w3.org/1999/html">
                    <td class="center">
                      <a href="remove.php?id=' .$good['id'].'">Remove</a>
                    </td>
                    <td>'.htmlspecialchars($good['name']).'</td>
                    <td class="right">'.$good['price'].'</td>
                    <td>'.htmlspecialchars($good['description']).'</td>
                    <td>
                        <form type="post" action="edit_cart.php">
                            <input required type="number" min="1" name="quantity" id="quantity" placeholder="'.$qv.'">
                            <input type="hidden" value="'.$good['id'].'" name="id">
                            <input type="submit" id="action" value="'.htmlspecialchars('Add').'" name="action">
                            <input type="submit"  id="action" value="'.htmlspecialchars('Remove').'" name="action">
                        </form>
                    </td>
                  </tr>';
          $sum+=$good['price']*$qv;
        }
        #endregion výpis jednotlivých položek v košíku
        echo '  </tbody>
                <tfoot>
                  <tr>
                    <td>SUM</td>
                    <td></td>
                    <td class="right">'.$sum.'</td>
                    <td></td>
                    <td>
                        <button>
                            <a href="#">Remove All!</a>
                        </button>
                    </td>
                  </tr>
                </tfoot>                
              </table>';
        #endregion výpis zboží v košíku
      }else{
        echo 'No goods yet.';
      }
    ?>
  </body>
</html>