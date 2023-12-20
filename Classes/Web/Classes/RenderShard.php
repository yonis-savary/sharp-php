<?php

namespace Sharp\Classes\Web\Classes;

class RenderShard
{
    protected string $file;
    protected array $context;

    protected ?array $parentContext = [];
    protected ?string $parentTemplate = null;

    protected ?string $currentSection = null;
    protected array $sectionContent = [];

    public function __construct(string $file, array $context=[], RenderShard $parent=null)
    {
        $this->parentContext = $parent ? $parent->getContext() : [];
        $this->file = $file;
        $this->context = array_merge($this->parentContext, $context);
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setParent(string $template, array $context=[]): void
    {
        $this->parentTemplate = $template;
        $this->parentContext = $context;
    }

    public function getParentInfos(): ?array
    {
        return $this->parentTemplate ? [$this->parentTemplate, $this->parentContext]: null;
    }

    public function startSection(string $section): void
    {
        if ($this->currentSection)
            $this->endSection();

        $this->currentSection = $section;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection)
            $this->sectionContent[$this->currentSection] = ob_get_clean();

        $this->currentSection = null;
    }

    public function getSection(string $name): string
    {
        return $this->sectionContent[$name] ?? '';
    }

    public function getAllSections(): array
    {
        return $this->sectionContent;
    }
}