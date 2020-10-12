<?php

namespace Tests\Markdown;

use Monyxie\Mdir\Markdown\ParserInterface;
use PHPUnit\Framework\TestCase;

abstract class ParserTest extends TestCase
{
    /**
     * @var ParserInterface
     */
    private $parser;

    protected function setUp(): void
    {
        $this->parser = $this->createSubject();
    }

    public function testParse()
    {
        $parseResult = $this->parser->parse("title\n===\ncontent\n");
        $this->assertEquals('title', $parseResult->title);

        $parseResult = $this->parser->parse("# title\ncontent\n");
        $this->assertEquals('title', $parseResult->title);

        $parseResult = $this->parser->parse("## nottitle\ncontent\n");
        $this->assertEquals('', $parseResult->title);
    }

    /**
     * @return ParserInterface
     */
    abstract protected function createSubject(): ParserInterface;
}
