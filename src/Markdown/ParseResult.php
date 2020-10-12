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
    /**
     * @var string
     */
    public $subtitle;

    public function __construct(string $markup, string $title, string $subtitle)
    {
        $this->markup = $markup;
        $this->title = $title;
        $this->subtitle = $subtitle;
    }
}