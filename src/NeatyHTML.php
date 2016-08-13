<?php

namespace Lab1521\NeatyHTML;

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
        'indent' => true,
        'output-html' => true,
        'drop-font-tags' => true,
        'clean' => true,
        'merge-spans' => true,
        'drop-proprietary-attributes' => true,
        'show-body-only' => true,
    ];

    public function __construct($markup = '')
    {
        $this->loadHtml($markup);
    }

    /**
     * Loads HTML markup to DOMDocument.
     *
     * @param string $markup HTML markup
     *
     * @return object $this
     */
    public function loadHtml($markup = '')
    {
        $this->markup = $markup;
        $this->document = new \DOMDocument('1.0', 'utf-8');

        if ($this->markup) {
            $current = libxml_use_internal_errors(true);
            @$this->document->loadHTML($this->markup);
            $error = libxml_get_last_error();
            libxml_use_internal_errors($current);
            if ($error) {
                libxml_clear_errors();
                throw new NeatyXMLError($error->message.
                    ' on line '.$error->line.
                    ' and column '.$error->column.' markup.');
            }
            $this->document->normalizeDocument();
        }

        return $this;
    }

    /**
     * Returns the generated markup string.
     *
     * @return string HTML
     */
    public function html()
    {
        $body = $this->getBody();

        if (!$body) {
            return '';
        }

        $bodyTags = array('<body>' => '', '</body>' => '');

        return strtr($this->document->saveHTML($body), $bodyTags);
    }

    /**
     * Returns the markup string of the body tag and children.
     *
     * @return string HTML
     */
    public function getBody()
    {
        return $this->document
            ->getElementsByTagName('body')
            ->item(0);
    }

    /**
     * Custom tag overrides to allow blocked tags to display.
     *
     * @param array $overrides Collection of tag overrides
     *
     * @return array
     */
    public function tagOverrides($overrides = [])
    {
        return $this->setupConfigKeys('tagOverrides', $overrides);
    }

    /**
     * Blocked tags to delete from document.
     *
     * @param array $tags Collection of tags
     *
     * @return array
     */
    public function blockedTags($tags = [])
    {
        return $this->setupConfigKeys('tags', $tags);
    }

    /**
     * Blocked tag attributes to delete from document.
     *
     * @param array $attributes Collection of tag attributes
     *
     * @return array
     */
    public function blockedAttributes($attributes = [])
    {
        return $this->setupConfigKeys('attr', $attributes);
    }

    /**
     * Evaluates a tag with its attribute values from tag overrides.
     *
     * @param string $tag            Tag name
     * @param string $attributeName  Tag attribute
     * @param string $attributeValue Tag attribute value
     *
     * @return bool TRUE when attribute values passes overrides
     */
    public function checkTagAttribute($tag, $attributeName, $attributeValue = '')
    {
        $tagOverrides = $this->tagOverrides();

        if (!isset($tagOverrides[$tag])) {
            return false;
        }

        $allowedValues = array_reduce(
            $tagOverrides[$tag],
            function ($carry, $attributes) use ($attributeName) {
                if ($attributes['attribute'] === $attributeName) {
                    return $carry + $attributes['values'];
                }
            },
            []
        );

        $protocols = array('http:' => '', 'https:' => '');
        $attributeValue = strtr($attributeValue, $protocols);

        if (!$attributeValue) {
            return false;
        }

        return array_reduce(
            $allowedValues,
            function ($carry, $allowed) use ($attributeValue) {
                if ($carry === true) {
                    return $carry;
                }
                $foundAtPosition = strpos($attributeValue, $allowed);

                return $foundAtPosition === 0;
            },
            false
        );
    }

    /**
     * Check blocked nodes array for later removal.
     *
     * @param array $node Collection of blocked tags
     *
     * @return bool TRUE when an element is blocked for removal
     */
    public function checkBlockedNodes($carry, $node)
    {
        if (!$node['elements']) {
            return $carry;
        }

        $attributes = $node['attributes'];

        $elements = array_reduce(
            $node['elements'],
            function ($carryElement, \DOMElement $element) use ($attributes) {
                $isBlocked = true;
                foreach ($attributes as $attribute) {
                    $hasFailed = !$this->checkTagAttribute($element->tagName, $attribute, $element->getAttribute($attribute));
                    if ($isBlocked) {
                        $isBlocked = $hasFailed;
                    }
                }

                if ($isBlocked) {
                    $carryElement[] = $element;
                }

                return $carryElement;
            },
            []
        );

        return array_merge($carry, $elements);
    }

    /**
     * Remove unwanted tags.
     *
     * @param object $xpath \DOMXPath instance
     */
    public function tidyUpTags($xpath)
    {
        $tagOverrides = $this->tagOverrides();
        $blockedNodes = array_map(
            function ($tag) use ($xpath, $tagOverrides) {
                $paths = $xpath->query("//{$tag}");
                $elements = [];
                foreach ($paths as $path) {
                    $elements[] = $path;
                }

                $attributes = [];
                if (isset($tagOverrides[$tag])) {
                    $attributes = array_map(
                        function ($attr) {
                            return $attr['attribute'];
                        },
                        $tagOverrides[$tag]
                    );
                }

                return [
                    'name' => $tag,
                    'attributes' => $attributes,
                    'elements' => $elements,
                ];
            },
            $this->blockedTags()
        );

        $blockedNodes = array_reduce(
            $blockedNodes,
            array($this, 'checkBlockedNodes'),
            []
        );

        array_walk(
            $blockedNodes,
            function (\DOMElement $node) {
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        );
    }

    /**
     * Remove unwanted tag attributes.
     *
     * @param object $xpath \DOMXPath instance
     */
    public function tidyUpAttributes($xpath)
    {
        $blockedAttributes = $this->blockedAttributes();
        array_walk(
            $blockedAttributes,
            function ($attributeName) use ($xpath) {
                $nodes = $xpath->query("//*[@{$attributeName}]");
                foreach ($nodes as $node) {
                    $node->removeAttribute($attributeName);
                }
            }
        );
    }

    /**
     * Cleans up and remove unwanted tags and attributes.
     *
     * @param string $markup HTML markup
     *
     * @return string HTML
     */
    public function tidyUp($markup = '')
    {
        if ($markup) {
            $this->loadHtml($markup);
        }
        $cleaner = tidy_parse_string($this->markup, $this->tidyConfig, 'UTF8');
        $cleaner->cleanRepair();
        $this->loadHtml($cleaner->body()->value);

        $xpath = new \DOMXPath($this->document);
        $this->tidyUpTags($xpath);
        $this->tidyUpAttributes($xpath);

        return $this->html();
    }

    /**
     * Loads configuration keys from relative file.
     *
     * @param string $key        Configuration file types
     * @param array  $moreConfig Additional array configurations
     *
     * @return array
     */
    protected function setupConfigKeys($key, $moreConfig)
    {
        if (!is_array($moreConfig)) {
            $moreConfig = [];
        }

        if ($this->blockList[$key] && !$moreConfig) {
            return $this->blockList[$key];
        }

        $configFile = require "config/{$key}.php";
        $this->blockList[$key] = array_merge($configFile, $moreConfig);

        return $this->blockList[$key];
    }
}
