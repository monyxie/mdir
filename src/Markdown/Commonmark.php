<?php


namespace Monyxie\Mdir\Markdown;


use League\CommonMark\Block\Element\Heading;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\Normalizer\SlugNormalizer;

class Commonmark implements ParserInterface
{
    public function parse(string $text): ParseResult
    {
        $title = '';
        $environment = Environment::createGFMEnvironment();
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addEventListener(DocumentParsedEvent::class, function (DocumentParsedEvent $event) use (&$title) {
            if (($firstChild = $event->getDocument()->firstChild())
                && $firstChild instanceof Heading
                && $firstChild->getLevel() == 1) {
                $title = $firstChild->getStringContent();
            }
        });
        $config = [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => 'user-content',
                'insert' => 'before',
                'title' => 'Permalink',
                'symbol' => HeadingPermalinkRenderer::DEFAULT_SYMBOL,
                'slug_normalizer' => new SlugNormalizer(),
            ],
        ];
        $converter = new CommonMarkConverter($config, $environment);
        $markup = $converter->convertToHtml($text);

        return new ParseResult($markup, $title);
    }
}