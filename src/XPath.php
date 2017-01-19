<?php
namespace Lab1521\NeatyHTML;

class XPath
{
    use Overrides;

    protected $doc;
    protected $dom;
    public function __construct(\DOMDocument $doc)
    {
        $this->doc = $doc;
        $this->dom = new \DOMXPath($this->doc);
    }

    /**
     *  Tidy up the DomDocument html markup
     * @param  \DOMDocument $doc Document HTML object
     * @return void
     */
    public static function tidyUp(\DOMDocument $doc)
    {
        $instance = new static($doc);
        $instance->tidyUpTags();
        $instance->tidyUpAttributes();
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
    public function tidyUpTags()
    {
        $xpath = $this->dom;
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
    public function tidyUpAttributes()
    {
        $xpath = $this->dom;
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
}
