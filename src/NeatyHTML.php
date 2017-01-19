<?php

namespace Lab1521\NeatyHTML;

class NeatyHTML
{
    use Overrides;

    protected $doc;
    protected $markup;

    public function __construct($markup = '')
    {
        Config::reset();
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
        $this->doc = new Document();
        $this->markup = $markup;
        if ($this->markup) {
            $this->doc->read($this->markup);
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
        return $this->doc->html();
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
        $this->markup = $markup ?: $this->markup;

        $this->loadHtml(Tidy::repair($this->markup));

        return $this->doc->tidyUp();
    }
}
