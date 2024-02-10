<?php

declare(strict_types=1);

namespace Extra;

include_once __DIR__ . "/dbConfig.php";
$SQLDBCON = new \mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if ($SQLDBCON->error) die('echec de connection');

// ------------ [DATA BASE HANDLER] ----

$str = false; # if is set to true the api will print request query too.
trait Database
{
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
  ): null|array|int {
    global $SQLDBCON;
    global $str;
    //SELECT FROM WHERE
    $tab = strtolower($table);
    $imploded_cols = implode(', ', $columns);
    $imploded_where = '';
    $_where = "WHERE";
    if (is_array($where)) {
      $WHR = [];
      $PRCL = '';
      $OPERATOR = $operator;
      foreach ($where as $key => $val) {
        $PRCL = is_string($val) ? '"' : '';
        $val = is_null($val) ? 'NULL' : $val;

        if (stripos($key, ':') > 0) {
          $key_parts = explode(':', $key);
          $key = $key_parts[0];
          $OPERATOR = isset($key_parts[1]) ? $key_parts[1] : $OPERATOR;
        }
        if (is_int($key)) {
          $WHR[] = "%cond:$val";
        } else
          $WHR[] = $key . ' ' . $OPERATOR . " $PRCL" . $val . "$PRCL";
      }
      foreach ($WHR as $value) {
        if (strpos($value, '%cond:') !== false) {
          $imploded_where .= ' ' . str_replace('%cond:', '', $value, 1) . ' ';
        } else {
          $condition_ = '';
          if (strlen($imploded_where) > 0) $condition_ = ' ' . $condition . ' ';
          $imploded_where .= $condition_ . $value;
        }
      }
      // $imploded_where = implode(' ' . $condition . ' ', $WHR);
      if (count($where) == 0) $_where = '';
    } else $_where = ' WHERE ';
    $sqlSelect = 'SELECT ' . $imploded_cols . ' FROM `' . $tab . '` ' . $_where . ' ' . $imploded_where;
    if ($str) echo $sqlSelect;
    // echo $sqlSelect;
    $o = [];
    $results = $SQLDBCON->query($sqlSelect);

    // ? --- RETURNING --- ?
    if (isset($results->num_rows)) {
      if ($returnNumRows == true) return $results->num_rows;
      elseif ($results->num_rows > 0) {
        while ($row = $results->fetch_assoc()) {
          array_push($o, $row);
        }

        return $o;
      } else return null;
    } else return null;
  }

  /** 
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
  ): array|null|bool {
    $datas = Database::get(
      table: $table,
      columns: $columns,
      where: $where,
      condition: $condition,
      operator: $operator
    );

    if ($datas == null) return null;

    $counted = count($datas);
    if ($counted == 1) return $datas[0];
    elseif ($counted > 1) return false;

    return null;
  }

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
  ): bool {
    global $SQLDBCON;
    global $str;
    //UPDATE SET WHERE
    $tab = strtolower($table);
    $imploded_where = '';
    $_where = "WHERE";
    if (is_array($where)) {
      $WHR = [];
      $PRCL = '';
      $OPERATOR = $operator;
      foreach ($where as $key => $val) {
        $PRCL = is_string($val) ? '"' : '';
        if (stripos($key, ':') > 0) {
          $key_parts = explode(':', $key);
          $key = $key_parts[0];
          $OPERATOR = isset($key_parts[1]) ? $key_parts[1] : $OPERATOR;
        }
        if (is_int($key)) {
          $WHR[] = "%cond:$val";
        } else
          $WHR[] = $key . ' ' . $OPERATOR . " $PRCL" . $val . "$PRCL";
      }
      foreach ($WHR as $value) {
        if (strpos($value, '%cond:') !== false) {
          $imploded_where .= ' ' . str_replace('%cond:', '', $value, 1) . ' ';
        } else {
          // $imploded_where .= ' ' . $condition . ' ' . $value;
          $condition_ = '';
          if (strlen($imploded_where) > 0) $condition_ = ' ' . $condition . ' ';
          $imploded_where .= $condition_ . $value;
        }
      }
      // $imploded_where = implode(' ' . $condition . ' ', $WHR);
      if (count($where) == 0) $_where = '';
    }
    $_val = [];

    foreach ($values as $key => $val) {
      if (is_string($val)) $val = '"' . $SQLDBCON->real_escape_string($val) . '"';
      elseif (is_array($val)) {
        $val_ = json_encode($val);
        $val_ = $val_ !== false ? $val_ : '';
        $val = '"' . $SQLDBCON->real_escape_string($val_) . '"';
      } elseif (is_bool($val)) $val = $val === true ? 1 : 0;
      elseif (is_null($value)) $values__[] = 'NULL';
      elseif (is_int($val) || is_float($val)) null;
      else continue;

      $_val[] = '`' . $key . '` = ' . $val;
    }
    $imploded_vals = implode(', ', $_val);
    $sqlSelect = 'UPDATE ' . $tab . ' SET ' . $imploded_vals . ' ' . $_where . ' ' . $imploded_where;
    if ($str) echo $sqlSelect;
    // echo $sqlSelect;
    $status = $SQLDBCON->query($sqlSelect);
    return $status;
  }

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
  ): bool {
    //DELETE FROM WHERE
    global $SQLDBCON;
    global $str;
    $tab = strtolower($table);
    $imploded_where = '';
    $_where = "WHERE";
    if (is_array($where)) {
      $WHR = [];
      $PRCL = '';
      $OPERATOR = $operator;
      foreach ($where as $key => $val) {
        $PRCL = is_string($val) ? '"' : '';
        if (stripos($key, ':') > 0) {
          $key_parts = explode(':', $key);
          $key = $key_parts[0];
          $OPERATOR = isset($key_parts[1]) ? $key_parts[1] : $OPERATOR;
        }
        if (is_int($key)) {
          $WHR[] = "%cond:$val";
        } else
          $WHR[] = $key . ' ' . $OPERATOR . " $PRCL" . $val . "$PRCL";
      }
      foreach ($WHR as $value) {
        if (strpos($value, '%cond:') !== false) {
          $imploded_where .= ' ' . str_replace('%cond:', '', $value, 1) . ' ';
        } else {
          $condition_ = '';
          if (strlen($imploded_where) > 0) $condition_ = ' ' . $condition . ' ';
          $imploded_where .= $condition_ . $value;
        }
      }
      // $imploded_where = implode(' ' . $condition . ' ', $WHR);
      if (count($where) == 0) $_where = '';
    }
    $sqlSelect = 'DELETE FROM ' . $tab . ' ' . $_where . ' ' . $imploded_where;
    if ($str) echo $sqlSelect;
    // echo $sqlSelect;
    $o = [];
    return  $SQLDBCON->query($sqlSelect);
  }

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
  {
    //INSERT INTO () VALUES ()
    global $SQLDBCON;
    global $str;
    $tab = strtolower($table);
    $cols = [];
    $values_ = [];
    if (count($values) == 0) return NULL;
    foreach ($values as $key => $val) {
      array_push($cols, $key);
      array_push($values_, $val);
    }
    $imploded_cols = '`' . implode('`, `', $cols) . '`';
    $values__ = [];
    foreach ($values_ as $value) {
      if (is_string($value)) $values__[] = '"' . $SQLDBCON->real_escape_string($value) . '"';
      elseif (is_array($value)) {
        $value = json_encode($value);
        $value = $value !== false ? $value : '';
        $values__[] = '"' . $SQLDBCON->real_escape_string($value) . '"';
      } elseif (is_bool($value)) $values__[] = $value === true ? 1 : 0;
      elseif (is_null($value)) $values__[] = 'NULL';
      elseif (is_int($value) || is_float($value))
        $values__[] = $value;
    }
    $imploded_values = implode(', ', $values__);
    $sqlInsert = 'INSERT INTO `' . $tab . '` (' . $imploded_cols . ') VALUES (' . $imploded_values . ')';
    if ($str) echo $sqlInsert;
    // echo $sqlInsert;
    if ($SQLDBCON->query($sqlInsert) === TRUE) return $SQLDBCON->insert_id;
    else return 0;
  }

  /** 
   *executer une requette SQL standard
   *@param query string SQL request
   *@return mixed data if result is rowed or any if not rowed.
   */
  static function query(string $query): mixed
  {
    global $SQLDBCON;
    global $str;
    // echo $query;
    $results = $SQLDBCON->query($query);
    // if ($is_table) {
    if ($results->num_rows > 0) {
      $o = [];
      while ($row = $results->fetch_assoc()) {
        array_push($o, $row);
      }
      return $o;
    } else return $results;
    // } else {
    //     return $results;
    // }
  }

  /**
   * this method is an alis of `Database::get` method.
   * 
   * @return int count of nuw rows.
   */
  static function count(
    string $table,
    array|string $where = [],
    string $condition = "AND",
    string $operator = "="
  ): int {
    $db_result = Database::get(
      table: $table,
      where: $where,
      condition: $condition,
      operator: $operator,
      returnNumRows: true,
    );

    if (is_int($db_result)) return $db_result;
    else return 0;
  }

  /** escape spacial characters in string 
   * 
   * __this method is unnecesary wathever you use `edit` or `add`
   * there `values` parameter implemented this behavior.__
   * 
   * and transform boolean value in integer 0|1.
   * if values is null or other specified type 
   * no chage will be maked.
   */
  static function escape(
    string|Null|int|float|bool $value
  ): string|Null|int|float {
    global $SQLDBCON;
    if (is_string($value))
      return $SQLDBCON->real_escape_string($value);
    elseif (is_bool($value))
      if ($value == true) return 1;
      else return 0;
    else return $value;
  }
}

# ***************************************************************************************
