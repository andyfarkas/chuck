<?php

namespace DixonsCz\Chuck\Presenters;

/**
 * @author Michal Svec <michal.svec@dixonsretail.com>
 */
class InfoPresenter extends ProjectPresenter
{
    public function renderDefault($project)
    {
        $info = $this->context->svnHelper->getInfo($project);
        $this->template->url = $info['url'];
        $this->template->repository = $info['root'];
    }

    public function handleRepositoryUpdate()
    {
        $this->context->svnHelper->updateRepository();
        $this->flashMessage('Repository updated.'); // nefunguje na devu :/
        $this->redirect("default");
    }
}
