<?php

namespace DC\LinkProxy\Repository;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class LinkProxy extends Repository
{
	/**
	 * @param \XF\Mvc\Entity\Entity $content
	 * @return bool
	 */
	public function isContentTypeSupported($content)
	{
		if (!$content instanceof Entity)
		{
			return false;
		}

		$contentType = $content->getEntityContentType();
		$supportedContentTypes = $this->options()->DC_LinkProxy_SupportedContentTypes;
		if (!isset($supportedContentTypes[$contentType]) || !$supportedContentTypes[$contentType])
		{
			return false;
		}

		return true;
	}

	/**
	 * Get content message field, which includes the original URL
	 *
	 * @param string $contentType
	 * @return string
	 */
	public function getContentMessageField($contentType)
	{
		return 'message';
	}

	/**
	 * Process proxy a URL and return the encoded URL
	 *
	 * @param string $url
	 * @param \XF\Mvc\Entity\Entity $content
	 * @return string
	 */
	public function proxyUrl($url, $content)
	{
		if (!$this->isContentTypeSupported($content))
		{
			return null;
		}

		$options = $this->options();

		/** Check white listed domains */
		$domainWhiteListed = $options->DC_LinkProxy_DomainWhiteList;
		$domainWhiteListedArray = explode("\n", $domainWhiteListed);
		$domain = parse_url($url, PHP_URL_HOST);
		$domain = ltrim($domain, 'www.'); // Remove "www" from base URL

		$urlEncoded = $this->app()->router('public')->buildLink('redirect', null, [
			'to' => base64_encode(htmlspecialchars($url)),
			'content_type' => $content->getEntityContentType(),
			'content_id' => $content->getEntityId()
		]);

		foreach ($domainWhiteListedArray as $value)
		{
			if ($domain == $value)
			{
				$urlEncoded = $url;
			}
		}

		return $urlEncoded;
	}

	/**
	 * Decode a URL
	 *
	 * @param string $encodedUrl
	 * @param \XF\Mvc\Entity\Entity $content
	 *
	 * @return string|false
	 */
	public function decodeUrl($encodedUrl, $content)
	{
		$urlDecoded = base64_decode($encodedUrl);
		if (filter_var($urlDecoded, FILTER_VALIDATE_URL) === FALSE)
		{
			return false;
		}

		if (!$this->isContentTypeSupported($content))
		{
			return false;
		}

		$contentStructureColumns = $content->structure()->columns;
		$messageField = $this->getContentMessageField($content->getEntityContentType());
		if (!isset($contentStructureColumns[$messageField]))
		{
			return false;
		}

		$message = $content->{$messageField};
		if (!$message || !is_string($message))
		{
			return false;
		}

		$message = strtolower($message);
		if (!str_contains($message, $urlDecoded))
		{
			return false;
		}

		return $urlDecoded;
	}
}