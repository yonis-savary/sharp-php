<?php

namespace Sharp\Classes\Web\Classes;

class Shard
{
    protected $file;
    protected $context;
    protected $parentTemplate = null;
    protected $parentContext = null;

    protected $currentSection = null;
    protected $sectionContent = [];

    public function __construct(string $file, array $context=[], Shard $parent=null)
    {
        $parentContext = $parent ? $parent->getContext() : [];
        $this->file = $file;
        $this->context = array_merge($parentContext, $context);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setParent(string $template, array $context=[])
    {
        $this->parentTemplate = $template;
        $this->parentContext = $context;
    }

    public function getParent()
    {
        return $this->parentTemplate ? [$this->parentTemplate, $this->parentContext]: null;
    }

    public function startSection(string $section)
    {
        if ($this->currentSection)
            $this->endSection();

        $this->currentSection = $section;
        ob_start();
    }

    public function endSection()
    {
        if (!$this->currentSection)
            return;

        $this->sectionContent[$this->currentSection] = ob_get_clean();
    }

    public function getSection(string $name)
    {
        return $this->sectionContent[$name] ?? '';
    }

    public function getAllSections()
    {
        return $this->sectionContent;
    }
}