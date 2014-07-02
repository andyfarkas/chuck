<?php

namespace DixonsCz\Chuck\Svn;

class FromFile implements IHelper
{
    /**
     *
     * @var string
     */
    protected $svnLogFile;

    /**
     * 
     * @param string $logPath
     */
    public function __construct($logPath)
    {
        $this->svnLogFile = $logPath;
    }

    /**
     * {@inheritdoc}
     */
    public function createTag($tagName, $tagMessage, $createFrom)
    {
    }

    /**
     * 
     * @return string
     */
    public function getCurrentBranch()
    {
        return 'fake';
    }

    /**
     * 
     * @param string $project
     * @return array
     */
    public function getInfo($project = null)
    {
        return array(
            'url' => 'http://really-fake-repo',
            'root' => 'fake-root',
        );
    }

    /**
     * 
     * @param string $path
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getLog($path = '/trunk', $offset = 0, $limit = 30)
    {
        $xmlLog = simplexml_load_file($this->svnLogFile);
        
        $output = array();
        foreach ($xmlLog as $log)
        {
            $output[(int) $log->attributes()->revision] = array(
                'revision' => (int) $log->attributes()->revision,
                'author' => (string) $log->author,
                'date' => (string) $log->date,
                'msg' => (string) $log->msg,
            );
        }
        
        return $output;
    }

    /**
     * 
     * @return int
     */
    public function getLogSize()
    {
        return 30;
    }

    /**
     * 
     * @return array
     */
    public function getTagList()
    {
        return array(
            'UAT-1.0.0' => array(
                'name' => 'UAT-1.0.0',
                'author' => 'john.doe',
                'revision' => '14',
            ),
            'UAT-1.0.1' => array(
                'name' => 'UAT-1.0.1',
                'author' => 'john.doe',
                'revision' => '18',
            ),
            'afsafsa' => array(
                'name' => 'afsafsa',
                'author' => 'john.doe',
                'revision' => '13',
            ),
        );
    }

    /**
     * 
     * @param string $tagName
     * @param int $limit
     * @return array
     */
    public function getTagLog($tagName, $limit = 30)
    {
        return $this->getLog();
    }

    /**
     * 
     * @param string $project
     */
    public function startup($project)
    {
        
    }

    public function updateRepository()
    {
        
    }

    /**
     * {@inheritdoc}
     */
    public function doesBranchExist($project, $branchName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function doesTagExist($project, $tagName)
    {
        return true;
    }

    public function getUATTagChangelog($tagName)
    {
        return array(
            '18' => array(
                'revision' => 18,
                'author' => 'svecm01',
                'date' => '2014-07-01T12:46:56.551610Z',
                'msg' => 'Test',
            ),
            '19' => array(
                'revision' => 19,
                'author' => 'svecm01',
                'date' => '2014-07-01T14:46:56.551610Z',
                'msg' => '[XXX-12] Test',
            ),
        );
    }
}
