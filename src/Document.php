<?php

namespace Lab1521\NeatyHTML;

class Document
{
    protected $doc;

    public function __construct()
    {
        $this->doc = new \DOMDocument('1.0', 'utf-8');
    }

    /**
     * Reads the html markup string and creates a document object
     * @param  string $html HTML markup code
     * @return object class instance
     */
    public function read($html)
    {
        $current = libxml_use_internal_errors(true);
        $this->doc->loadHTML($html);
        $error = libxml_get_last_error();
        libxml_use_internal_errors($current);
        if ($error) {
            libxml_clear_errors();
            throw new NeatyDOMException(trim($error->message).
                ' on line '.$error->line.
                ' and column '.$error->column.' markup.');
        }
        $this->doc->normalizeDocument();

        return $this;
    }

    /**
     * Returns the markup string of the body tag and children.
     *
     * @return string HTML
     */
    public function getBody()
    {
        return $this->doc->getElementsByTagName('body')->item(0);
    }

    /**
     * Tidy up the DomDocument html markup
     * @return void
     */
    public function tidyUp()
    {
        XPath::tidyUp($this->doc);

        return $this->html();
    }

    /**
     * Returns the generated markup string.
     *
     * @return string HTML
     */
    public function html()
    {
        $body = $this->getBody();

        if (!$body) return '';

        return strtr(
            $this->doc->saveHTML($body),
            ['<body>' => '', '</body>' => '']
        );
    }

}
