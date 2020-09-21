<?php

require_once __DIR__."/../autoload.php";

use emagombe\DataTable;


$result = json_decode(file_get_contents(__DIR__."/data.json"), true);

$table = DataTable::create($result)->addColumn("action", function($row) {
	$id = $row["id"];
	return "<button class=\"ui button icon small circular orange\" name=\"$id\">".$row["name"]."</button>";
});

$table->searchColumn("name", "aca");

$table->stream();