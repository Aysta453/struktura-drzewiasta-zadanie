<?php
session_start();
include_once ("php/connect.php");
@$_SESSION['message'];


if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['form_type'] == 'form_add') {

    @$name=$_POST['nameOfElement'];
    @$parent=$_POST['selectAdd'];

    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {
        //run the store proc
        $sql= "CALL dodajWezel('{$name}',$parent);";
        $wynik = $polaczenie->query($sql);

        $_SESSION['message']="Dodano nowy wezeł";
        $polaczenie->close();
    }
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['form_type'] == 'form_rename') {

        @$name=$_POST['newName'];
        @$id=$_POST['selectRename'];

        $polaczenie = @new mysqli($servername, $username, $password, $dbname);

        if ($polaczenie->connect_errno != 0) {
            echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
        } else {
            $sql= "CALL zmienNazwe('{$name}',$id);";
            $wynik = $polaczenie->query($sql);
            $_SESSION['message']="Zmieniono nazwe węzła";
            $polaczenie->close();
        }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['form_type'] == 'form_move') {

    $id_wezla = $_POST['selectSource'];
    $id_nowego_rodzica = $_POST['selectDestination'];

    setDestination($id_wezla,$id_nowego_rodzica);

}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['form_type'] == 'form_delete') {
    $id=$_POST['selectDelete'];
    deleteTree($id);

}



function deleteTree($id){

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";
    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {

        $query = "SELECT * FROM countries WHERE parent_id='{$id}';";
        $result = $polaczenie->query($query);
        while($row = $result->fetch_assoc()) {
            deleteTree($row['id']);
        }
        $query = "DELETE FROM countries WHERE id='$id';";
        $result = $polaczenie->query($query);
        $_SESSION['message']="Usunieto wezeł";
        $polaczenie->close();
    }

}


function setDestination($id_wezla,$id_nowego_rodzica)
{
    $nazwa_tabeli = "countries";
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";
    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {

            $query = "SELECT id, parent_id FROM countries WHERE id = '{$id_wezla}';";
            $result = $polaczenie->query( $query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id_rodzica_wezla = $row['parent_id'];

                    if ($id_wezla == $id_nowego_rodzica) {
                        $_SESSION['message']= "Wybrano ten sam węzeł";

                    } elseif ($id_rodzica_wezla == $id_nowego_rodzica) {
                        $_SESSION['message']="Węzeł jest już dzieckiem tego rodzica";

                    } else {
                        $query = "SELECT GROUP_CONCAT(lv SEPARATOR ',') AS potomkowie FROM (SELECT @pv:=(SELECT GROUP_CONCAT(id SEPARATOR ',')" .
                            " FROM countries WHERE parent_id IN (@pv)) AS lv FROM countries" .
                            " JOIN (SELECT @pv:={$id_wezla})tmp WHERE parent_id IN (@pv)) a;";
                        $result = $polaczenie->query( $query);
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                break;
                            }

                            $potomkowie = $row['potomkowie'];
                            $potomkowie_tablica = explode(",", $potomkowie);

                            if (in_array($id_nowego_rodzica, $potomkowie_tablica)) {
                                $_SESSION['message']="Nie można przenieść węzła do niższego poziomu w tej samej gałęzi";

                            } else {

                                // Zamienia w bazie danych wartość pola 'parent_id' wybranego węzła na ID nowego rodzica.
                                $query = "UPDATE {$nazwa_tabeli} SET parent_id = '{$id_nowego_rodzica}' WHERE id = '{$id_wezla}';";
                                $result = $polaczenie->query($query);

                                if ($result) {
                                    $_SESSION['message']= "Węzeł został przeniesiony";

                                } else {
                                    $_SESSION['message']= "Wystąpił błąd podczas przenoszenia węzła";
                                }
                            }
                        } else {


                            $query = "UPDATE {$nazwa_tabeli} SET parent_id = '{$id_nowego_rodzica}' WHERE id = '{$id_wezla}';";
                            $result =  $polaczenie->query($query);

                            if ($result) {
                                $_SESSION['message']="Węzeł został przeniesiony";

                            } else {
                                $_SESSION['message']= "Wystąpił błąd podczas przenoszenia węzła";
                            }
                        }
                        $result =$polaczenie->query($query);
                    }
                    break;
                }
            } else {
                $_SESSION['message']= "Wystąpił błąd podczas przenoszenia węzła";
            }
        }
}

function childs($id){

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";
    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {
        $sql= "select * from countries where parent_id={$id}";
        $wynik = $polaczenie->query($sql);

        while ($r = $wynik->fetch_assoc()) {
            $checking=checkIfChilds($r['id']);
            if($checking>0){
                echo "<button class=\"collapsible\">" . $r['text'] . "</button>";
                echo "<div class=\"content\">";
                $rs = $r['id'];
                childs($rs);
                echo "</div>";
            }else{
                echo "<div class=\"content2\">".$r['text']."</div>";
            }
        }
        $polaczenie->close();
    }
}

