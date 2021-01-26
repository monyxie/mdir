<?php


namespace Monyxie\Mdir\Markdown;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Util\Xml;

final class FencedCodeRenderer implements BlockRendererInterface
{
    /**
     * @param FencedCode $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool $inTightList
     *
     * @return HtmlElement
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false)
    {
        if (!($block instanceof FencedCode)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . \get_class($block));
        }

        $attrs = $block->getData('attributes', []);

        $language = null;
        $infoWords = $block->getInfoWords();
        if (\count($infoWords) !== 0 && \strlen($infoWords[0]) !== 0) {
            $language = $infoWords[0];
        }

        if ($language === 'mermaid') {
            $elem = new HtmlElement('div', ['class' => 'mermaid'], Xml::escape($block->getStringContent()));
        } else {
            $attrs['class'] = isset($attrs['class']) ? $attrs['class'] . ' ' : '';
            $attrs['class'] .= 'language-' . $language;
            $elem = new HtmlElement(
                'pre',
                [],
                new HtmlElement('code', $attrs, Xml::escape($block->getStringContent()))
            );
        }
        return $elem;
    }
}
