<?php

namespace Extra; # ----------------------------------------------------------------------

function headers()
{
  // ? THIS HEADERS ARE NECESSARY TO ACCESS EXTERNAL ORIGIN IN APP
  header('Access-Control-Allow-Headers: access-control-allow-origin, x-requested-with, access-control-allow-headers, content-type', true);
  header('Access-Control-Allow-Methods: POST, GET', true); // PUT, OPTION, DELETE, HEAD, GET, POST
  header('X-requested-with: XMLHttpRequest', true);
  // ! TO CUSTOMIZ ORIGIN VIA APPLICATION
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_ALLOW_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ACCESS_CONTROL_ALLOW_ORIGIN'], true);
  } else {
    header('Access-Control-Allow-Origin: *', true); // ? IT CAN BE http://loaclhost
  }

  # INIT CONNEXTION VIA HEADER
  /* print_r($_SERVER);
  if (isset($_SERVER['HTTP_X_CUSTOMER_ID'])) {
    if (
      $_SERVER['HTTP_X_CUSTOMER_ID'] !== 'null' &&
      $_SERVER['HTTP_X_CUSTOMER_ID'] !== null) {
      // * SET TO GLOABAL CONSANT
      define("CID", $_SERVER['HTTP_X_CUSTOMER_ID']);
    }
  } */
}

/**
 * upload file 
 * 
 * @param String $form_name Theme of file in form
 * @param Array $mimes Them mimes types of that the file can be
 * @param String $destination destination path where to store file
 * @param ?String $custom_name The custom name that file will have if specified
 */
function uploadFile(
  string $form_name,
  array $mimes,
  string $destination,
  ?string $custom_name = null
): ?string {
  $file = $_FILES[$form_name];
  if (in_array($file['type'], $mimes)) {
    if (is_dir($destination)) {
      $name = $file['name'];
      if ($custom_name != null) {
        $name = $custom_name;
      }

      $file_path = $destination . '/' . $name;

      $state = move_uploaded_file($_FILES[$form_name]['tmp_name'], $file_path);
      if ($state) return $file_path;
      else return null;
    } else return null;
  } else return null;
}

/** 
 * RANDOM VALUE 
 * @param int $len la longeur max qu'aura la chaine de retour.
 * cette valeur ne doit pas depasser la valeur par de $mixWith.
 * s'il est superieur ou inferieur il est reinitializer au valeur equivalant de $mixWith.
 * @param string $content la valeur par defaut est "onlynumber".
 * - onlynumber : 0-9
 * - uppercase : 36 (onlynumber + 26)
 * - lowercase : 62 (with_uppercase + 26)
 * @param bool $notUnique par defaut cette valeur fait que la fonction retourne que de caracteres 
 * no double ou requirent, chaque caractere est unique. si il est TRUE alors la valeur de retour
 * peut contenir des caracteres repliquer.
 * @return string of rendom values
 */
function randomCode(int $len = 7, string $mixWith = "onlynumber", bool $notUnique = FALSE): string
{
  $nb_a_tirer = abs($len) - 1;
  $val_min = 0;
  $vals = [
    "numeric" => 9,
    "alpha_uppercase" => 9 + 26,
    "alpha_lowercase" => 9 + 26 + 26
  ];

  $val_max = $vals["alpha_uppercase"];
  if ($mixWith == "uppercase") {
    $val_max = $vals["alpha_uppercase"];
    if ($len > (9 + 26)) $len = 9 + 26;
  } elseif ($mixWith == "lowercase") {
    $val_max = $vals["alpha_lowercase"];
    if ($len > (9 + 26 + 26)) $len = 9 + 26 + 26;
  } else {
    $val_max = $vals["numeric"];
    if ($len > 9) $len = 9;
  }

  $tab_result = array();
  $alpha = [
    0 => 0,
    1 => 1,
    2 => 2,
    3 => 3,
    4 => 4,
    5 => 5,
    6 => 6,
    7 => 7,
    8 => 8,
    9 => 9,
    10 => "A", // uppercase
    11 => "B",
    12 => "C",
    13 => "D",
    14 => "E",
    15 => "F",
    16 => "G",
    17 => "H",
    18 => "I",
    19 => "J",
    20 => "K",
    21 => "L",
    22 => "M",
    23 => "N",
    24 => "O",
    25 => "P",
    26 => "Q",
    27 => "R",
    28 => "S",
    29 => "T",
    30 => "U",
    31 => "V",
    32 => "W",
    33 => "X",
    34 => "Y",
    35 => "Z",
    36 => "a", // lowercase
    37 => "b",
    38 => "c",
    39 => "d",
    40 => "e",
    41 => "f",
    42 => "g",
    43 => "h",
    44 => "i",
    45 => "j",
    46 => "k",
    47 => "l",
    48 => "m",
    49 => "n",
    50 => "o",
    51 => "p",
    52 => "q",
    53 => "r",
    54 => "s",
    55 => "t",
    56 => "u",
    57 => "v",
    58 => "w",
    59 => "x",
    60 => "y",
    61 => "z",
  ];

  while ($nb_a_tirer >= 0) {
    $nombre = mt_rand($val_min, $val_max);
    $valeur = $alpha[$nombre];
    if ($notUnique || !in_array($valeur, $tab_result)) {
      $tab_result[] = $valeur;
      $nb_a_tirer--;
    }
  }

  return implode($tab_result);
}

