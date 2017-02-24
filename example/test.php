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



$users = $db->table('users')->delete(['id' => 4]);

echo '<pre>';
print_r($db->debug());

print_r($users);
echo '</pre>';

$db->disconnect();