<?php

namespace App\StaticPageGeneratorExtension\Commands;

use App\Task\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\Application;
use Symfony\Component\Filesystem\Filesystem;

class Generate404PageCommand extends BaseCommand
{
    /**
     * @var string
     */
    private $sourceDirectory;
    /**
     * @var string
     */
    private $finalTemplateDirectory;
    /**
     * @var Filesystem
     */
    private $fileSystem;

    private $templating;

    public function __construct(Application $app, Filesystem $fileSystem, $templating, $sourceDirectory, $finalTemplateDirectory)
    {
        $this->sourceDirectory = $sourceDirectory;
        $this->finalTemplateDirectory = $finalTemplateDirectory;
        $this->fileSystem = $fileSystem;
        $this->templating = $templating;
        parent::__construct($app);
    }
    protected function configure()
    {
        $this->setName('static-page-generator:404-page');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->fileSystem->exists($this->sourceDirectory)) {
            throw new \Exception('error copying directory');
        }
        $this->fileSystem->mirror($this->sourceDirectory, $this->finalTemplateDirectory);
        $this->replacePlaceholder('<!-- poster_placeholder -->', 'StaticPage/404/banner');
        $this->replacePlaceholder('<!-- counters_placeholder -->', 'StaticPage/404/metrics');
    }

    private function replacePlaceholder($placeholder, $data)
    {
        $page404TemplateIndexPath = $this->finalTemplateDirectory . '/index.html';
        $page404PageTemplate = file_get_contents($page404TemplateIndexPath);
        $renderData = $this->templating->render($data);
        $page404PageTemplate = str_replace($placeholder, $renderData, $page404PageTemplate);
        $this->fileSystem->dumpFile($page404TemplateIndexPath, $page404PageTemplate);
    }
}
