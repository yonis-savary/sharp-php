<?php

namespace Sharp\Classes\Web;

use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Http\Response;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Web\Classes\Shard;
use Sharp\Core\Autoloader;

class Renderer
{
    use Component, Configurable;

    protected bool $cached = false;

    protected array $shards = [];
    protected ?Shard $current = null;
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
        $this->cached = $this->configuration['cached'];
    }

    /**
     * Try to find a template file in Views directory
     * @param string $templateName template to look for
     * @return string|false Template's absolute path or false if not found
     */
    public function findTemplate(string $template): string|false
    {
        $ext = $this->configuration['file_extension'];

        if (!str_ends_with($template, $ext))
            $template .= $ext;

        if (file_exists($template))
            return $template;

        foreach (Autoloader::getListFiles(Autoloader::VIEWS) as $file)
        {
            if (str_ends_with($file, $template))
                return $file;
        }
        return false;
    }

    /**
     * Uses `findTemplate()` `false` property to tell if a template exists
     */
    public function templateExists(string $templateName): bool
    {
        return ($this->findTemplate($templateName) !== false);
    }

    public function render(string $templateName, array $context=[]): Response
    {
        $path = $this->findTemplate($templateName);

        $newShard = new Shard($path, $context, $this->current);
        $currentIndex = array_push($this->shards, $newShard)-1;
        $this->current = $this->shards[$currentIndex];


        foreach ($this->current->getContext() as $name => $value)
        {
            if (isset($$name))
            Logger::getInstance()->warning("Cannot redeclare [$name] while rendering");
            else
            $$name = $value;
        }


        ob_start();
        require $path;
        $this->current->endSection();
        $content = ob_get_clean();

        $this->sections = array_merge($this->sections, $this->current->getAllSections());

        if ($parent = $this->current->getParent())
            $content = $this->render($parent[0], $parent[1])->getContent();

        array_pop($this->shards);
        $this->current = $this->shards[count($this->shards)-1] ?? null;

        return Response::html($content);
    }

    public function useTemplate(string $template, array $context=[])
    {
        $this->current->setParent($template, $context);
    }

    public function startSection(string $sectionName)
    {
        $this->current->startSection($sectionName);
    }

    public function stopSection()
    {
        $this->current->endSection();
    }

    public function section(string $sectionName): string|null
    {
        return $this->sections[$sectionName] ?? null;
    }
}