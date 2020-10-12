<?php


namespace Monyxie\Mdir\Markdown;


class Parsedown implements ParserInterface
{
    /**
     * @var \Parsedown
     */
    private $parsedown;

    public function __construct()
    {
        $this->parsedown = new class extends \Parsedown {
            public function myParse($text)
            {
                # make sure no definitions are set
                $this->DefinitionData = array();

                # standardize line breaks
                $text = str_replace(array("\r\n", "\r"), "\n", $text);

                # remove surrounding line breaks
                $text = trim($text, "\n");

                # split text into lines
                $lines = explode("\n", $text);

                # iterate through lines to identify blocks
                list($markup, $title) = $this->myLines($lines);

                # trim line breaks
                $markup = trim($markup, "\n");

                return new ParseResult($markup, $title);
            }

            protected function myLines(array $lines)
            {
                $CurrentBlock = null;

                foreach ($lines as $line) {
                    if (chop($line) === '') {
                        if (isset($CurrentBlock)) {
                            $CurrentBlock['interrupted'] = true;
                        }

                        continue;
                    }

                    if (strpos($line, "\t") !== false) {
                        $parts = explode("\t", $line);

                        $line = $parts[0];

                        unset($parts[0]);

                        foreach ($parts as $part) {
                            $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                            $line .= str_repeat(' ', $shortage);
                            $line .= $part;
                        }
                    }

                    $indent = 0;

                    while (isset($line[$indent]) and $line[$indent] === ' ') {
                        $indent++;
                    }

                    $text = $indent > 0 ? substr($line, $indent) : $line;

                    # ~

                    $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

                    # ~

                    if (isset($CurrentBlock['continuable'])) {
                        $Block = $this->{'block' . $CurrentBlock['type'] . 'Continue'}($Line, $CurrentBlock);

                        if (isset($Block)) {
                            $CurrentBlock = $Block;

                            continue;
                        } else {
                            if ($this->isBlockCompletable($CurrentBlock['type'])) {
                                $CurrentBlock = $this->{'block' . $CurrentBlock['type'] . 'Complete'}($CurrentBlock);
                            }
                        }
                    }

                    # ~

                    $marker = $text[0];

                    # ~

                    $blockTypes = $this->unmarkedBlockTypes;

                    if (isset($this->BlockTypes[$marker])) {
                        foreach ($this->BlockTypes[$marker] as $blockType) {
                            $blockTypes [] = $blockType;
                        }
                    }

                    #
                    # ~

                    foreach ($blockTypes as $blockType) {
                        $Block = $this->{'block' . $blockType}($Line, $CurrentBlock);

                        if (isset($Block)) {
                            $Block['type'] = $blockType;

                            if (!isset($Block['identified'])) {
                                $Blocks [] = $CurrentBlock;

                                $Block['identified'] = true;
                            }

                            if ($this->isBlockContinuable($blockType)) {
                                $Block['continuable'] = true;
                            }

                            $CurrentBlock = $Block;

                            continue 2;
                        }
                    }

                    # ~

                    if (isset($CurrentBlock) and !isset($CurrentBlock['type']) and !isset($CurrentBlock['interrupted'])) {
                        $CurrentBlock['element']['text'] .= "\n" . $text;
                    } else {
                        $Blocks [] = $CurrentBlock;

                        $CurrentBlock = $this->paragraph($Line);

                        $CurrentBlock['identified'] = true;
                    }
                }

                # ~

                if (isset($CurrentBlock['continuable']) and $this->isBlockCompletable($CurrentBlock['type'])) {
                    $CurrentBlock = $this->{'block' . $CurrentBlock['type'] . 'Complete'}($CurrentBlock);
                }

                # ~

                $Blocks [] = $CurrentBlock;

                unset($Blocks[0]);

                # ~

                $markup = $title = '';

                $titleBlock = reset($Blocks);
                if ($titleBlock && in_array(($titleBlock['type'] ?? null), ['SetextHeader', 'Header']) && ($titleBlock['element']['name'] ?? null) === 'h1') {
                    $title = $titleBlock['element']['text'];
                }

                foreach ($Blocks as $Block) {
                    if (isset($Block['hidden'])) {
                        continue;
                    }

                    $markup .= "\n";
                    $markup .= isset($Block['markup']) ? $Block['markup'] : $this->element($Block['element']);
                }

                $markup .= "\n";

                # ~

                return [$markup, $title];
            }
        };
    }

    public function parse(string $text): ParseResult
    {
        return $this->parsedown->myParse($text);
    }
}