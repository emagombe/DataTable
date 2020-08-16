<?php

require_once __DIR__."/../autoload.php";

use emagombe\DataTable;

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

DataTable::create($result)->addColumn("action", function($row) {
	return "<b>".$row["id"]."</b>";
})->stream();