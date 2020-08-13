# DataTable

This lib allows to process DataTable data content from server-side using PHP

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

$dt = DataTable::create($result)->build();
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

