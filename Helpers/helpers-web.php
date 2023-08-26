<?php

use Sharp\Classes\Env\Session;
use Sharp\Classes\Extras\AssetServer;
use Sharp\Classes\Web\Renderer;

/**
 * Get a route path to the wanted asset (Automassets Component)
 * @param string $target End of the target file's path
 * @return string Fixed request path returned by Automassets
 * @example CSS `asset("/css/app.css")`
 * @example JS `asset("/lib/dist/chart.js")`
 */
function asset(string $target) : string
{
    return AssetServer::getInstance()->getURL($target);
}

function script(string $target, bool $inject=false)
{
    return $inject ?
        "<script>".file_get_contents(AssetServer::getInstance()->findAsset($target))."</script>":
        "<script src='".asset($target)."'></script>";
}

function style(string $target, bool $inject=false)
{
    return $inject ?
        "<style>".file_get_contents(AssetServer::getInstance()->findAsset($target))."</style>":
        "<link rel='stylesheet' href='".asset($target)."'>";
}

/**
 * Render another template and return its content
 * @param string $templateName End of the template's path
 * @param array $vars Context for the template
 * @example base `render("contact/creation", ["title"=>"New Contact"])`
 *          renders `.../contact/creation.php` and make `$title` available in your view
 */
function render(string $templateName, array $vars=[]) : string
{
    return Renderer::getInstance()->render($templateName, $vars)->getContent();
}

/**
 * Tell the renderer that we want to put current content into a greater template
 * @param string $templateName End of the template's path
 * @param array $vars Context for the template
 * @example base `template("commons/base", ["title"=>"New Contact"])`
 *          renders `.../commons/base.php` with current template sections and make `$title` available in it
 */
function template(string $templateName, array $context=[])
{
    Renderer::getInstance()->useTemplate($templateName, $context);
}

/**
 * Get a section content from the Renderer
 * @param string $sectionName Name of the section used in your template
 * @example base ```php
 *      <!-- common/base.php ... -->
 *      <?= section("title") ?>
 * ```
 * ```php
 *      <!-- another/template  -->
 *      <?= template("base") ?>
 *      <?= start("title") ?>
 *      My Title !
 * ```
 */
function section(string $sectionName)
{
    return Renderer::getInstance()->section($sectionName);
}

/**
 * Start filling a template Section
 * @param string $sectionName Name of the section used in your template
 * @example base ```php
 *      <!-- common/base.php ... -->
 *      <?= section("title") ?>
 *      <?= section("body") ?>
 * ```
 * ```php
 *      <!-- another/template  -->
 *      <?= template("base") ?>
 *      <?= start("title") ?>
 *      My Title !
 *      <?= start("body") ?>
 *      My Body !
 * ```
 */
function start(string $sectionName)
{
    Renderer::getInstance()->startSection($sectionName);
}

/**
 * Tell the renderer that we are done with the current section. (Called automatically by default)
 * @param string $sectionName Name of the section used in your template
 * @example base ```php
 *      <!-- common/base.php ... -->
 *      <?= section("title") ?>
 *      <?= section("body") ?>
 * ```
 * ```php
 *      <!-- another/template  -->
 *      <?= template("base") ?>
 *      <?= start("title") ?>
 *      My Title !
 *      <?= stop("title") ?>
 *      <?= start("body") ?>
 *      My Body !
 * ```
 */
function stop()
{
    Renderer::getInstance()->stopSection();
}
