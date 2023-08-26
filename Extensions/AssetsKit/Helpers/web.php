<?php

use Sharp\Classes\Extras\AssetServer;
use Sharp\Extensions\AssetsKit\Components\Svg;

const ASSETS_KIT_BUNDLE_SCRIPTS = [

    "bridge.js",
    "lang.js",
    "date.js",

    "eventSource.js",
    "fetch.js",
    "svg.js",

    "aside.js",
    "menu.js",
    "overlay.js",
    "component.js",
    "animation.js",

    "highstate.js",
    "validate.js",
    "notify.js",
    "autocomplete.js",

    "nav.js"
];

function svg(string $name, int $size=null)
{
    return Svg::getInstance()->get($name, $size);
}

function assetsKitJSBundle(bool $inject=false)
{
    $str = "";
    foreach (ASSETS_KIT_BUNDLE_SCRIPTS as $s)
    {
        $str .= $inject ?
            "<script>".file_get_contents(AssetServer::getInstance()->findAsset($s))."</script>":
            "<script src='". AssetServer::getInstance()->getURL($s) ."'></script>"
        ;

    }
    return $str;
}