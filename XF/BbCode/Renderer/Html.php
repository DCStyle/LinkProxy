<?php

namespace DC\LinkProxy\XF\BbCode\Renderer;

class Html extends XFCP_Html
{
    protected function getRenderedLink($text, $url, array $options)
	{
		$urlTagString = parent::getRenderedLink($text, $url, $options);

		$link = $this->formatter->getLinkClassTarget($url);
		if ($link['type'] != 'internal')
		{
			$post = $options['entity'] ?? null;
			if (!$post instanceof \XF\Entity\Post)
			{
				return $urlTagString;
			}

			/** @var \DC\LinkProxy\Repository\LinkProxy $linkProxyRepo */
			$linkProxyRepo = \XF::repository('DC\LinkProxy:LinkProxy');

			$urlEncoded = $linkProxyRepo->proxyUrl($url, $post);
			if (!$urlEncoded)
			{
				return $urlTagString;
			}

			$urlTagString = str_replace('href="' . $url . '"', 'href="' . $urlEncoded . '"', $urlTagString);
		}

		return $urlTagString;
	}
}