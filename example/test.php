<?php
require_once __DIR__.'/../class.db.php';

$config = [
    'host' => 'localhost',
    'name' => 'temp',
    'username' => 'root',
    'password' => '',
];

$db = new Database();
$db->connect($config);



$gpsdata = $db->table('gpsdata')->select([
    'method' => PDO::FETCH_ASSOC
])->get();

echo '<pre>';
print_r($db->debug());

print_r($gpsdata);
echo '</pre>';

$db->disconnect();
