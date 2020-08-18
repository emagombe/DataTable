<?php

require_once __DIR__."/../autoload.php";

use emagombe\DataTable;


$result = json_decode(file_get_contents(__DIR__."/data.json"), true);

DataTable::create($result)->addColumn("action", function($row) {
	return "<b>".$row["id"]."</b>";
})->stream();