function childs1($id){

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";
    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {
        $sql= "select * from countries where parent_id={$id} order by text asc";
        $wynik = $polaczenie->query($sql);

        while ($r = $wynik->fetch_assoc()) {
            $checking=checkIfChilds($r['id']);
            if($checking>0){
                echo "<button class=\"collapsible\">" . $r['text'] . "</button>";
                echo "<div class=\"content\">";
                $rs = $r['id'];
                childs($rs);
                echo "</div>";
            }else{
                echo "<div class=\"content2\">".$r['text']."</div>";
            }
        }
        $polaczenie->close();
    }
}
function childs2($id){

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";
    $polaczenie = @new mysqli($servername, $username, $password, $dbname);

    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {
        $sql= "select * from countries where parent_id={$id} order by text desc";
        $wynik = $polaczenie->query($sql);

        while ($r = $wynik->fetch_assoc()) {
            $checking=checkIfChilds($r['id']);
            if($checking>0){
                echo "<button class=\"collapsible\">" . $r['text'] . "</button>";
                echo "<div class=\"content\">";
                $rs = $r['id'];
                childs($rs);
                echo "</div>";
            }else{
                echo "<div class=\"content2\">".$r['text']."</div>";
            }
        }
        $polaczenie->close();
    }
}

function checkIfChilds($id){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "countries";

    $polaczenie = @new mysqli($servername, $username, $password, $dbname);
    if ($polaczenie->connect_errno != 0) {
        echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
    } else {
        $sql = "select * from countries where parent_id={$id}";
        $wynik = $polaczenie->query($sql);
        $row = mysqli_num_rows($wynik);
        $polaczenie->close();
        return $row;
    }
}

function komunikat($tresc){
    echo '<script>
    alert("' . $tresc . '");
</script>';
}

if(isset($_SESSION['message'])) {
    komunikat($_SESSION['message']);
    unset($_SESSION['message']);
};

