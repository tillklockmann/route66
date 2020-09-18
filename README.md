# route66
A simple mvc app providing routing, controller, repository and view functionality.
## install with composer
```  
composer require tillklockmann\route66:dev-master
``` 

## usage
In order to make the app work, you need to have a Repository and a Controller class, that extend the Route66 abstract counterpart.
### 1. create Controller class
```php
// Controller.php

class Controller extends Route66\AbstractController
{
    public function index($request)
    {
        $this->view->template('index')->render();
    }

    public function about($request)
    {
        echo 'hello about';
    }
}

```
### 2. create Repository class
```php
// Repo.php
class Repo extends Route66\AbstractRepository
{
    
}
```
### 3. create index.php and setup app
```php
require 'vendor/autoload.php';
require 'Repo.php';
require 'Controller.php';

use Route66\App;

$app = new App(Controller::class, new Repo);

$app->get('/', 'index');
$app->get('/about', 'about');

$app->run();
```

Have fun :-)
