<?php

namespace DC\LinkProxy\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;

class Redirect extends \XF\Pub\Controller\AbstractController
{
    public function actionIndex() {
        $encodedUrl = $this->filter('to', '?str');
        if (!$encodedUrl || $encodedUrl == '')
        {
	        return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
        }

		$postId = $this->filter('post', '?uint');
		if (!$postId || $postId == 0)
		{
			return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
		}

		/** @var \XF\Entity\Post $post */
		$post = $this->em()->find('XF:Post', $postId);
		if (!$post)
		{
			return $this->error(\XF::phrase('DC_LinkProxy_url_not_valid'));
		}

		/** @var \DC\LinkProxy\Repository\LinkProxy $linkProxyRepo */
		$linkProxyRepo = $this->repository('DC\LinkProxy:LinkProxy');

		$urlDecoded = $linkProxyRepo->decodeUrl($encodedUrl, $postId);
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
	        'post' => $post,
            'url' => $urlDecoded
        ];
        return $this->view('DC\LinkProxy:Redirecting', 'DC_LinkProxy_Redirecting', $viewParams);
    }
}