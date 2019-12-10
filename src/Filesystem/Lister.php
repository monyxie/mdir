<?php


namespace Monyxie\Mdir\Filesystem;


use Symfony\Component\Finder\Finder;

class Lister
{
    /**
     * @var Jail
     */
    private $jail;
    private $markdownExtensions;
    private $extraExtensions;

    /**
     * Lister constructor.
     * @param Jail $jail
     * @param $markdownExtensions
     * @param $extraExtensions
     */
    public function __construct(Jail $jail, $markdownExtensions, $extraExtensions)
    {
        $this->jail = $jail;
        $this->markdownExtensions = $markdownExtensions;
        $this->extraExtensions = $extraExtensions;
    }

    /**
     * @param string $filename
     * @return array
     */
    public function listDirectory(string $filename): array
    {
        $exts = array_merge($this->markdownExtensions, $this->extraExtensions);
        $directories = $files = [];

        foreach (Finder::create()->in($filename)->depth(0)->directories() as $subdirectory) {
            $relativePath = $this->jail->resolveAbsolute($subdirectory, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $directories [basename($subdirectory)] = $link;
        }

        $patterns = [];
        foreach ($exts as $ext) {
            $patterns [] = '*.' . $ext;
        }
        foreach (Finder::create()->in($filename)->files()->depth(0)->name($patterns) as $file) {
            $relativePath = $this->jail->resolveAbsolute($file, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $files [basename($file)] = $link;
        }
        return array($files, $directories);
    }

    public function listUps(string $dir)
    {
        $ups = [];
        while ($up = $this->jail->resolveAbsolute(dirname($dir))) {
            $relativePath = $this->jail->resolveAbsolute($up, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($relativePath !== '') {
                $ups[basename($up)] = $link;
            } else {
                $ups['home'] = $link;
            }
            $dir = $up;
        }
        return array_reverse($ups, true);
    }
}