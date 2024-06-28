<?php

namespace Sharp\Classes\Web;

use Exception;
use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Cache;
use Sharp\Classes\Events\AfterViewRender;
use Sharp\Classes\Events\BeforeViewRender;
use Sharp\Classes\Web\Classes\RenderShard;
use Sharp\Core\Autoloader;
use Throwable;

class Renderer
{
    use Component, Configurable;

    protected array $pathCache = [];

    protected array $shards = [];
    protected ?RenderShard $current = null;
    protected array $sections = [];

    public static function getDefaultConfiguration() : array
    {
        return [
            'cached' => false,
            'file_extension' => '.php'
        ];
    }

    public function __construct()
    {
        $this->loadConfiguration();

        if (!$this->isCached())
            return;

        $this->pathCache = &Cache::getInstance()->getReference("sharp.renderer.path-cache");
    }

    /**
     * Try to find a template file in Views directory
     * @param string $templateName template to look for
     * @return string|false Template's absolute path or false if not found
     */
    public function findTemplate(string $template): string|false
    {
        if ($cached = $this->pathCache[$template] ?? false)
            return $cached;

        $ext = $this->configuration['file_extension'];

        if (!str_ends_with($template, $ext))
            $template .= $ext;

        if (!str_starts_with($template, "/"))
            $template = "/$template";

        if (file_exists($template))
            return $template;

        foreach (Autoloader::getListFiles(Autoloader::VIEWS) as $file)
        {
            if (!str_ends_with($file, $template))
                continue;

            $this->pathCache[$template] = $file;
            return $file;
        }
        return false;
    }

    /**
     * Uses `findTemplate()` `false` property to tell if a template exists
     */
    public function templateExists(string $templateName): bool
    {
        return false !== $this->findTemplate($templateName);
    }

    public function render(string $templateName, array $context=[]): string
    {
        if (!($path = $this->findTemplate($templateName)))
            throw new Exception("[$templateName] view not found !");

        $newRenderShard = new RenderShard($path, $context, $this->current);
        $currentIndex = array_push($this->shards, $newRenderShard)-1;
        $current = $this->current = &$this->shards[$currentIndex];

        foreach ($current->getContext() as $varName => $varValue)
        {
            if (isset($$varName))
                Logger::getInstance()->warning(new InvalidArgumentException("Cannot redeclare [$name] while rendering"));
            else
                $$varName = $varValue;
        }

        $events = EventListener::getInstance();

        if (!ob_start())
            throw new Exception("Could not start a new output buffering");

        $events->dispatch(new BeforeViewRender($templateName));

        try
        {
            require $path;
        }
        catch (Throwable $err)
        {
            ob_end_clean();
            throw $err;
        }

        $events->dispatch(new AfterViewRender($templateName));
        $current->endSection();
        $content = ob_get_clean();

        $this->sections = array_merge($this->sections, $current->getAllSections());

        if ($parent = $current->getParentInfos())
            $content = $this->render($parent[0], $parent[1]);

        array_pop($this->shards);

        $lastIndex = count($this->shards)-1;
        $current = null;

        if ($lastIndex >= 0)
            $current = &$this->shards[$lastIndex];

        $this->current = $current;

        return $content;
    }

    public function useTemplate(string $template, array $context=[]): void
    {
        $this->current->setParent($template, $context);
    }

    public function startSection(string $sectionName): void
    {
        $this->current->startSection($sectionName);
    }

    public function stopSection(): void
    {
        $this->current->endSection();
    }

    public function section(string $sectionName): ?string
    {
        return $this->sections[$sectionName] ?? null;
    }
}