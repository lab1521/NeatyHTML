<?php
namespace Lab1521;

class NeatyHTML
{
	protected $document;
	protected $markup;
	protected $blockList = [
		'attr' => [],
		'tags' => [],
		'tagOverrides' => [],
	];

	protected $tidyConfig = [
		'indent'                      => true,
		'output-html'                 => true,
		'drop-font-tags'              => true,
		'clean'                       => true,
		'merge-spans'                 => true,
		'drop-proprietary-attributes' => true,
		'show-body-only'              => true,
	];

	public function __construct($markup)
	{
		$this->markup = $markup;
		$this->blockList['tagOverrides'] = require 'blockedTagOverrides.php';
		$this->blockList['tags'] = require 'blockedTags.php';
		$this->blockList['attr'] = require 'blockedAttributes.php';
		$this->document = new \DOMDocument('1.0', 'utf-8');
		$this->loadHtml($markup);
	}

	public function loadHtml($markup)
	{
		if ($markup) $this->document->loadHTML($markup);
		return $this;
	}

	public function html()
	{
		$body = $this->getBody();

		if (!$body) return '';

		$bodyTags = array('<body>' => '', '</body>' => '');

		return strtr($this->document->saveHTML($body), $bodyTags);
	}

	public function getBody()
	{
		return $this->document
			->getElementsByTagName('body')
			->item(0);
	}

	public function tidyUp()
	{
		$cleaner = new \tidy;
		$cleaner->parseString($this->markup, $this->tidyConfig, 'utf8');
		$cleaner->cleanRepair();

		$this->markup = $cleaner->body()->value;

		$xpath = new \DOMXPath($this->document);

		$tagOverrides = $this->blockList['tagOverrides'];
		$blockedNodes = array_map(
			function($tag) use ($xpath){
				$paths = $xpath->query("//{$tag}");
				$items = [];
				foreach ($paths as $path) {
					$items[] = $path;
				}
				return [
					'name'  => $tag,
					'items' => $items
				];
			},
			$this->blockList['tags']
		);

		$blockedNodes = array_filter(
			$blockedNodes,
			function($node) use ($tagOverrides) {
				return true;
				// foreach ($tagOverrides as $tagOverride) {
				// 	return array_reduce(
				// 		$node['items'],
				// 		function($carry, $nodeItem) use ($tagOverride) {
				// 			if ($carry === false) return $carry;

				// 			$carry = array_reduce(
				// 				$tagOverride,
				// 				function($carry, $override) use ($nodeItem) {
				// 					$nodeItemValue = $nodeItem->getAttribute($override['attribute']);
				// 					if ($nodeItemValue && in_array($nodeItemValue, $override['values'])) {
				// 						$carry = true;
				// 					}
				// 					return $carry;
				// 				},
				// 				false
				// 			);

				// 		},
				// 		true
				// 	);
				// 	foreach ($node['items'] as $nodeItem) {
				// 		$nodeItemValue = $nodeItem->getAttribute($attrName);
				// 		if ($nodeItemValue && in_array($nodeItemValue, $attrValues)) {
				// 			continue;
				// 		}
				// 	}
				// }
			}
		);


		// foreach ($blockedNodes as $tag => $node) {
		// 	if (isset($tagOverrides[$tag])) {
		// 		$nodeAttributes = array_keys($tagOverrides[$tag]);
		// 		foreach ($nodeAttributes as $attribute) {
		// 			$attributeValue = $node->getAttribute($attribute);
		// 			if (in_array($attributeValue, $tagOverrides[$tag][$attribute])) {
		// 				continue;
		// 			}
		// 		}
		// 	}
		// 	$node->parentNode->removeChild($node);
		// }

		$nodes = $xpath->query('//*[@onmouseover]');
		foreach ($nodes as $node) {
			$node->removeAttribute('onmouseover');
		}

		return $this;
	}
}
