<?php

namespace Bluefin\Renderer;

use Bluefin\App;
use Bluefin\View;

class JSONRenderer implements RendererInterface
{
    public function render(View $view)
    {
        App::getInstance()->response()
            ->setHeader("Content-Type", "application/json;charset=utf-8", true)
            ->setHeader("Cache-Control", "no-store", true);

        log_info("__AJAX__:",$view->getData());
        return json_encode($view->getData());
    }
}
