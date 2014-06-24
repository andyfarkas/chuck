<?php

namespace DixonsCz\Chuck\Presenters;


class ApiPresenter extends ProjectPresenter
{
    /**
     * @var \DixonsCz\Chuck\Svn\Helper
     */
    private $svn;

    public function __construct(\DixonsCz\Chuck\Svn\IHelper $svnHelper)
    {
        $this->svn = $svnHelper;
    }

    /**
     * @param $project
     * @param $tagName
     * @throws \Nette\Application\AbortException
     */
    public function actionCreateUatTag($project, $tagName)
    {
        $branch = 'UAT';
        if(!$this->svn->doesBranchExist($project, $branch)) {
            $this->sendJson(array(
                'status' => 'NOK',
                'message' => "Source branch doesn't exist",
            ));
        }

        if($this->svn->doesTagExist($project, $tagName)) {
            $this->sendJson(array(
                'status' => 'NOK',
                'message' => "Tag already exists",
            ));
        }

        $this->svn->createTag($tagName, "Creating: {$tagName}", 'branches/UAT');

        $this->terminate();
        $this->sendJson(array(
            'status' => 'OK',
            'message' => 'Tag created',
        ));
    }


    public function actionGetTagHistory($project, $tagName)
    {
        $logList = $this->svn->getUATTagChangelog($tagName);

        $changeLog = $this->getChangelogTemplate(
            $this->getTemplateForProject($project),
            $this->getLogGenerator()->generateTicketLog($logList)
        );

        $this->sendJson(array(
                'status' => 'OK',
                'message' => (string) $changeLog,
            ));


    }


}
