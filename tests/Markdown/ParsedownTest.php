<?php


namespace Tests\Markdown;


use Monyxie\Mdir\Markdown\Parsedown;
use Monyxie\Mdir\Markdown\ParserInterface;

class ParsedownTest extends ParserTest
{
    protected function createSubject(): ParserInterface
    {
        return new Parsedown();
    }
}