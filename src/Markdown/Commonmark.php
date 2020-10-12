<?php


namespace Monyxie\Mdir\Markdown;


use League\CommonMark\Block\Element\Heading;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;

class Commonmark implements ParserInterface
{
    public function parse(string $text): ParseResult
    {
        $title = '';
        $environment = Environment::createGFMEnvironment();
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) use (&$title) {
            if (($firstChild = $event->getDocument()->firstChild())
                && $firstChild instanceof Heading
                && $firstChild->getLevel() == 1) {
                $title = $firstChild->getStringContent();
            }
        });
        $converter = new CommonMarkConverter([], $environment);
        $markup = $converter->convertToHtml($text);

        return new ParseResult($markup, $title);
    }
}