<?php

require("config.php");

//获取参数
$request = $_SERVER["REQUEST_URI"];
if ($request == "/")
    $redirectTo = "create.html";
else {
    $redirectTo = "404.html";
    $request = str_replace("/", "", $request);
    // 创建数据库连接
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // 检测数据库连接
    if (!$conn) {
        $redirectTo = "connectionerror.html";
    } else {
        // echo "连接成功";
        $sql = "SELECT * FROM shortLink WHERE shorten = '$request'";
        // echo $sql;
        $databaseResult = $conn->query($sql);
        while ($row = $databaseResult->fetch_assoc()) {
            if ($row["expire"] == 0 | $row["expire"] > time()) {
                $redirectTo = $row["origin"];
                break;
            }
        }
    }
}

// echo $redirectTo;
Header("HTTP/1.1 301 Moved Permanently");
Header("Location: $redirectTo");
