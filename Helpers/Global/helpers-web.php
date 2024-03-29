<?php

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

/**
 * Get a script tag for your view
 *
 * @param string $target Asset name used for `AssetServer->getURL()`
 * @param bool $inject If `true`, returned string contains the file content directly,
 *             if false, the attribute src will contain an URL to the file
 * @return string `script` tag with the script linked to it or inside directly (see the `$inject` parameter)
 * @see \Sharp\Classes\Extras\AssetServer::getUrl()
 */
function script(string $target, bool $inject=false): string
{
    if (!$inject)
        return "<script src='".asset($target)."'></script>";

    if (!($path = AssetServer::getInstance()->findAsset($target)))
        throw new Exception("Script not found [$target]");

    return "<script>".file_get_contents($path)."</script>";
}

/**
 * Get a script tag for your view
 *
 * @param string $target Asset name used for `AssetServer->getURL()`
 * @param bool $inject If `true`, returned string contains the file content directly,
 *             if false, the attribute href will contain an URL to the file
 * @return string `link` tag with the stylesheet linked to it,
 *                or a `style` tag with the stylesheet inside directly injected (see the `$inject` parameter)
 * @see \Sharp\Classes\Extras\AssetServer::getUrl()
 */
function style(string $target, bool $inject=false): string
{
    if (!$inject)
        return "<link rel='stylesheet' href='".asset($target)."'>";

    if (!($path = AssetServer::getInstance()->findAsset($target)))
        throw new Exception("Stylesheet not found [$target]");

    return "<style>".file_get_contents($path)."</style>";
}

/**
 * Render another template and return its content
 * @param string $templateName End of the template's path
 * @param array $vars Context for the template
 * @example base `render("contact/creation", ["title"=>"New Contact"])`
 *          renders `.../contact/creation.php` and make `$title` available in your view
 * @return string Rendered view as string
 */
function render(string $templateName, array $vars=[]) : string
{
    return Renderer::getInstance()->render($templateName, $vars);
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
function section(string $sectionName): ?string
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
function start(string $sectionName): void
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
 * <!-- another/template  -->
 * <?= template("base") ?>
 *      <?= start("title") ?>
 *      My Title !
 *      <?= stop("title") ?>
 *      <?= start("body") ?>
 *      My Body !
 * ```
 */
function stop(): void
{
    Renderer::getInstance()->stopSection();
}
