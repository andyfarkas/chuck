<?php

namespace DixonsCz\Chuck\Presenters;

/**
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class LogPresenter extends ProjectPresenter
{
    /**
     * Renders list of log with a visual paginator
     *
     * @param string $project
     */
    public function renderList($project)
    {
        $vp = new \VisualPaginator($this, 'vp');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 30;
        $paginator->itemCount = $this->context->svnHelper->getLogSize();
        $this->template->vpPage = $paginator->page;

        $this->template->mailTo = "";
        if (isset($this->context->parameters['projects'][$this->project]['sendTo'])) {
            $this->template->mailTo = $this->context->parameters['projects'][$this->project]['sendTo'];
        }

        try {
            $logList = $this->context->svnHelper->getLog(
                $this->context->svnHelper->getCurrentBranch(),
                $paginator->offset,
                $paginator->itemsPerPage
            );
        } catch (\Exception $e) {
            $logList = array();
            $this->flashMessage($e->getMessage(), 'error');
        }

//		$this->template->ticketLog = false;
        $this->template->logTpl = "";

        // generating log for confluence
        $selectedLogs = array();
        if (isset($_GET['log']) && !empty($_GET['log'])) {

            $changeLogList = array();
            foreach ($_GET['log'] as $revision) {
                $changeLogList[$revision] = $logList[$revision];
                $selectedLogs[] = $revision;
            }

            if (isset($_GET['emailSend']) && $_GET['emailSend'] == 'email') {
                $this->context->mailHelper->getMail(
                    $this->formatLog($changeLogList),
                    $this->template->projectName,
                    $this->project,
                    $_GET['toReleaseNote'],
                    'Line'
                );
                $this->flashMessage('Mail was sent!', 'success');
            }

            $this->template->logTpl = $this->getChangelogTemplate(
                $this->getTemplateForProject($project),
                $this->getLogGenerator()->generateTicketLog($changeLogList)
            );
        }

        \Nette\Diagnostics\Debugger::barDump($logList, "Log list");

        $logTable = new \DixonsCz\Chuck\Log\Table($this, 'logTable');
        $logTable->setLog($this->formatLog($logList));
        $logTable->setSelectedLogs($selectedLogs);

        $this->template->log = array();
    }
}
