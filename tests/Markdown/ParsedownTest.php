<?php

namespace Markdown;

use Monyxie\Mdir\Markdown\Parsedown;
use PHPUnit\Framework\TestCase;

class ParsedownTest extends TestCase
{
    public function testParse()
    {
        $parsedown = new Parsedown();

        list($content, $title, $subtitle) = $parsedown->myParse("title\n===\ncontent\n");
        $this->assertEquals('title', $title);
        $this->assertEquals('', $subtitle);

        list($content, $title, $subtitle) = $parsedown->myParse("title\n===\nsubtitle\n---\ncontent\n");
        $this->assertEquals('title', $title);
        $this->assertEquals('subtitle', $subtitle);

        list($content, $title, $subtitle) = $parsedown->myParse("thisisnotsubtitle\n---\ncontent\n");
        $this->assertEquals('', $title);
        $this->assertEquals('', $subtitle);
    }
}
