<?php

class Router {
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function post(string $path, string $controller, string $method): void {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch(string $uri, string $httpMethod): void {
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';

        if (isset($this->routes[$httpMethod][$uri])) {
            $route = $this->routes[$httpMethod][$uri];
            $controllerName = $route['controller'];
            $methodName     = $route['method'];

            require_once BASE_PATH . '/app/Controllers/' . $controllerName . '.php';
            $controller = new $controllerName();
            $controller->$methodName();
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
