<?php

require_once __DIR__."/../autoload.php";

use src\DataTable\DataTable;

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

$result = [];
foreach($stmt as $item) {
	$result[] = $item;
}

$dt = DataTable::create($result)->addColumn("action", function($row) {
	return "Hello".$row["id"];
})->build();
$result = json_encode($dt);
header("Content-type: Application/json");

print_r($result);