<?php

namespace DC\LinkProxy\Repository;

use XF\Mvc\Entity\Repository;

class LinkProxy extends Repository
{
	/**
	 * Process proxy a URL and return the encoded URL
	 *
	 * @param string $url
	 * @param \XF\Entity\Post $post
	 * @return string
	 */
	public function proxyUrl($url, $post)
	{
		if (!$post instanceof \XF\Entity\Post)
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
			'post' => $post->post_id
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
	 * @return string|false
	 */
	public function decodeUrl($encodedUrl, $postId)
	{
		$urlDecoded = base64_decode($encodedUrl);
		if (filter_var($urlDecoded, FILTER_VALIDATE_URL) === FALSE)
		{
			return false;
		}

		/** @var \XF\Entity\Post $post */
		$post = $this->em->find('XF:Post', $postId);
		if (!$post)
		{
			return false;
		}

		$message = strtolower($post->message);
		if (!str_contains($message, $urlDecoded))
		{
			return false;
		}

		return $urlDecoded;
	}
}