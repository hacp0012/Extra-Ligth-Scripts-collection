<?php
/* HTACCESS FILE CONTENT (.htaccess): 
copy this in a .htaccess file.

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_URI}  !(\.png|\.jpg|\.webp|\.gif|\.jpeg|\.zip|\.css|\.svg|\.js|\.pdf)$
# change only this file path
RewriteRule (.*) index.php [QSA,L]
*/

declare(strict_types=1);

namespace Extra;

include_once __DIR__ . '/message.php';

/** Routes controler
 * 
 * _Cette routeur fonction partout, il vous sufit just de bien
 * specifie le `$baseRoute` quand vous initialisez votre
 * Routeur._
 * ----
 * 
 * - path variable start with `$`. ex: `/user/$name` in 
 * router methods.
 */
class Router extends Route
{
  /** Router Constructor
   * 
   * @param string $baseRoute is the initial default base route.
   * _use yaur own but must only end with `/`_.
   * @param array $headers array list of header. `['header:params', ...]`.
   * if given be sure than _Router::construct_ is on top of
   * any other function calls.
   */
  function __construct(string $baseRoute = '/', array $headers = [])
  {
    # initiqlize headers
    foreach ($headers as $header) {
      header($header, true);
    }

    # init parent
    parent::__construct(baseRoute: $baseRoute);
  }

  /** Security source */
  function set_csrf()
  {
    session_start();
    if (!isset($_SESSION["csrf"])) $_SESSION["csrf"] = bin2hex(random_bytes(50));
    echo '<input type="hidden" name="csrf" value="' . $_SESSION["csrf"] . '">';
  }

  /** Security source */
  function is_csrf_valid()
  {
    session_start();
    if (!isset($_SESSION['csrf']) || !isset($_POST['csrf'])) return false;
    if ($_SESSION['csrf'] != $_POST['csrf']) return false;

    return true;
  }

  /** Accept GET only request method.
   * 
   * @param ?string $routeUrl can't start with '/'. if is `null` this 
   * method will be executed without routePath conditon.
   * @param string|callable $callable `function(Route, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   * 
   * if $callable is function, the function must return type `Message`
   * class.
   * @param array $queryParamRules default query parameters ruler. 
   * if parameter dont match rules an Error 500 API is returned
   * `PARAMETER_ERROR`. `[param:type:?]` the `?` is mean 0|1 or optionsl.
   */
  public function get(string $routeUrl, string|callable $callable, array $queryParamRules = []): _RouteControler
  {
    return new _RouteControler(
      method: 'GET',
      callable: $callable,
      paramRoute: $routeUrl,
      router: $this,
      queryParamRules: $queryParamRules,
    );
  }

  /** Accept POST only request method.
   * 
   * @param ?string $routeUrl can't start with '/'. if is `null` this 
   * method will be executed without routePath conditon.
   * @param string|callable $callable `function(Route, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   * 
   * if $callable is function, the function must return type `Message`
   * class.
   * @param array $queryParamRules default query parameters ruler. 
   * if parameter dont match rules an Error 500 API is returned
   * `PARAMETER_ERROR`. `[param:type:?]` the `?` is mean 0|1 or optionsl.
   */
  public function post(string $routeUrl, string|callable $callable, array $queryParamRules = []): _RouteControler
  {
    return new _RouteControler(
      method: 'POST',
      callable: $callable,
      paramRoute: $routeUrl,
      router: $this,
      queryParamRules: $queryParamRules,
    );
  }

  /** Accept only PUT only request method.
   * 
   * @param ?string $routeUrl can't start with '/'. if is `null` this 
   * method will be executed without routePath conditon.
   * @param string|callable $callable `function(Route, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   * 
   * if $callable is function, the function must return type `Message`
   * class.
   * @param array $queryParamRules default query parameters ruler. 
   * if parameter dont match rules an Error 500 API is returned
   * `PARAMETER_ERROR`. `[param:type:?]` the `?` is mean 0|1 or optionsl.
   */
  public function put(string $routeUrl, string|callable $callable, array $queryParamRules = []): _RouteControler
  {
    return new _RouteControler(
      method: 'PUT',
      callable: $callable,
      paramRoute: $routeUrl,
      router: $this,
      queryParamRules: $queryParamRules,
    );
  }

