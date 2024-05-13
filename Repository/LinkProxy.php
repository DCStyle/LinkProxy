<?php

namespace DC\LinkProxy\Repository;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;

class LinkProxy extends Repository
{
	protected function reverseUrl($url)
	{
		$parts = explode('.', $url);
		$reversedParts = array_reverse($parts);

		return implode('.', $reversedParts);
	}

	protected function generatePossibleMatches($url)
	{
	    // Extract the domain and the full URL path without scheme
	    $domain = parse_url($url, PHP_URL_HOST);
	    $fullPath = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);

	    $domain = strtolower($domain);
	    $domain = ltrim($domain, 'www.'); // Remove "www" if present

	    // Split the domain into parts and reverse to start from the top-level domain
	    $domainParts = array_reverse(explode('.', $domain));
	    $possibleMatches = [];
	    $subdomain = '';

	    // Add the full URL path first
	    $possibleMatches[] = rtrim($fullPath, '?'); // Remove trailing '?' if no query parameters

	    // Construct all possible subdomains and the domain itself
	    foreach ($domainParts as $index => $part)
		{
	        if ($subdomain === '') {
	            $subdomain = $part;
	        } else {
				$subdomain = $subdomain . '.' . $part;
			}

			// Only add subdomains that include a dot (thus skipping top-level domain alone)
			if (str_contains($subdomain, '.')) {
				$possibleMatches[] = $this->reverseUrl($subdomain);
			}
		}

		return array_unique($possibleMatches);
	}

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

		// Split the domain whitelist into an array, removing any extra spaces
		/** Check white listed domains */
		$domainWhiteListed = $this->options()->DC_LinkProxy_DomainWhiteList;
		$domainWhiteListedArray = array_map('trim', explode("\n", $domainWhiteListed));

		$possibleMatches = $this->generatePossibleMatches($url);
		foreach($possibleMatches AS $possibleMatch)
		{
			// Check the exact match and subdomain match
			if (in_array($possibleMatch, $domainWhiteListedArray) || in_array('.' . $possibleMatch, $domainWhiteListedArray)) {
				return $url; // Return the original URL if the domain is whitelisted
			}
		}

		// Perform encoding if no match found in whitelist
		return $this->app()->router('public')->buildLink('redirect', null, [
			'to' => base64_encode(htmlspecialchars($url)),
			'content_type' => $content->getEntityContentType(),
			'content_id' => $content->getEntityId()
		]);
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

		if (!str_contains($message, $urlDecoded))
		{
			return false;
		}

		return $urlDecoded;
	}
}