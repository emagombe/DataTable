# DataTable

This lib allows to process DataTable data content from server-side using PHP

# Demo

Here is the link for demostration
https://edsonmagombe.xyz/datatable/test/
![Demo image](https://github.com/emagombe/datatable/blob/master/demo.png?raw=true)

## Instalation

### Using composer
```bash
composer require emagombe/datatable
```

### Without composer

**Clone** the project or **download** a release from https://github.com/emagombe/datatable/releases
And import autoload.php file to you project
```php
require_once 'autoload.php';
```

## Generating server-side data

### from PDOStatement object
In the static method **create** pass the PDOStatement object then call the **build** object method to execute and return the output
```php
use emagombe\DataTable;

$sql = "SELECT * FROM table;";
$stmt = $conn->prepare($sql);

$dt = DataTable::create($stmt)->build();
echo $dt; 
```

### from Array
Same as above, pass a **array** to the static method **create** then call the **build** object method to execute and return the output
```php
use emagombe\DataTable;

$array = [];
$dt = DataTable::create($array)->build();
echo $dt;
```

## Streaming response
Use the **stream** method to print the response
```php
DataTable::create($result)->stream();
```

## Adding custom column
Call the object method **addColumn** to add a custom column. This method receives the current **row** as parameter;

***Note***: The callback should return a **string** containing the custom content of the column!

```php
$dt = DataTable::create($result)->addColumn("action", function($row) {
	return "<b>".$row["name"]."</b>";
})->build();
```
#### addColumn params  

| order  | data type |
|--------|-----------|
| first  | string    |
| second | method    |