<?php

namespace DC\LinkProxy\XF\Template;

class Templater extends XFCP_Templater
{
    public function renderUnfurl(\XF\Entity\UnfurlResult $result, array $options = [])
    {
        $templateString = parent::renderUnfurl($result, $options);

	    $formatter = $this->app->stringFormatter();
	    $linkInfo = $formatter->getLinkClassTarget($result->url);

	    if ($linkInfo['type'] != 'internal')
	    {
		    $content = $options['entity'] ?? null;
			$url = $result->url;

		    /** @var \DC\LinkProxy\Repository\LinkProxy $linkProxyRepo */
		    $linkProxyRepo = \XF::repository('DC\LinkProxy:LinkProxy');

			$urlEncoded = $linkProxyRepo->proxyUrl($url, $content);
			if (!$urlEncoded)
			{
				return $templateString;
			}

			$templateString = str_replace($url, $urlEncoded, $templateString);
	    }

		return $templateString;
    }
}