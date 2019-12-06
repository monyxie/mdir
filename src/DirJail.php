<?php


namespace Monyxie\Mdir;


class DirJail
{
    private $basePath;

    /**
     * Traverser constructor.
     * @param $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = realpath($basePath) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $userPath
     * @param bool $returnRelative
     * @return bool|string
     */
    public function resolveAbsolute(string $userPath, bool $returnRelative = false)
    {
        $realUserPath = realpath($userPath);

        if ($realUserPath === false || (strpos($realUserPath, $this->basePath) !== 0 && $realUserPath !== realpath($this->basePath))) {
            return false;
        }

        if ($returnRelative) {
            return mb_substr($realUserPath, mb_strlen($this->basePath));
        }

        return $realUserPath;
    }

    /**
     * @param string $path
     * @param bool $returnRelative
     * @return bool|string
     */
    public function resolveRelative(string $path, bool $returnRelative = false)
    {
        $path = $this->basePath . $path;
        return $this->resolveAbsolute($path, $returnRelative);
    }
}