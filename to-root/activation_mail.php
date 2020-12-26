<html>
    <head>
        <title>Подтверждение почты | Расписание онлайн by deVDem</title>
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
                $query = $connect->query("SELECT `keyMail`, `userId` FROM `mail_activation` WHERE `keyMail`=\"$key\"");
                $keyInfo = $query->fetch_assoc();
                if($keyInfo==null) {
                    goError("Ключ не действительный. Проверьте правильность ссылки", 0x03);
                } else {
                    $user=getUser($keyInfo['userId'], $connect);
                    if($user==null) {
                        goError("Ключ не действительный. Проверьте правильность ссылки", 0x04);
                    } else {
                        $connect->query("UPDATE `users` SET `confirmed`='Yes' WHERE `id`=".$keyInfo['userId']);
                        $connect->query("DELETE FROM `mail_activation` WHERE `keyMail` =".'"'.$keyInfo['keyMail'].'"');
                        ok("Успешно. Спасибо за подтверждение Вашей почты, это важно для меня", 0x05);
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