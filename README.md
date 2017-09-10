# PDO MySql driver class for PHP

## Introduction
This is simple class for SELECT, INSERT, UPDATE, DELETE query for MySQL

## Installation 
`composer require hadi/database`

if you don't want composer then simple grab class file from src/Database.php and use it!

## Usage

### Connection
```php
$config = [
    'host' => 'localhost',
    'name' => 'temp',
    'username' => 'root',
    'password' => '',
];

$db = new \Hadi\Database();
$db->connect($config);
```

### Disconnect
```php
$db->disconnect();
```
    
### Select Query

#### Method #1
```php
$db->query('SELECT * FROM users')->get();
```

```php
$db->query('SELECT * FROM users')->first();
```
    
#### Method #2

```php
$db->table('users')->select([
    'field' => ['name', 'username'],
])->first();
```

```php
$db->table('users')->select([
    'field' => ['name', 'username'],
    'condition' => 'WHERE id > 0',
    'limit' => '0, 10',
    'orderby' => 'name',
    'groupby' => 'name',
])->first();
```
    
### Insert

Insert data:
```php
$db->table('users')->insert(['name' => 'John doe', 'email' => 'john@email.com']);
```

Insert data when supplied email `john@email.com` not exists in table `users`:

```php
$db->table('users')->insert(
    ['name' => 'John doe', 'email' => 'john@email.com'],
    ['email']
);
```

##### result

```
affected_row
inserted_id
is_duplicate
```
    
### Update

Update data where `id = 1`
```php
$db->table('users')->update(
    ['name' => 'John doe', 'email' => 'john@email.com'],
    ['id' => 1]
);
```

or 
```php
$db->table('users')->update(
    ['username' => 'johndoe'],
    'id = 1'
);
```

update `username` if nobody else using same username

```php
$db->table('users')->update(
    ['username' => 'johndoe'],
    ['id' => 4],
    ['username']
);
```

##### result
```
affected_row
is_duplicate
```
    
### Delete
```php
$db->table('users')->delete(['id' => 4]);
```

##### result
```
affected_row
```
