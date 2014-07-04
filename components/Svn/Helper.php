<?php

namespace DixonsCz\Chuck\Svn;

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
     * @var Panel
     */
    private $panel = null;

    public function __construct($tempDir, $panel, $credentials = null, $projects = array())
    {
        //TODO: check if exists
        $this->panel = $panel;
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

        $this->panel->startCommand($cmd);
        $result = $this->executeCommand($cmd);
        $this->panel->endCommand($result);

        return $result;
    }

    /**
     * Executes command on remote repository
     *
     * @param   string  $command remote command to execute
     * @param   string  $path    path in repository f.e. /tags/1.0.0
     * @param   boolean $xml    use xml output?
     * @return  string  script output
     */
    private function executeRemoteCommand($command, $path = '/trunk', $xml = true)
    {
        // check if there is / at the beginning of path
        if ($path !== null && 0 == preg_match('~^\/.*~', $path)) {
            $path = '/' . $path;
        }

        $cmd = $this->getSvnExecutable() . $command . ($xml ? ' --xml ' : '') . ($path !== null ? '"'.$this->remoteUrl . $path.'"' : "");
        $this->panel->startCommand($cmd);
        $return = $this->executeCommand($cmd);
        $this->panel->endCommand($return);

        return $return;
    }

    /**
     * @param  string $tagName
     * @param  int $limit
     * @throws \Exception
     * @return array  revision name as a key, revision, author, message and date as a content
     */
    public function getTagLog($tagName, $limit = 30)
    {
        $cmd = "log --limit {$limit}";
        $log = $this->executeRemoteCommand($cmd, "/tags/{$tagName}");

        if ($log == "") {
            throw new \Exception("Unable to load svn log!");
        }

        return $this->processRawLog($log);
    }

    /**
     * Checks two tags, their revisions and then generates log from uat branch
     *
     * @param  string $tagName
     * @return array
     * @throws \DixonsCz\Chuck\Api\SvnException
     */
    public function getUATTagChangelog($tagName)
    {
        // find previous tag
        $tagList = $this->getTagList();
        ksort($tagList);
        $tagNames = array_keys($tagList);
        $tagPosition = array_search($tagName, $tagNames);

        // check if there is preceding tag and if it's really UAT
        if(!isset($tagName[$tagPosition-1]) || strstr($tagNames[$tagPosition-1], 'UAT') === false) {
            throw new \DixonsCz\Chuck\Api\SvnException("Unable to create changelog from tag {$tagName}");
        }

        $prevTagInfo = $this->getTagInfo($tagNames[$tagPosition-1]);
        $startCommit = $prevTagInfo->entry->commit->attributes()->revision;

        $cmd = "log -r {$startCommit}:HEAD";

        $this->panel->startCommand($cmd);
        $log = $this->executeRemoteCommand($cmd, "tags/{$tagName}");
        $this->panel->endCommand($log);

        if ($log == "") {
            throw new \DixonsCz\Chuck\Api\SvnException("Unable to load svn log for 'tags/{$tagName}'!");
        }

        return $this->processRawLog($log);
    }

    /**
     * @param  string $tagName
     * @return \SimpleXMLElement
     */
    public function getTagInfo($tagName)
    {
        $info = $this->executeRemoteCommand('info', "/tags/{$tagName}");
        return simplexml_load_string($info);
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

        $this->panel->startCommand($cmd);
        $log = $this->executeProjectCommand($cmd, $path);
        $this->panel->endCommand($log);

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
     * @param string $project
     * @return array[array] array with tag names as keys and array (with keys: name, author, date, revision) as values
     */
    public function getTagList($project = null)
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
     * {@inheritdoc}
     */
    public function createTag($tagName, $tagMessage, $createFrom)
    {
        $tempFile = $this->tempDir . '/tagCommitMessage-'.$tagName;

        // TODO: make unique filename
        file_put_contents($tempFile, $tagMessage);
        $cmd = "cp {$this->remoteUrl}/{$createFrom} {$this->remoteUrl}/tags/{$tagName} -F {$tempFile}";
        $return = $this->executeRemoteCommand($cmd, null, false);
        unlink($tempFile);
        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function doesBranchExist($project, $branchName)
    {
        $result = $this->executeRemoteCommand("ls --depth immediates", "/branches/{$branchName}");

        $xml = simplexml_load_string($result);

        return $xml->list->entry->count() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function doesTagExist($project, $tagName)
    {
        $result = $this->executeRemoteCommand("ls --depth immediates", "/tags/{$tagName}");

        $xml = simplexml_load_string($result);

        return $xml->list->entry->count() > 0;
    }
}
