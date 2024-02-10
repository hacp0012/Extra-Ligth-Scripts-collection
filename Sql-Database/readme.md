# PHP LIGHTWEIGHT DATABASE HANDLER

A light tool to manage mySql request fast and fast et efficient.

## Documentation

### Installation

Juste thes tow files need to be placed in the same fold.

[database.php](database.php) file is the main file that containe all static functions to work with.

[dbConfig.php](dbConfig.php) is the config file that you can customize any way you want.

## Usage

*Configure database connection*

```php
const DATABASE_HOST     = "localhost";
const DATABASE_NAME     = "rdcfruits";
const DATABASE_USERNAME = "root";
const DATABASE_PASSWORD = "";
```

*Useage*

```php
use Extra\Database;

// Retrive fruits
$fruits = Database::get(table: 'fruits', columns: ['*'], where: ['name'=>'apple']);

// Insert new record in table fruits.
$sertedIndex = Database::add(
    table: 'fruits',
    values: [
        'name' => 'apple',
        'type' => 'table',
        'description' => Database::escape("Esse excepteur enim voluptate exercitation cillum ipsum culpa pariatur."),
    ]
);

# Retrive via pure sql query.
$knifs = Database::query("SELECT * FROM `knifs` WHERE `type` = 'table'");
```

## Functions

### get

`$where` if array is given array must be key-value pair.

- __value__ must accept only __int, float, string, null__.
- __key__ can accept some rules : ex: `['age:>'=> 18]`. that will produce : `age > 18` in sql query.
- when the __where__ array contain an index key value some as `['key'=>'val', 'AND', 'age:>'=>18]`. this will produce in sql query : `key = 'val' AND age > 18`.

```php
/** get
 * @param string $table the tab name
 * @param array $columns columns to retrive
 * @param array|string $where where condition.
 * if array is given array must be key-value pair. 
 * - __value__
 * must accept only __int, float, string, null__.
 * - __key__ can accept some rules : ex: `['age:>'=> 18]`. that
 * will produce : `age > 18` in sql query.
 * - when the _where_ array contain an index key value some as
 * `['key'=>'val', 'AND', 'age:>'=>18]`. this will 
 * produce in sql query : `key = 'val' AND age > 18`.
 * @param string $condition contition that will be applie default : AND|OR
 * @param string $operator operator to use evrey where in array map it replace `=>`.
 * @param bool $returnNumRows return type will be `int` if `true`. the number of
 * rows fetched.
 */
static function get(
    string $table,
    array $columns = ["*"],
    array|string $where = [],
    string $condition = "AND",
    string $operator = "=",
    bool $returnNumRows = false,
): null|array|int
```

### getOneIn

```php
/** getOneIn
 * renvoie une seul valeur en `array`. is a __get__ alias.
 * 
 * renvoie `null` si aucune veleur n'a etait trover et 
 * renvoie `false` si beaucoups des valeurs ont etaient trover.
 */
static function getOneIn(
    string  $table,
    array   $columns = ["*"],
    array|string   $where = [],
    string  $condition = "AND",
    string  $operator = "="
): array|null|bool
```

### edit

__where__ condition.
if array is given array must be key-value pair.

- __value__ must accept only __int, float, string, null__.
- __key__ can accept some rules : ex: `['age:>'=> 18]`. that will produce : `age > 18` in sql query.
- when the __where__ array contain an index key value some as `['key'=>'val', 'AND', 'age:>'=>18]`. this will produce in sql query : `key = 'val' AND age > 18`.

```php
 /**
 * @param string $table the tab name
 * @param array $columns columns to retrive
 * @param array|string $where where condition.
 * if array is given array must be key-value pair. 
 * - __value__
 * must accept only __int, float, string, null__.
 * - __key__ can accept some rules : ex: `['age:>'=> 18]`. that
 * will produce : `age > 18` in sql query.
 * - when the _where_ array contain an index key value some as
 * `['key'=>'val', 'AND', 'age:>'=>18]`. this will 
 * produce in sql query : `key = 'val' AND age > 18`.
 * @param array $values key value pair. _value_ automaticaly escape
 * special `SQL` characters when _value_ is a `string` type.
 * the supported values is `string|int|float|bool|array`.
 * _boolean_ is converted to integer 1|0, _array_ is converted to _string.json_.
 * @param string $condition contition that will be applie default : AND|OR
 * @param string $operator operator to use evrey where
 * in array map it replace `=>`.
 */
static function edit(
    string $table,
    array $values = [],
    array|string $where = [],
    string $condition = "AND",
    string $operator = "="
): bool
```

### delete

__where__ condition. if array is given array must be key-value pair. 

- __value__ must accept only __int, float, string, null__.
- __key__ can accept some rules : ex: `['age:>'=> 18]`. that will produce : `age > 18` in sql query.
- when the __where__ array contain an index key value some as `['key'=>'val', 'AND', 'age:>'=>18]`. this will produce in sql query : `key = 'val' AND age > 18`.

```php
/**
 * @param string $table the tab name
 * @param array $columns columns to retrive
 * @param array|string $where where condition.
 * if array is given array must be key-value pair. 
 * - __value__
 * must accept only __int, float, string, null__.
 * - __key__ can accept some rules : ex: `['age:>'=> 18]`. that
 * will produce : `age > 18` in sql query.
 * - when the _where_ array contain an index key value some as
 * `['key'=>'val', 'AND', 'age:>'=>18]`. this will 
 * produce in sql query : `key = 'val' AND age > 18`.
 * @param string $condition contition that will be applie default : AND|OR
 * @param string $operator operator to use evrey where
 * in array map it replace `=>`.
 */
static function delete(
    string $table,
    array|string $where = [],
    string $condition = "AND",
    string $operator = "="
): bool
```

### add

__values__ key value pair. _value_ automaticaly escape special `SQL` characters when __value__ is a `string` type. the supported values is `string|int|float|bool|array`.
__boolean__ is converted to integer 1|0, __array__ is converted to __string.json__.

```php
/**
 * add new row in aimed table and return inserted id.
 * @param string $table nema of table.
 * @param array $values key value pair. _value_ automaticaly escape
 * special `SQL` characters when _value_ is a `string` type.
 * the supported values is `string|int|float|bool|array`.
 * _boolean_ is converted to integer 1|0, _array_ is converted to _string.json_.
 * @return int|string|null if `null` is returned it to mean `$values` is 
 * maybe empty. `int` is the inserted index in _database::table_.
 * if `string` is returned is to mean the primary key in table is a
 * `string` type.
 */
static function add(string $table, array $values = []): int|string|null
```

### query

```php
/** 
 *executer une requette SQL standard
*@param query string SQL request
*@return mixed data if result is rowed or any if not rowed.
*/
static function query(string $query): mixed
```

### count

```php
/**
 * this method is an alis of `Database::get` method.
 * 
 * @return int count of num rows.
 */
static function count(
    string $table,
    array|string $where = [],
    string $condition = "AND",
    string $operator = "="
): int
```

### escape

```php
/** escape spacial characters in string 
 * 
 * __this method is unnecesary wathever you use `edit` or `add`
 * there `values` parameter implemented this behavior.__
 * 
 * and transform boolean value in integer 0|1.
 * if values is null or other specified type 
 * no chage will be maked.
 */
static function escape(string|Null|int|float|bool $value): string|Null|int|float
```
