# ROUTER

__Light http request router.__

Before all you must create a `.htaccess` file in your root forlder.

```htaccess
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_URI}  !(\.png|\.jpg|\.webp|\.gif|\.jpeg|\.zip|\.css|\.svg|\.js|\.pdf)$
# change only this file path
RewriteRule (.*) index.php [QSA,L]
```

__Your index.php initialize `Router`__

doc content

[Router](#router)

[Route](#route)

[Message](#message)


## ROUTER

### Routes controle

__Cette routeur fonction partout, il vous sufit just de bien specifie le `$baseRoute` quand vous initialisez votre Routeur.__

- path variable start with `$`. ex: `/user/$name` in router methods.

```php
# Main class
class Router extends Route
```

### Usage (initialize)

```php
$router = new Router(route: '/v1/modules/users/');

$router->batch('/main', [
  router->get('url/a'),
  router->post('url/b'),
])
$Router->batch('url/c', [
  router->get('c/a')
]);

router->get(
  '/url/$a/$b', 
  string|function(Route, $param1, $param2): Message callback,
  $defaultQueris = ['name:int:?'],
  function(Route, Message): Router interceptor,
): _RouteControler;
```

### Constructor

```php
/** Router Constructor
   * 
   * @param string $baseRoute is the initial default base route.
   * _use yaur own but must only end with `/`_.
   * @param array $headers array list of header. `['header:params', ...]`.
   * if given be sure than _Router::construct_ is on top of
   * any other function calls.
   */
  function __construct(string $baseRoute = '/', array $headers = [])
```

### set_csrf

```php
/** Security source */
  function set_csrf()
```

### is_csrf_valid

```php
  /** Security source */
  function is_csrf_valid()
```

### get

```php
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
```

### post

```php
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
```

### put

```php
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
```

### any

```php
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
```

### group

```php
  /** organize yaur routes with a groute list.
   * 
   * group is not run if is in other groupe.
   * @param string $groupRoute the group route path.
   * @param array $routeControlers a list of `_RouteControler`.
   * ex: `[$router.get('/', function():Message {}), ...]`
   */
  public function group(string $groupRoute, array $routeControlers): _RouteControler
```

## ROUTE

route handler

### PROPERTIES

```php
/** @param string $route base of this basepath. */
public $route         = '/';
public $url           = '';
public $method        = '';
public $query         = array();
```

### Constructor

```php
function __construct(string $baseRoute = '/')
```

### getJsonPost

```php
  /** return POSTed json data as Array. */
  function getJsonPost(): ?array
```

### saveFile

```php
  /**
   * save file 
   * 
   * @param String $form_name Theme of file in form
   * @param Array $mimes Them mimes types of that the file can be
   * @param String $destination destination path where to store file
   * @param ?String $custom_name The custom name that file will have if specified
   */
  function saveFile(string $form_name, array $mimes, string $destination, ?string $custom_name = null): ?string
```

## MESSAGE

Message handler

### Constructor

```php
  function __construct(
    mixed $content = null,
    MessageType $messageType = MessageType::isAPI,

    int|HttpStatus $httpStatus = HttpStatus::OK,
    int $status = 0,
    string $status_message = null,
    string $status_text = 'OK',
  )
```

### api

```php
  /** set some API params. */
  public function api(
    int $level,
    ?string $message = null,
    ?string $status_text = null
  )
```

### status

```php
  /** set http status. */
  public function status(int|HttpStatus $level = HttpStatus::OK): Message
```

### pnd

```php
  /** Print and Die.
   * 
   * if `message` is provided only it is printed.
   */
  public function pnd(mixed $message = null): void
```

### print

```php
  /** Print message
   * 
   * if `message` is provided only it is printed.
   */
  public function print(mixed $message = null): Message
```

### MessageType

```php
/** `Message` response type. */
enum MessageType
{
  isJSON;
  isPLAIN;
  isHTML;
  isAPI;
}
```

### HttpStatus

```php
/** `HttpStatus` response type. */
class HttpStatus
{
  NOT_FOUND = 404;
  ERROR = 500;
  OK = 200;
}
```
