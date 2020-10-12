<?php

namespace Markdown;

use Monyxie\Mdir\Markdown\Parsedown;
use PHPUnit\Framework\TestCase;

class ParsedownTest extends TestCase
{
    public function testParse()
    {
        $parsedown = new Parsedown();

        $parseResult = $parsedown->parse("title\n===\ncontent\n");
        $this->assertEquals('title', $parseResult->title);
        $this->assertEquals('', $parseResult->subtitle);

        $parseResult = $parsedown->parse("title\n===\nsubtitle\n---\ncontent\n");
        $this->assertEquals('title', $parseResult->title);
        $this->assertEquals('subtitle', $parseResult->subtitle);

        $parseResult = $parsedown->parse("thisisnotsubtitle\n---\ncontent\n");
        $this->assertEquals('', $parseResult->title);
        $this->assertEquals('', $parseResult->subtitle);
    }
}
