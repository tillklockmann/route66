<?php
namespace Route66;

use FastRoute;
use FastRoute\DataGenerator;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher\RegexBasedAbstract;
use FastRoute\DataGenerator\GroupCountBased as DataGeneratorGroupCountBased;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class App extends RegexBasedAbstract implements DataGenerator
{
    /** @var ParameterBag */
    protected $bag;

    /** @var DataGeneratorGroupCountBased */
    protected $dataGenerator;

    /** @var Std */
    protected $routeParser;

    /** @var View */
    protected $view;

    /** @var AbstractController */
    protected $controller;

    /** @var Repo */
    protected $repo;

    /** @var Request */
    protected $request_globals;

    public function __construct(AbstractController $controller, AbstractRepository $repo, string $view_folder)
    {
        if (empty($view_folder)) {
            $view_folder = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views';
        }
         $this->request_globals = Request::createFromGlobals();
         $this->uri = $this->request_globals->getPathInfo();
         $this->method = $this->request_globals->server->get('REQUEST_METHOD');
         $this->dataGenerator = new DataGeneratorGroupCountBased;
         $this->routeParser =  new Std;
         $this->controller = new $controller(
             new $repo, new View($view_folder)
         );
    }

    public function addRoute($httpMethod, $routeData, $handler) { 

    }

    public function getData() : array
    { 
        return $this->dataGenerator->getData();
    }

    public function get(string $route, string $handler)
    {
        $routeData = $this->routeParser->parse($route);
        $this->dataGenerator->addRoute('GET',$routeData[0], $handler);
    }

    public function post(string $route, string $handler)
    {
        $routeData = $this->routeParser->parse($route);
        $this->dataGenerator->addRoute('POST',$routeData[0], $handler);
    }
    
    public function run()
    {
        list($this->staticRouteMap, $this->variableRouteData) = $this->getData();
        $routeInfo = $this->dispatch($this->method, $this->uri);
        
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                echo 'Page not found.'; 
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                echo 'Method not allowed.'; 
                break;
            case FastRoute\Dispatcher::FOUND:
                $classMethod = $routeInfo[1];
                $bag = $this->setBag();
                $bag = $this->setRouteParamsOnBag($routeInfo[2]);
                // route action
                $this->controller->{$classMethod}($bag);
                break;
        }
    }

    protected function setBag() 
    {
        $this->bag = ($this->method == 'GET') ?
            $this->request_globals->query :
            // POST
            $this->request_globals->request;
    }

    protected function setRouteParamsOnBag(array $params) 
    {
        foreach ($params as $key => $value) {
            $this->bag->set($key, $value);
        }
    }

    protected function dispatchVariableRoute($routeData, $uri)
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            list($handler, $varNames) = $data['routeMap'][count($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}