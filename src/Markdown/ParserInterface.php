<?php

namespace Monyxie\Mdir\Markdown;

interface ParserInterface
{
    public function parse(string $text): ParseResult;
}