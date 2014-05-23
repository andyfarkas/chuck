<?php

namespace DixonsCz\Chuck\Svn;

use Psr\Log\LoggerInterface;

/**
 *
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class Helper implements IHelper
{
    /**
     * Path to temporary directory
     * @var string
     */
    private $tempDir;

    /**
     * Project name
     * @var string
     */
    private $project;

    /**
     * Remote URL used for remote SVN commands without slash at the end
     * @var string
     */
    private $remoteUrl;

    /**
     * List of all commit to pagination easier
     * @var array
     */
    private $commitList;

    /**
     * Path to current branch - f.e. trunk, branches/MN_WW
     * @var string
     */
    private $currentBranch;

    /**
     * Project list
     * @var array
     */
    private $projects = array();

    /**
     * @var null|array
     */
    private $credentials;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($tempDir, LoggerInterface $logger, $credentials = null, $projects = array())
    {
        //TODO: check if exists
        $this->logger = $logger;
        $this->tempDir = $tempDir;
        $this->projects = $projects;
        $this->credentials = $credentials;
    }

    /**
     * Returns svn executable string to append on the beginning of command
     *
     * @return string
     */
    protected function getSvnExecutable()
    {
        $auth = "";
        if ($this->credentials) {
            $auth = "--username \"{$this->credentials['user']}\" --password \"{$this->credentials['password']}\" --no-auth-cache";
        }

        $ret = "svn {$auth} ";

        if (PHP_OS == 'WINNT') {
            $ret = "C:\\cygwin\\bin\\svn.exe {$auth} ";
        } else { // ( PHP_OS != 'WINNT' ) {
            $ret = "export LC_ALL=C; ".$ret;
        }

        return $ret; // LANG=en
    }

    /**
     * @param string $project project directory
     */
    public function startup($project)
    {
        $this->project = $project;
        $info = $this->getInfo();
        $this->remoteUrl = $info['root'];

        $this->currentBranch = str_replace($info['root'], '', $info['url']);

        // load revision log
        $cmd = "log --with-no-revprops"; // norevprops for faster load
        $svnLog = $this->executeProjectCommand($cmd, true);

        $xmlLog = simplexml_load_string($svnLog);
        foreach ($xmlLog->logentry as $revision) {
            $this->commitList[] = (int) $revision->attributes()->revision;
        }
    }

    /**
     * Executes command command
     *
     * @param $command
     * @return string
     */
    private function executeCommand($command)
    {
        return trim(shell_exec($command));
    }

    /**
     * @param  string $command
     * @param  bool   $xml
     * @return string
     */
    private function executeProjectCommand($command, $xml = true)
    {
        $cmd = $this->getSvnExecutable() . " --non-interactive --trust-server-cert " . $command . ' ' . ($xml == true ? '--xml' : '') . ' "' . $this->projects[$this->project]['repositoryPath'] . '"';
        $result = $this->executeCommand($cmd);
        $this->logger->info('Project command: ' . $cmd);
//        $this->logger->info('Result: ' . $result);

        return $result;
    }

    /**
     * Executes command on remote repository
     *
     * @param   string $command remote command to execute
     * @param   string $path    path in repository f.e. /tags/1.0.0
     * @return  string script output
     */
    private function executeRemoteCommand($command, $path = '/trunk')
    {
        // check if there is / at the beginning of path
        if (0 == preg_match('~^\/.*~', $path)) {
            $path = '/' . $path;
        }

        $cmd = $this->getSvnExecutable() . $command . ' --xml "' . $this->remoteUrl . $path . '"';
        $this->logger->info('Remote command:' . $cmd);

        return $this->executeCommand($cmd);
    }

    /**
     * @param  string $tagName
     * @param  int $limit
     * @throws \Exception
     * @return array  revision name as a key, revision, author, message and date as a content
     */
    public function getTagLog($tagName, $limit = 30)
    {
        $this->logger->info("getTagLog: log for tag: $tagName");

        $cmd = "log --limit {$limit}";
        $log = $this->executeRemoteCommand($cmd, "/tags/{$tagName}");

        if ($log == "") {
            throw new \Exception("Unable to load svn log!");
        }

        return $this->processRawLog($log);
    }

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
    public function getLog($path = '/trunk', $offset = 0, $limit = 30)
    {
        $last = $offset + $limit;

        // get latest valid revision number
        if ($last > count($this->commitList)) {
            $last = count($this->commitList) - 1;
        }

        $cmd = "log -r {$this->commitList[$offset]}:{$this->commitList[$last]} --limit {$limit}";
        $this->logger->info('getLog: ' . $cmd);

        $log = $this->executeProjectCommand($cmd, $path);
        $this->logger->info('downloaded log: ' . $log);

        if ($log == "") {
            throw new \Exception("Unable to load svn log!");
        }

        return $this->processRawLog($log);
    }

    /**
     * @param  string $log RAW xml from SVN
     * @return array  array of log details
     */
    protected function processRawLog($log)
    {
        $xmlLog = simplexml_load_string($log);

        $output = array();
        foreach ($xmlLog as $log) {
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
     * @return string
     */
    public function getCurrentBranch()
    {
        return $this->currentBranch;
    }

    /**
     * Get log messages count
     *
     * @return integer total count of commit messages in log
     */
    public function getLogSize()
    {
        return (int) count($this->commitList);
    }

    /**
     * Get svn info parameters
     * TODO: return all parameters from svn info
     *
     * @param  string|null $project
     * @return string[]
     */
    public function getInfo($project = null)
    {
        if (null == $project || '' == $project) {
            $project = $this->project;
        }

        $xml = simplexml_load_string($this->executeProjectCommand('info', $project));

        return array(
            'url' => $xml->entry->url,
            'root' => $xml->entry->repository->root,
        );
    }

    /**
     * Load tags information
     *
     * @return array[array] array with tag names as keys and array (with keys: name, author, date, revision) as values
     */
    public function getTagList()
    {
        $tagList = simplexml_load_string($this->executeRemoteCommand('ls', '/tags/'));

        $output = array();
        foreach ($tagList->list->entry as $tag) {

            $output[(string) $tag->name] = array(
                'name' => (string) $tag->name,
                'author' => (string) $tag->commit->author,
                'date' => (string) $tag->commit->date,
                'revision' => (string) $tag->commit->attributes()->revision
            );
        }

        return $output;
    }

    public function updateRepository()
    {
        $this->executeProjectCommand('update', false);

        return true;
    }

    /**
     * Creates new tag from trunk
     *
     * @param string $tagName    tag name
     * @param string $tagMessage message to add
     */
    public function createTag($tagName, $tagMessage)
    {
        $tempFile = $this->tempDir . '/commitMessage';

        // TODO: make unique filename
        file_put_contents($tempFile, $tagMessage);
        $cmd = "svn cp {$this->remoteUrl}/trunk {$this->remoteUrl}/tags/{$tagName} -F {$tempFile}";
        die($cmd); //TODO: test!!!
        $this->executeCommand($cmd);

        unlink($tempFile);
    }
}
