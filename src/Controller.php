<?php

namespace Nskdmitry\Cutlink;

class Controller {
    public function __construct(
        protected array $data,
        public string $responseType = App::RESPONSE_TYPE_DEFAULT,
        protected array $defaults = []
    ) {
        $this->data = array_merge($this->defaults, $this->data);
    }

    public function index() {
        $model = new Model();
        $links = $model->all();
        return $this->render('index', ['links' => $links]);
    }

    public function relocate(array $query): string|null {
        $url = Model::getShortUrl($query['short']);
        $link = Model::findBy("short", "'$url'");
        if (!$link) {
            return null;
        }
        http_response_code(308);
        header("Location: {$link->full}");
        return html_entity_decode($link->full);
    }

    public function post(array $any): string {
        $this->responseType = App::RESPONSE_TYPE_JSON;
        $url = $this->data['link'];
        $model = new Model();
        $number = $model->getCount() + 1;
        $short = Model::makeShort($number);
        $model->full = $url;
        $model->short = $short;
        $model->save();
        return $short;
    }

    public function error404(array $errorDefinition): string {
        return $this->render('404', $errorDefinition);
    }

    protected function render(string $viewName, array $variables = []) {
        extract($variables);
        $templateFilePath = "{$_SERVER['DOCUMENT_ROOT']}/src/view/{$viewName}.phtml";
        require_once($templateFilePath);
        return "";
    }
}