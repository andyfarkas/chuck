<?php

namespace DixonsCz\Api;


class ApiService
{
    /**
     * @var \DixonsCz\Chuck\Svn\Helper
     */
    private $helper;

    public function __construct(\DixonsCz\Chuck\Svn\IHelper $svnHelper)
    {
        $this->helper = $svnHelper;
    }

    /**
     * @param string $project
     * @param string $pattern preg_replace pattern without delimiters
     * @return array list of tags matching pattern
     */
    public function listTags($project, $pattern = '.*')
    {
        $tags =  $this->helper->getTagList($project);

        return array_filter($tags, function ($item) use ($pattern) {
            return 1 === preg_match("~{$pattern}~", $item['name']);
        });
    }

    /**
     * @param string $project
     * @param string $sourceBranch {UAT, PROD}
     * @param string $tagName
     * @return string svn message
     * @throws SvnException
     */
    public function createTag($project, $sourceBranch, $tagName)
    {
        if(!$this->helper->doesBranchExist($project, $sourceBranch)) {
            throw new SvnException("Source branch doesn't exist");
        }

        if($this->helper->doesTagExist($project, $tagName)) {
            throw new SvnException("Tag already exists");
        }

        return $this->helper->createTag($tagName, "Creating: {$tagName}", "branches/{$sourceBranch}");
    }
} 