  /** Accept ANY request method.
   * 
   * @param ?string $routeUrl can't start with '/'. if is `null` this 
   * method will be executed without routePath conditon.
   * @param string|callable $callable `function(Route, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   * 
   * if $callable is function, the function must return type `Message`
   * class.
   * @param array $queryParamRules default query parameters ruler. 
   * if parameter dont match rules an Error 500 API is returned
   * `PARAMETER_ERROR`. `[param:type:?]` the `?` is mean 0|1 or optionsl.
   */
  public function any(string|callable $callable, ?string $routeUrl = null, array $queryParamRules = []): _RouteControler
  {
    return new _RouteControler(
      router: $this,
      method: 'ANY',
      paramRoute: is_null($routeUrl) ? $this->route : $routeUrl,
      queryParamRules: $queryParamRules,
      callable: $callable,
    );
  }

  /** organize yaur routes with a groute list.
   * 
   * group is not run if is in other groupe.
   * @param string $groupRoute the group route path.
   * @param array $routeControlers a list of `_RouteControler`.
   * ex: `[$router.get('/', function():Message {}), ...]`
   */
  public function group(string $groupRoute, array $routeControlers): _RouteControler
  {
    foreach ($routeControlers as $routeFunction) {
      $routeFunction->route = $groupRoute . '/' . ltrim($routeFunction->route, '/');

      # reload parameters
      $routeFunction->loadUrlParsing();

      # output
      $routeFunction->end();
    }

    return new _RouteControler(
      method: 'BATCH',
      callable: null,
      paramRoute: $groupRoute,
      queryParamRules: [],
      router: $this
    );
  }
}

class _RouteControler
{
  /** the request method function type. `ANY|GET|POST|PUT` */
  public $callableMethod        = 'ANY';
  public $parameters            = array();
  /** @var string $url  */
  public $route                 = '';
  /** 
   * @var string|callable $callable `function(Router, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   */
  public $callable              = '';
  /** @var $interceptor `function(Router, Message):void` */
  public $interceptor           = null;
  /** @var $message Message class */
  public ?Message $message      = null;

  private Router $router_;
  private $queryParamRules = [];

  /** 
   * @param string|callable $callable `function(Router, params...):Message` 
   * 
   * if is string callable must  point to a faile. if no file extention 
   * is given the `.php` extention will be added.
   */
  function __construct(string $method, string|callable|null  $callable, string $paramRoute, array $queryParamRules = [], Router $router)
  {
    $this->callableMethod = $method;
    $this->callable = $callable;
    $this->router_  = $router;
    $this->route    = $paramRoute;
    $this->queryParamRules = $queryParamRules;

    # url parser
    $this->loadUrlParsing();
    // $route_parts = explode('/', $paramRoute);
    // $route_parts = explode('/', $this->route);
    // $request_url_parts = explode('/', trim($router->route, '/'));

    // for ($__i__ = 0; $__i__ < count($route_parts); $__i__++) {
    //   if (preg_match("/^[$]/", $route_parts[$__i__])) {
    //     echo $this->route . PHP_EOL;
    //     echo $router->route . PHP_EOL;
    //     if (isset($request_url_parts[$__i__]))
    //       $this->parameters[] = $request_url_parts[$__i__];
    //   }
    // }
  }

  # url parser
  public function loadUrlParsing(): void
  {
    // $route_parts = explode('/', $paramRoute);
    $route_parts = explode('/', $this->route);
    $request_url_parts = explode('/', trim($this->router_->route, '/'));

    $this->parameters = [];

    for ($index = 0; $index < count($route_parts); $index++) {
      if (preg_match("/^[$]/", trim($route_parts[$index])) == 1) {
        // echo $route_parts[$index] . PHP_EOL;
        // echo $request_url_parts[$index] . PHP_EOL;
        if (isset($request_url_parts[$index])) {
          $this->parameters[] = $request_url_parts[$index];
        }
      }
    }
  }

