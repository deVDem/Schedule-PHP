<html>
    <head>
        <meta charset="utf-8">
        <title>Восстановление аккаунта | Расписание онлайн by deVDem</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSS Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <!-- JavaScript Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="container mt-3">
        <?php
        $key = $_GET['key'];
        if(!isset($key)) $key = $_POST['key'];
        $password = $_POST['newPass'];
        if ($key != null && $key != "") {
            $credinals = json_decode(file_get_contents("D:\Sites\credinals.json"), true);
            $connect = mysqli_connect(
                $credinals['mySQL']['host'],
                $credinals['mySQL']['account'][0]['user'],
                $credinals['mySQL']['account'][0]['password'],
                $credinals['mySQL']['account'][0]['database'],
                $credinals['mySQL']['port']);
            if (!$connect) {
                goError("Ошибка в базе данных", 0x01);
            } else {
                $query = $connect->query("SELECT `keyMail`, `userId` FROM `restore_keys` WHERE `keyMail`=\"$key\"");
                $keyInfo = $query->fetch_assoc();
                if($keyInfo==null) {
                    goError("Ключ не действительный. Проверьте правильность ссылки", 0x03);
                } else {
                    $user=getUser($keyInfo['userId'], $connect);
                    if($user==null) {
                        goError("Ключ не действительный. Проверьте правильность ссылки", 0x04);
                    } else {
                        if(isset($password)) {
                            $connect->query("UPDATE `users` SET `confirmed`='Yes', `password`=\"".password_hash($password, PASSWORD_DEFAULT)."\" WHERE `id`=".$keyInfo['userId']);
                            $connect->query("DELETE FROM `restore_keys` WHERE `keyMail` =".'"'.$keyInfo['keyMail'].'"');
                            ok("Успешно. Ваш пароль сменён, вернитесь в приложение и введите его. Так же ваша почта теперь подтверждена", 0x05);
                        } else getPass($key);
                    }
                }
            }
        } else {
            goError("Не найден ключ в ссылке. Проверьте правильность ссылки", 0x02);
        }

        $connect->close();


        function goError($message, $code)
        {
            echo "<div class=\"alert alert-danger\" role=\"alert\">";
            echo "<h1>Произошла ошибка</h1>";
            echo "<hr/>";
            echo "<h5>".$message."</h5>";
            echo "Код ошибки: ".$code;
            echo "</div>";
        }

        function ok($message, $code) {
            echo "<div class=\"alert alert-success\" role=\"alert\">";
            echo "<h1>Успешно</h1>";
            echo "<hr/>";
            echo "<h5>".$message."</h5>";
            echo "Код ответа: ".$code;
            echo "</div>";
        }

        function getPass($key) {
            echo "<div class=\"alert alert-warning\" role=\"alert\">";
            echo "<h1>Требуются данные</h1>";
            echo "<hr/>";
            echo "<h5>Отлично, осталось немного. Введите новый пароль для аккаунта</h5>";
            echo "<form action=\"restore.php\" method=\"POST\">";
            echo "<label class=\"me-2\">Новый пароль: </label>";
            echo "<input type=\"password\" name=\"newPass\">";
            echo "<input hidden name=\"key\" value=\"".$key."\">";
            echo "<br/>";
            echo "<input class=\"btn btn-success\" type=\"submit\">";
            echo "</form>";
            echo "</div>";
        }

        function getUser($id, $connect)
        {
            $user = null;
            $query = $connect->query("SELECT * FROM `users` WHERE `id`=\"$id\"");
            while ($row = $query->fetch_assoc()) {
                $user = $row;
            }
            return $user;
        }
        ?>
        </div>
    </body>
</html>