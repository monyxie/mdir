<?php


namespace Monyxie\Mdir\Filesystem;


use Symfony\Component\Finder\Finder;

class Lister
{
    /**
     * @var Jail
     */
    private $jail;
    /**
     * @var array
     */
    private $extensions;
    /**
     * @var array
     */
    private $excludedDirs;

    /**
     * Lister constructor.
     * @param Jail $jail
     * @param array $extensions
     * @param array $excludedDirs
     */
    public function __construct(Jail $jail, array $extensions, array $excludedDirs)
    {
        $this->jail = $jail;
        $this->extensions = $extensions;
        $this->excludedDirs = $excludedDirs;
    }

    /**
     * @param string $filename
     * @return array
     */
    public function listDirectory(string $filename): array
    {
        $directories = $files = [];

        $finder = Finder::create()
            ->in($filename)
            ->depth(0)
            ->sortByName(true)
            ->exclude($this->excludedDirs)
            ->directories();
        foreach ($finder as $subdirectory) {
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
        foreach ($this->extensions as $ext) {
            $patterns [] = '*.' . $ext;
        }
        foreach (Finder::create()->in($filename)->files()->depth(0)->sortByName(true)->name($patterns) as $file) {
            /** @var \SplFileInfo $file */
            $relativePath = $this->jail->resolveAbsolute($file, true);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $files [] = [
                'base_name' => basename($file->getPathname()),
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