# route66
A simple router.
## install with composer
```  
composer require tillklockmann\route66:1.0
``` 

## usage
In order to make the app work, you need to have a Repository and a Controller class, that extend the Route66 abstract counterpart. 

```php
require 'vendor/autoload.php';

use Route66\Router;

$container = new Container;

$container['controller'] = function($c) {
    return new MyOwnController;
}

$app = new Router($container);

$app->get('/', 'controller@index');
$app->get('/about', 'controller@about');

$app->run();
```

Have fun :-)
