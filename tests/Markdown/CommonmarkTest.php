<?php


namespace Tests\Markdown;


use Monyxie\Mdir\Markdown\Commonmark;
use Monyxie\Mdir\Markdown\ParserInterface;

class CommonmarkTest extends ParserTest
{
    protected function createSubject(): ParserInterface
    {
        return new Commonmark();
    }
}