<?php

namespace DixonsCz\Chuck\Presenters;

/**
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class TagPresenter extends ProjectPresenter
{

    protected $tagList;

    public function renderList($project)
    {
        $this->tagList = $this->context->svnHelper->getTagList();
        $this->template->logTpl = "";
        $this->template->selectedTags = array();
        $this->template->mailTo = $this->context->parameters['projects'][$this->project]['sendTo'];

        if (isset($_GET['tag']) && !empty($_GET['tag']) && is_array($_GET['tag'])) {
            if (count($_GET['tag']) != 2) {
                $this->flashMessage("Please, select exactly 2 tags!", 'error');
                $this->redirect('this');
            }

            if (isset($_GET['sendReleaseNote'])) {
                $this->sendReleaseNote($_GET['tag'], $_GET['toReleaseNote']);
            }

            if (isset($_GET['confluenceLog'])) {
                $this->generateChangelog($project, $_GET['tag']);
            }
        }

        $this->template->tags = $this->tagList;
    }

    public function renderCreateTag()
    {

    }

    public function renderCompareWithTrunk($tagName)
    {
        $trunkLog = $this->context->svnHelper->getLog();
        $tagLog = $this->context->svnHelper->getTagLog((string) $tagName);

        // format commit message
        $trunkLog = $this->formatLog($trunkLog);
        $tagLog = $this->formatLog($tagLog);

        // create components
        $trunkLogTable = new \DixonsCz\Chuck\Log\Table($this, 'trunkTable');
        $tagLogTable = new \DixonsCz\Chuck\Log\Table($this, 'tagTable');

        // set components
        $trunkLogTable->disableColumnRender('date');
        $trunkLogTable->disableColumnRender('author');
        $trunkLogTable->setLog($trunkLog);

        $tagLogTable->disableColumnRender('date');
        $tagLogTable->disableColumnRender('author');
        $tagLogTable->setLog($tagLog);

        $this->template->tag = $tagName;
    }

    protected function createComponentCreateTagForm($name)
    {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText('tagName', "Tag name");
        $form->addTextArea('tagMessage', 'Message');

        $cancelButton = $form->addSubmit('cancelButton', 'Cancel')->setAttribute('class', 'btn');

        $presenter = $this;
        $cancelButton->onClick[] = function () use ($presenter) {
            $presenter ->redirect('default');
        };

        $form->addSubmit('submitButton', 'Create')->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = callback($this, 'createTagFormSubmitted');

        return $form;
    }

    /**
     * @param \Nette\Application\UI\Form $form
     */
    public function createTagFormSubmitted($form)
    {
        $values = $form->getValues();
        $this->context->svnHelper->createTag($this->project, $values['tagName'], $values['tagMessage']);
        $this->redirect("Project:log", array('project' => $values['project']));
    }

    /**
     * @param string $project
     * @param array  $tags
     */
    public function generateChangelog($project, $tags)
    {
        $tagNames = array_keys($this->tagList);

        sort($tags);

        $tag1 = $tags[0];
        $tag2 = $tags[1];

        $callback = function (\Nette\Application\UI\Presenter $parent, $tag) {
            $parent->flashMessage("Unknown tag {$tag}");
            $parent->redirect("Tag:list");
        };

        if (false === array_search($tag1, $tagNames)) {
            $callback($this, $tag1);
        }
        if (false === array_search($tag2, $tagNames)) {
            $callback($this, $tag2);
        }

        $this->template->selectedTags = array($tag1, $tag2);

        $tag1Log = $this->context->svnHelper->getTagLog($tag1, 100);
        $tag2Log = $this->context->svnHelper->getTagLog($tag2, 100);

        $diff = array_diff_key($tag1Log, $tag2Log);

        $changelogTpl = $this->getChangelogTemplate(
            $this->getTemplateForProject($project),
            $this->getLogGenerator()->generateTicketLog($diff)
        );
        $changelogTpl->rollbackTag = $tag1;
        $changelogTpl->releaseTag = $tag2;
        $this->template->logTpl = $changelogTpl;
    }

    /**
     * @param array  $tags
     * @param string $toReleaseNote
     */
    public function sendReleaseNote($tags, $toReleaseNote)
    {
        $tagNames = array_keys($this->tagList);
        sort($tags);

        list($tag1, $tag2) = $tags;

        $callback = function (\Nette\Application\UI\Presenter $parent, $tag) {
            $parent->flashMessage("Unknown tag {$tag}");
            $parent->redirect("Tag:list");
        };

        if (false === array_search($tag1, $tagNames)) {
            $callback($this, $tag1);
        }
        if (false === array_search($tag2, $tagNames)) {
            $callback($this, $tag2);
        }

        $this->template->selectedTags = array($tag1, $tag2);

        $tag1Log = $this->context->svnHelper->getTagLog($tag1, 100);
        $tag2Log = $this->context->svnHelper->getTagLog($tag2, 100);

        $diff = array_diff_key($tag1Log, $tag2Log);

        $this->context->mailHelper->getMail(
            $this->formatLog($diff),
            $this->template->projectName,
            $this->project,
            $toReleaseNote,
            'Line'
        );

        $this->flashMessage('Mail was sent!', 'success');
    }
}
