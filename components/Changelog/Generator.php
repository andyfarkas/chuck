<?php

namespace DixonsCz\Chuck\Changelog;


class Generator implements IGenerator
{
    /**
     * @var array
     */
    private $projects;

    /**
     * @var array
     */
    private $tplVariables;

    /**
     * @param array $projects project configuration
     * @param array $tplVariables variables passed to changelog template
     */
    public function __construct(array $projects, $tplVariables = array())
    {
        $this->projects = $projects;
        $this->tplVariables = $tplVariables;
    }

    /**
     * Gets file name for the template used set in config
     *
     * @param  string $project
     * @return string
     */
    protected function getTemplateFile($project)
    {
        return isset($this->projects[$project]['changelogTpl']) ? $this->projects[$project]['changelogTpl'] : 'default.latte';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogFormatted($project, array $log)
    {
        $template = $this->createTemplate($project);
        $template->ticketLog = $log;
        foreach($this->tplVariables as $key => $var) {
            $template->$key = $var;
        }

        return (string) $template;
    }

    /**
     * Creates Latte template to generate changelog
     *
     * @param string $project
     * @return \Nette\Templating\FileTemplate
     */
    private function createTemplate($project)
    {
        $template = new \Nette\Templating\FileTemplate(APP_DIR . '/templates/Log/changelogTpls/'.$this->getTemplateFile($project));
        $template->registerFilter(new \Nette\Latte\Engine());

        return $template;
    }
} 