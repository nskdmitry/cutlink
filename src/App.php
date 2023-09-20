<?php

namespace Nskdmitry\Cutlink;

class App {
    const RESPONSE_TYPE_DEFAULT = 'text/html';
    const RESPONSE_TYPE_HTML = 'text/html';
    const RESPONSE_TYPE_JSON = 'application/json';

    public function __construct(
        protected array $router = [],
        protected array $request = [],
        protected string $uri = "",
        protected string $method = 'GET',
    ) {
        $this->router = require_once(__DIR__.'/config/router.php');
        $this->request = array_merge(
            $this->request, 
            filter_input_array(INPUT_GET) ?? [], 
            filter_input_array(INPUT_POST) ?? []
        );
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = trim($_SERVER['REQUEST_URI'], ' /');
    }

    public function run() {
        $variants = $this->router[$this->method];
        foreach ($variants as $pattern => $method) {
            if ($this->matched($pattern)) {
                $this->call($method, $this->getURIArgs($pattern));
                return;
            }
        }
        $this->call([Controller::class, 'error404'], ['uri' => $this->uri]);
    }

    protected function matched(string $pattern): bool {
        $uriParts = explode('/', $this->uri);
        $uriParts = array_diff($uriParts, [null, '']);
        $patternParts = explode('/', $pattern);
        $patternParts = array_diff($patternParts, [null, '']);
        return \count($uriParts) == \count($patternParts);
    }

    protected function getURIArgs(string $pattern): array {
        $uriParts = explode('/', $this->uri);
        $patternParts = explode('/', $pattern);
        $properties = [];
        foreach ($patternParts as $i => $particion) {
            $name = $this->getArgName($particion);
            if (!$name) {
                continue;
            }
            $properties[$name] = $uriParts[$i] ?? $uriParts[$i-1];
        }
        return $properties;
    }

    protected function call(array $define, array $uriArgs) {
        $class = $define[0];
        $method = $define[1];

        $controller = new $class($this->request);
        $result = $controller->$method($uriArgs);

        if ($controller->responseType == static::RESPONSE_TYPE_JSON) {
            header("Content-type: {$controller->responseType}");
            $result = \json_encode($result);
        }
        echo $result;
    }

    protected function getArgName(string $pattern): string {
        $match = [];
        \mb_ereg("{(\w+)}", $pattern, $match);
        if (\count($match) == 0) {
            return "";
        }
        return $match[1];
    }
}