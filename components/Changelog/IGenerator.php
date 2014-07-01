<?php

namespace DixonsCz\Chuck\Changelog;

interface IGenerator
{
    /**
     * Generates HTML/markup template for project
     *
     * @param string $project
     * @param array $log
     * @return string
     */
    public function getLogFormatted($project, array $log);
}
