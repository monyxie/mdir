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

        foreach (Finder::create()->in($filename)->depth(0)->sortByName(true)->directories() as $subdirectory) {
            /** @var \SplFileInfo $subdirectory */
            $relativePath = $this->jail->resolveAbsolute($subdirectory, true);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $directories [] = [
                'base_name' => basename($subdirectory),
                'absolute_path' => $subdirectory->getPathname(),
                'relative_path' => $relativePath,
                'link' => '/' . $relativePath
            ];
        }

        $patterns = [];
        foreach ($exts as $ext) {
            $patterns [] = '*.' . $ext;
        }
        foreach (Finder::create()->in($filename)->files()->depth(0)->sortByName(true)->name($patterns) as $file) {
            /** @var \SplFileInfo $file */
            $relativePath = $this->jail->resolveAbsolute($file, true);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $files [] = [
                'base_name' => basename($file),
                'absolute_path' => $file->getPathname(),
                'relative_path' => $relativePath,
                'link' => '/' . $relativePath
            ];
        }
        return array($files, $directories);
    }

    public function listUps(string $dir)
    {
        $ups = [];
        while ($up = $this->jail->resolveAbsolute(dirname($dir))) {
            $relativePath = $this->jail->resolveAbsolute($up, true);
            $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $ups[] = [
                'base_name' => basename($up),
                'absolute_path' => $up,
                'relative_path' => $relativePath,
                'link' => $link
            ];

            $dir = $up;
        }
        return array_reverse($ups, true);
    }
}