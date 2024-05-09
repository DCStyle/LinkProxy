<?php

namespace DC\LinkProxy\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Pub\Controller\AbstractController;

class Redirect extends AbstractController
{
    public function actionIndex() {
        $encodedUrl = $this->filter('to', '?str');
        if (!$encodedUrl || $encodedUrl == '')
        {
	        return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
        }

		$contentType = $this->filter('content_type', '?str');
		if (!$contentType)
		{
			return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
		}

		$contentId = $this->filter('content_id', '?str');
		if (!$contentId)
		{
			return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
		}

		/** @var \XF\Mvc\Entity\Entity $content */
		$content = $this->app->findByContentType($contentType, $contentId);

	    /** @var \DC\LinkProxy\Repository\LinkProxy $linkProxyRepo */
	    $linkProxyRepo = $this->repository('DC\LinkProxy:LinkProxy');

	    $urlDecoded = $linkProxyRepo->decodeUrl($encodedUrl, $content);
	    if (!$urlDecoded)
	    {
		    return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
	    }

		if ($this->isPost())
		{
			return $this->redirect($urlDecoded);
		}
        
        $viewParams = [
			'encodedUrl' => $encodedUrl,
	        'contentType' => $contentType,
	        'contentId' => $contentId,
	        'contentUrl' => $content->getContentUrl(),
            'url' => $urlDecoded
        ];
        return $this->view('DC\LinkProxy:Redirecting', 'DC_LinkProxy_Redirecting', $viewParams);
    }
}