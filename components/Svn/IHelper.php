<?php

namespace DixonsCz\Chuck\Svn;

interface IHelper
{

    /**
     * @param string $project project directory
     */
    public function startup($project);

    /**
     * @param  string $tagName
     * @param  int $limit
     * @throws \Exception
     * @return array  revision name as a key, revision, author, message and date as a content
     */
    public function getTagLog($tagName, $limit = 30);

    /**
     * List all commits
     *
     * @param  string       $path   path in project repository, default /trunk, f.e. /tags/1.0.0
     * @param  int          $offset
     * @param  int          $limit
     * @return array[array] list of svn commits in array of hashes - keys are revision numbers
     *                             values have keys: revision, author, date, msg
     * @throws \Exception
     */
    public function getLog($path = '/trunk', $offset = 0, $limit = 30);

    /**
     * @return string
     */
    public function getCurrentBranch();

    /**
     * Get log messages count
     *
     * @return integer total count of commit messages in log
     */
    public function getLogSize();

    /**
     * Get svn info parameters
     * TODO: return all parameters from svn info
     *
     * @param  string|null $project
     * @return string[]
     */
    public function getInfo($project = null);

    /**
     * Load tags information
     *
     * @return array[array] array with tag names as keys and array (with keys: name, author, date, revision) as values
     */
    public function getTagList();

    public function updateRepository();

    /**
     * Creates new tag from trunk
     *
     * @param string $tagName tag name
     * @param string $tagMessage message to add
     * @param string $createFrom branch or trunk
     */
    public function createTag($tagName, $tagMessage, $createFrom);

    /**
     * Checks if branch exists
     *
     * @param $project
     * @param $branchName
     * @return boolean
     */
    public function doesBranchExist($project, $branchName);

    /**
     * Checks if tag exists (is not empty)
     *
     * @param $project
     * @param $tagName
     * @return mixed
     */
    public function doesTagExist($project, $tagName);
}
