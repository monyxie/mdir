<?php


namespace Monyxie\Mdir\Markdown;


class ParseResult
{
    /**
     * @var string
     */
    public $markup;
    /**
     * @var string
     */
    public $title;

    public function __construct(string $markup, string $title)
    {
        $this->markup = $markup;
        $this->title = $title;
    }
}