<?php

header("Access-Control-Allow-Origin: *");
// 获取传入参数
$link = str_replace("&amp;", "&", isset($_GET['link']) ? htmlspecialchars($_GET['link']) : '');
$day = isset($_GET['day']) ? htmlspecialchars($_GET['day']) : '';
$age = isset($_GET['age']) ? htmlspecialchars($_GET['age']) : '';
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
// 判断链接是否正确
if (strpos($link, "http://", 0) !== 0 & strpos($link, "https://", 0) !== 0) {
    $result = array("code" => -1, "msg" => "No link or not a link.");
} else if (strpos($link, $_SERVER["HTTP_HOST"], 0) !== false) {
    $result = array("code" => -5, "msg" => "The link has been shorten.");
} else {
    if ($age != "on") {
        $day = 0;
        $expire = 0;
    } else { // 计算时间戳
        $expire = time() - time() % 86400 + ($day * 86400 - $day % 86400);
    }
    $servername = "localhost";
    $username = "s9938012";
    $password = "nmnm";
    $dbname = "s9938012";
    // 创建数据库连接
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // 检测数据库连接
    if (!$conn) {
        $result = array("code" => -2, "msg" => "Connection to the database failed: " . mysqli_connect_error());
    } else {
        // echo "连接成功";

        // 生成随机字符串
        $shortResult = newShort($link);
        // 确认字符串可用性
        $sql = "SELECT shorten, origin, expire FROM shortLink";
        $databaseResult = $conn->query($sql);
        // 输出数据
        while ($row = $databaseResult->fetch_assoc()) {
            if ($shortResult == $row["shorten"]) {
                $result = array("code" => -3, "msg" => "The shorten url has been used. Please retry. If this error happens all the time, please contact the site admin.");
                goto sendResult;
            }
            if ($link == $row["origin"] & $expire == $row["expire"]) {
                $result = array("code" => 1, "msg" => $http_type . $_SERVER['HTTP_HOST'] . "/" . $row["shorten"]);
                goto sendResult;
            }
        }
        $sql = "INSERT INTO shortLink (shorten, origin, expire, requestTime, requestIP, requestURL) VALUES ('$shortResult', '$link', '$expire', '" . time() . "', '" . get_client_ip() . "', '" . $_SERVER["HTTP_REFERER"] . "')";
        // echo ($sql);
        if ($conn->query($sql) === TRUE) {
            // echo "新记录插入成功";
            $result = array("code" => 0, "msg" => "https://" . $_SERVER['HTTP_HOST'] . "/" . $shortResult);
        } else {
            $result = array("code" => -4, "msg" => "Try to write to the database but failed: " . $conn->error);
        }


        $conn->close();
    }
}

sendResult:
echo json_encode($result);
// echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
// echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

function newShort($url)
{
    $shortResult = "";
    $urlLength = 6;
    $shortWith = "0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
    for ($i = 0; $i < $urlLength; $i++) {
        $shortResult[$i] = $shortWith[mt_rand(0, mb_strlen($shortWith) - 1)];
    }
    return $shortResult;
}

function get_client_ip()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] as $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}
