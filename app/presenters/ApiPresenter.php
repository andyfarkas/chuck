<?php

namespace DixonsCz\Chuck\Presenters;


class ApiPresenter extends ProjectPresenter
{
    /**
     * @var \DixonsCz\Chuck\Svn\Helper
     */
    private $svn;

    /**
     * @var \DixonsCz\Chuck\Api\ApiService
     */
    private $service;

    public function __construct(\DixonsCz\Chuck\Svn\IHelper $svnHelper, \DixonsCz\Chuck\Api\ApiService $service)
    {
        $this->svn = $svnHelper;
        $this->service = $service;
    }

    /**
     * POST: Creates new tag
     * GET: get tag list
     *
     * @param  string $project
     * @param  string|NULL $id
     * @throws \DixonsCz\Chuck\Api\InvalidMethodException
     */
    public function actionUatTags($project, $id = null)
    {
        $response = "";
        try {
            switch ($this->getRequest()->getMethod()) {
                case "POST":
                    if(!is_string($id)) {
                        throw new \InvalidArgumentException("Missing tag name");
                    }
                    $response = $this->service->createTag($project, "UAT", $id);
                    break;

                case "GET":
                    $response = $this->service->listTags($project, 'UAT');
                    break;

                default:
                    throw new \DixonsCz\Chuck\Api\InvalidMethodException("Unsupported HTTP method.");
            }
        } catch(\Exception $e) {
            $this->sendJson(array(
                'status' => 'NOK',
                'message' => $e->getMessage(),
            ));
        }

        $this->sendJson(array(
            'status' => 'OK',
            'message' => $response,
        ));
    }

    /**
     * Gets history for tag
     *
     * @param  string $project
     * @param  string|null $id
     * @throws \DixonsCz\Chuck\Api\InvalidMethodException
     */
    public function actionHistory($project, $id = null)
    {
        $response = "";
        try {
            switch ($this->getRequest()->getMethod()) {
                case "GET":
                    $response = $this->service->getTagHistory($project, $id, true);
                    break;

                default:
                    throw new \DixonsCz\Chuck\Api\InvalidMethodException("Unsupported HTTP method.");
            }
        } catch(\Exception $e) {
            $this->sendJson(array(
                    'status' => 'NOK',
                    'message' => $e->getMessage(),
                ));
        }

        $this->sendJson(array(
                'status' => 'OK',
                'message' => $response,
            ));
    }
}
