<?php
require_once __DIR__ . '/../vendor/autoload.php';

$database = new \Hadi\Database();
$database->dbErrorMsg = 'sssss';
var_dump($database);