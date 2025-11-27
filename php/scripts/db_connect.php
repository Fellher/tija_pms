<?php
// Only create DB connection if config is available and DBConn doesn't already exist
if (isset($config) && !isset($DBConn)) {
    // ob_start();
    // $DBConn = new MySQLConnection($config['DBUser'], $config['DBPassword'], $config['DBHost'], $config['DB']);
    // $connect = $DBConn->connect();
    // ob_clean();

    ob_start();
    $DBConn= new mysqlConnect($config['DBUser'], $config['DBPassword'], $config['DBHost'], $config['DB']);
    $conn= $DBConn->connect();

    ob_clean();
}

?>