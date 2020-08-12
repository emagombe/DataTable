<?php

require_once __DIR__."/../autoload.php";

#use DataTable;

$host = "localhost";
$port = 3306;
$database = "mk";
$username = "root";
$password = "";
$charset = "utf8";

$conn = new PDO("mysql:host=$host;dbname=$database;port=$port;charset=$charset", $username, $password);

$sql = "SELECT * FROM gener;";
$stmt = $conn->prepare($sql);
$stmt->execute();

$result = json_encode(DataTable::create($stmt)->build());
header("Content-type: Application/json");

print_r($result);