<?php
namespace Route66;

use FastRoute;
use FastRoute\DataGenerator;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher\RegexBasedAbstract;
use FastRoute\DataGenerator\GroupCountBased as DataGeneratorGroupCountBased;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Psr\Container\ContainerInterface;

class Router extends RegexBasedAbstract implements DataGenerator
{
    /** @var ParameterBag */
    protected $bag;

    /** @var DataGeneratorGroupCountBased */
    protected $dataGenerator;

    /** @var Std */
    protected $routeParser;

    /** @var Request */
    protected $request_globals;

    /** @var ContainerInterface */
    protected $dic;

    public function __construct(ContainerInterface $dic)
    {
        $this->dic = $dic;
        $this->request_globals = Request::createFromGlobals();
        $this->uri = $this->request_globals->getPathInfo();
        $this->method = $this->request_globals->server->get('REQUEST_METHOD');
        $this->dataGenerator = new DataGeneratorGroupCountBased;
        $this->routeParser =  new Std;
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
                $this->dispatch($routeInfo);
                break;
        }
    }


    /**
     * dispatch registered controller action
     * @param array $routeInfo 
     * @return void 
     * 
     * @todo throw exception, if controller or action do not exist
     */
    protected function dispatch(array $routeInfo)
    {
        $handler = explode('@', $routeInfo[1]);
        $class_dic_id = $handler[0];
        
        $controllerClass = $this->dic->get($class_dic_id);
        $classMethod = $handler[1];
        $bag = $this->setBag();
        $bag = $this->setRouteParamsOnBag($routeInfo[2]);
        // route action
        $controllerClass->{$classMethod}($bag);
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