  private function queryParamRules(array $rules): void
  {
    $status = 0;
    $keyName = '';
    $typeName = '';

    foreach ($rules as $value) {
      $rules_parts = explode(':', $value);
      if (count($rules_parts) && $status == 0) {
        $key    = $rules_parts[0];
        $type   = isset($rules_parts[1]) ? $rules_parts[1] : 'mixed';
        $state  = isset($rules_parts[2]) ? $rules_parts[2] : null;

        $keyName = $key;
        $typeName = $type;

        # check key exist
        if (!isset($this->router_->query[$key])) {
          $status = 1;
          break;
        }

        # check type
        $var = $this->router_->query[$key];
        switch ($type) {
          case 'int':
            if (is_int($var) == false && $state != '?') {
              $status = 2; # type error
            }
            break;
          case 'double':
            if (is_double($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          case 'string':
            if (is_string($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          case 'null':
            if (is_null($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          case 'bool':
            if (is_bool($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          case 'array':
            if (is_array($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          case 'mixed':
            if (is_array($var) == false && $state != '?') {
              $status = 2;
            }
            break;
          default:
            $keyName = $key;
            $status = 3; # unknow type
            $typeName = $type;
        }
      }
    }

    if ($status > 0) {
      $content = '';
      $status_message = '';

      switch ($status) {
        case 1:
          $content = "Parameter : $keyName is unexisted.";
          $status_message = 'UNEXISTED';
          break;
        case 2:
          $content = "Value of parameter $keyName must be of type : $typeName.";
          $status_message = 'TYPE_ERROR';
          break;
        case 3:
          $content = "Unrecognized type : $typeName of parameter $keyName.";
          $status_message = 'UNRECOGNIZED';
          break;
      }

      (new Message(
        httpStatus: HttpStatus::ERROR,
      ))
        ->api(
          level: 5001,
          status_text: $status_message,
          message: $content,
        )
        ->pnd();
    }
  }

  /** execut callable.
   * 
   * can't execut when request method is diferent excepte ANY.
   */
  public function end(): void
  {
    # don't make end when request method is Batch.
    if ($this->callableMethod == 'BATCH') return;

    # check if url match before continue.
    if ($this->isThis() == false) return;

    # check whether request mothod match with fn method.
    if (
      $this->router_->method != $this->callableMethod
      && $this->callableMethod != 'ANY'
    ) return;

    # parse query rulers befaut or stop when fail.
    $this->queryParamRules($this->queryParamRules);
    // * is callback *
    if (is_callable($this->callable)) {
      try {
        $message = call_user_func_array(
          $this->callable,
          [$this->router_, ...$this->parameters]
        );
        if (is_a($message, '\Extra\Message') == false) {
          throw new \Exception("The callback function must be of type 'Message'.", 1);
        }
        $message->pnd();
      } catch (\Exception $e) {
        throw $e;
      }
      // * is file *
    } elseif (!is_null($this->callable)) {
      if (!strpos($this->callable, '.php')) {
        $this->callable .= '.php';
      }
      // ! EXECUTION DU SCRIPT.
      {
        include_once $this->callable;
      }
    }
  }

  /** check wether the request function if for this action */
  public function isThis(): bool
  {
    $parentRoute = explode('/', trim($this->router_->route, '/'));
    $chilRoute = explode('/', trim($this->route, '/'));

    if (count($parentRoute) != count($chilRoute)) {
      return false;
    }

    $index = 0;
    foreach ($chilRoute as $element) {
      if (preg_match("/^[$]/", $element)) {
        $index++;
        continue;
      } elseif ($element != $parentRoute[$index]) {
        return false;
      }

      $index++;
    }
    return true;
  }
}

class Route
{
  # PROPERTIES
  /** @param string $route base of this basepath. */
  public $route         = '/';
  public $url           = '';
  public $method        = '';
  public $query         = array();

  # METHODS
  function __construct(string $baseRoute = '/')
  {
    $this->query = $_REQUEST;
    $this->method = $_SERVER['REQUEST_METHOD'];

    # parse route
    $request_url  = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $request_url  = rtrim($request_url, '/');
    $request_url  = is_string(strtok($request_url, '?')) ? strtok($request_url, '?') : $request_url;
    $this->url    = $request_url;

    $s = 1;
    $this->route = str_replace(
      search: $baseRoute,
      replace: '/',
      subject: $request_url,
      count: $s,
    );
  }

  /** return POSTed json data as Array. */
  function getJsonPost(): ?array
  {
    $text_data = file_get_contents('php://input');
    if (strlen($text_data) == 0) return null;

    $data = json_decode(json: $text_data, associative: true);

    if (json_last_error_msg() != 'No error') {
      return null;
    } else
      return $data;
  }

  /**
   * save file 
   * 
   * @param String $form_name Theme of file in form
   * @param Array $mimes Them mimes types of that the file can be
   * @param String $destination destination path where to store file
   * @param ?String $custom_name The custom name that file will have if specified
   */
  function saveFile(string $form_name, array $mimes, string $destination, ?string $custom_name = null): ?string
  {
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
}

/*
CONCEPTION MODEL:

$router = new Router(route: '/v1/modules/users/');

$router.batch('/main', [
  router.get('url/a'),
  router.post('url/b'),
])
$Router.batch('url/c', [
  router.get('c/a')
]);

router.get(
  '/url/$a/$b', 
  string|function(Route, $param1, $param2): Message callback,
  $defaultQueris = ['name:int:?'],
  function(Route, Message): Router interceptor,
): _RouteControler;
*/