?>
<html>
<head>

    <script>
        function validateFormMove() {
            var y = document.forms["formMove"]["selectSource"].value;
            var x = document.forms["formMove"]["selectDestination"].value;

            if (x == y) {
                alert("Nie jest mozliwe w to samo miejsce");
                return false;
            }
        }
    </script>
    <script>
        function validateFormAdd() {
            var regexp;
            var x = document.forms["form_add"]["nameOfElement"].value;

            if (x == "") {
                alert("Nazwa jest pusta, wypełnij.");
                return false;
            }else{
                regexp = /^[A-Za-z \t\r\n\f]*$/;
                if (!regexp.test(x))
                {
                    alert("Nazwa może zawierać litery i spacje");
                    return false;
                }
            }
        }
    </script>
    <script>
        function validateFormRename() {
            var regexp;
            var x = document.forms["formRename"]["newName"].value;
            if (x == "") {
                alert("Nazwa jest pusta, wypełnij.");
                return false;
            }else{
                regexp = /^[A-Za-z \t\r\n\f]*$/;
                if (!regexp.test(x))
                {
                    alert("Nazwa może zawierać litery i spacje");
                    return false;
                }
            }

        }
    </script>
    <link href="styles/style.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="main">
       <div class="left">
           <div class="left_element">
                <form  name ='form_add' action="./index.php" onsubmit="return validateFormAdd();" method="post">
                    <input type="hidden" name="form_type" value="form_add">
                    <div class="left_element_info">
                        <p>Dodaj nowy węzeł</p>
                    </div>
                    <div class="left_element_first">
                        <span><p>Nazwa węzła: </p></span>
                        <input type="text" name="nameOfElement">
                    </div>
                    <div class="left_element_second">
                        <p>Miejsce docelowe:</p>
                        <select name="selectAdd" id="selectAdd">
                        <option value="0">Dodaj jako nowy węzeł</option>
                            <?php

                                $polaczenie = @new mysqli($servername, $username, $password, $dbname);

                                if ($polaczenie->connect_errno != 0) {
                                    echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
                                } else {
                                    $sql= "select * from countries";
                                    $wynik = $polaczenie->query($sql);
                                    while ($r = $wynik->fetch_assoc()) {
                                        echo "<option value=\"".$r['id']."\">".$r['text']."</option>";
                                    }
                                    $polaczenie->close();
                                }
                            ?>
                        </select>
                    </div>
                    <div class="left_element_button">
                        <input type="submit" name="button_add" value="Dodaj węzeł" class="left_button">
                    </div>
                </form>
            </div>
           <div class="left_element">
            <form name="formMove" action="./index.php" method="post" onsubmit="return validateFormMove();">
                <input type="hidden" name="form_type" value="form_move">
                <div class="left_element_info">
                    <p>Przenieś węzeł</p>
                </div>
                <div class="left_element_first">
                    <p>Wybierz węzeł:</p>
                    <select name="selectSource" id="selectSource">
                        <?php
                        require_once "php/connect.php";
                        $polaczenie = @new mysqli($servername, $username, $password, $dbname);

                        if ($polaczenie->connect_errno != 0){
                            echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
                        } else {

                            $sql= "select * from countries";
                            $wynik = $polaczenie->query($sql);
                            while ($r = $wynik->fetch_assoc()) {
                                echo "<option value=\"".$r['id']."\">".$r['text']."</option>";
                            }
                            $polaczenie->close();
                        }
                        ?>
                    </select>
                </div>
                <div class="left_element_second">
                    <p>Wybierz nowego rodzica:</p>
                    <select name="selectDestination" id="selectDestination">
                        <?php

                        require_once "php/connect.php";
                        $polaczenie = @new mysqli($servername, $username, $password, $dbname);

                        if ($polaczenie->connect_errno != 0) {
                            echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
                        } else {

                            $sql= "select * from countries";
                            $wynik = $polaczenie->query($sql);
                            while ($r = $wynik->fetch_assoc()) {
                                echo "<option value=\"".$r['id']."\">".$r['text']."</option>";

                            }
                            $polaczenie->close();
                        }
                        ?>
                    </select>
                </div>
                <div class="left_element_button">
                    <input type="submit" name="button_move" value="Przenieś" class="left_button">
                </div>
            </form>
        </div>
           <div class="left_element">

               <form name="formRename" action="./index.php" onsubmit="return validateFormRename();" method="post">
                   <input type="hidden" name="form_type" value="form_rename">
                   <div class="left_element_info"><p>Zmień nazwe węzła</p></div>
                   <div class="left_element_first">
                       <p>Wybierz węzeł:</p>
                       <select name="selectRename" id="selectRename">
                           <?php
                                require_once "php/connect.php";
                                $polaczenie = @new mysqli($servername, $username, $password, $dbname);

                                if ($polaczenie->connect_errno != 0) {
                                    echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
                                } else {

                                    $sql= "select * from countries";
                                    $wynik = $polaczenie->query($sql);
                                    while ($r = $wynik->fetch_assoc()) {
                                        echo "<option value=\"".$r['id']."\">".$r['text']."</option>";

                                    }
                                    $polaczenie->close();
                                }
                            ?>
                       </select>
                   </div>
                   <div class="left_element_second">
                       <p>Nowa nazwa węzła: </p>
                       <div class="left_element_first">
                           <input type="text" name="newName">
                       </div>
                   </div>
                   <div class="left_element_button">
                       <input type="submit" name="button_rename" value="Zatwierdź zmianę" class="left_button">
                   </div>
               </form>
           </div>
           <div class="left_element2">
               <form name="deleteForm"  action="./index.php" method="post">
                   <input type="hidden" name="form_type" value="form_delete">
                   <div class="left_element_info"><p>Usuń wezęł</p></div>
                   <div class="left_element_first2">
                       <p>Wybierz węzeł: </p>
                       <select name="selectDelete" id="selectDelete">
                           <?php
                                require_once "php/connect.php";
                                $polaczenie = @new mysqli($servername, $username, $password, $dbname);

                                if ($polaczenie->connect_errno != 0) {
                                    echo "Error" . $polaczenie->connect_errno . " Opis: " . $polaczenie->connect_error;
                                } else {

                                    $sql= "select * from countries";
                                    $wynik = $polaczenie->query($sql);
                                    while ($r = $wynik->fetch_assoc()) {
                                        echo "<option value=\"".$r['id']."\">".$r['text']."</option>";

                                    }
                                    $polaczenie->close();
                                }
                            ?>
                       </select>
                   </div>
                   <div class="left_element_button2">
                       <input  type="submit" name="button_delete" value="Usuń" class="left_button">
                   </div>
               </form>
           </div>
           <div class="left_element2">
               <form action="./index.php" method="post">
                   <div class="left_element_info"><p>Sortowanie nazwe węzła</p></div>
                   <div class="left_element_button2">
                       <input type="submit" default name="button_change" value="Brak sortowania" class="left_button">
                   </div>
                   <div class="left_element_button2">
                       <input type="submit" name="button_change" value="Rosnąco" class="left_button">
                   </div>
                   <div class="left_element_button2">
                       <input type="submit" name="button_change" value="Malejąco" class="left_button">
                   </div>
               </form>
           </div>
    </div>
      <div class="right">
         <div class="right_element">
             <?php

                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['button_change'] == 'Brak sortowania') {
                        childs(0);

                    }else if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['button_change'] == 'Rosnąco') {
                        childs1(0);

                    }else if ($_SERVER['REQUEST_METHOD'] == 'POST' && @$_POST['button_change'] == 'Malejąco') {
                        childs2(0);

                    }else{
                        childs1(0);
                    }


            ?>
        </div>
    </div>
</div>
    <script>
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.maxHeight){
                    content.style.maxHeight = null;
                } else {
                    content.style.maxHeight = 20*content.scrollHeight + "px";
                }
            });
        }
    </script>
</body>
</html>


