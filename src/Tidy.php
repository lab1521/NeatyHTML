<?php

namespace Lab1521\NeatyHTML;

class Tidy
{
    protected $config = [
        'clean'                       => true,
        'indent'                      => true,
        'output-html'                 => true,
        'merge-spans'                 => true,
        'show-body-only'              => true,
        'drop-font-tags'              => true,
        'drop-proprietary-attributes' => true,
    ];

    public function cleanAndRepair($html)
    {
        $cleaner = tidy_parse_string($html, $this->config, 'UTF8');
        $cleaner->cleanRepair();
        return $cleaner->body()->value;
    }

    public static function repair($html)
    {
        return (new static())->cleanAndRepair($html);
    }
}
