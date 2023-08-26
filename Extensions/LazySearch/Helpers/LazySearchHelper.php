<?php

use Sharp\Classes\Web\Renderer;

function lazySearch(string $url)
{
    return Renderer::getInstance()->render("LazySearch", ["url"=>$url])->getContent();
}