namespace Extra\Api; # ------------------------------------------------------------------
/**
 * Control Params if is set
 * @param array $param array that contain params object
 * @param array $haystack the params rules.
 * suported type  : string, number, object.
 * - optional state : ?
 * - syntax : ['param:type', 'param:type:?' ...]
 * @return mixed TRUE or String
 * TRUE : if all is OK
 * String : if param type error
 */

function paraset(array $params, array $haystack)
{
  $haystack_count = 0;
  foreach ($haystack as $value) {
    $splited = explode(":", $value);
    $isOptional = isset($splited[2]) && $splited[2] == "?" ? TRUE : FALSE;
    if ($isOptional) continue;
    $haystack_count++;
  }

  if (count($params) >= $haystack_count) {
    foreach ($haystack as $key => $value) {
      $splited = explode(":", $value);
      if (count($splited) == 2 || count($splited) == 3) {
        $value    = $splited[0];
        $isOptional = isset($splited[2]) && $splited[2] == "?" ? TRUE : FALSE;
        if (isset($params[$value]) == FALSE && $isOptional) continue;
        switch ($splited[1]) {
          case 'string':
            if (isset($params[$value]) && is_string($params[$value])  == false) return "Le parametre '$value' doit etre de type 'string'";
            break;
          case 'object':
            if (isset($params[$value]) && is_array($params[$value])   == false) return "Le parametre '$value' doit etre de type 'object'";
            break;
          case 'number':
            if (isset($params[$value]) && is_numeric($params[$value]) == false) return "Le parametre '$value' doit etre de type 'number'";
            break;

          default:
            return "The type '" . $splited[1] . "' is not a suported TYPE. only [number, string, object] are suported";
            break;
        }
      }

      $haystacked = [];
      foreach ($params as $key_ => $val) {
        array_push($haystacked, $key_);
      }
      // print_r($haystacked);

      if (in_array($splited[0], $haystacked) == false) {
        return "La param " . $splited[0] . " n'est pas reconue"; // ? no matched parameter
      }
    }
    return true; // ? all is OK
  } else return "Params insufisant or missed"; // ? unsufisent params
}

/**
 * outie pour les parametre par defaut en object
 * il permet remplire la valeurs donnee et de completer les valeur par defaut.
 * @param dafault array recoie l'objet des valeur par dafaut
 * Ex: ["name"=>"Chris", "skill"=>"code"]
 * @param parameter array recoie l'objet des valeur entrant
 * Ex: ["name"=>"Charles"]
 * @return array retourn l'objet de valeurs completer.
 * Ex: ["name"=>"Charles", "skill"=>"code"]
 */
function defaultParameter(array $default, array $parameter): array
{
  foreach ($default as $key => $value) {
    if (isset($parameter[$key])) $default[$key] = $parameter[$key];
  }
  return $default;
}

namespace Extra\Response; # -------------------------------------------------------------

# --> { content-type, status, reponse, message}
/**
 * print to outPut (print on browser)
 * @param status string|int can be one of belows
 * - 200 OK | NO
 * - 201 Unauthorized
 * - 303 Bad request
 * - 404 Not Found
 * - 500 Internal Server Error | NO
 * @param data mixed can be array or text
 * @param content string specify type of content $data are and sant "content-type" based on it.
 * dafaults valeus are " json | text | html
 */
function message($status = "OK", $data, string $content = 'json'): void
{
  if (is_string($status)) http_response_code(200);
  else http_response_code($status);

  $content_ = 'text/text';
  switch ($content) {
    case 'text':
      $content_ = 'text/plain';
      break;
    case 'json':
      $content_ = 'application/json';
      break;
    case 'html':
      $content_ = 'text/html';
      break;

    default:
      $content_ = 'application/json';
      break;
  }
  // header('HTTP/1.1 ');
  header('Content-Type: ' . $content_, true);

  // response
  echo json_encode(["status" => $status, "data" => $data]);
}

function error($status = 404, $response = [], $content = 'json')
{
  message($status, $response, $content);
}
