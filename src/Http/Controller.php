<?php


namespace Monyxie\Mdir\Http;


use Colors\RandomColor;
use Monyxie\Mdir\Filesystem\Jail;
use Monyxie\Mdir\Filesystem\Lister;
use Monyxie\Mdir\Markdown\Parsedown;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Templating\EngineInterface;

class Controller
{
    private $lister;
    /**
     * @var Jail
     */
    private $jail;
    /**
     * @var array
     */
    private $config;
    /**
     * @var EngineInterface
     */
    private $template;
    /**
     * @var Parsedown
     */
    private $markdown;

    /**
     * Controller constructor.
     * @param Lister $lister
     * @param Jail $jail
     * @param EngineInterface $template
     * @param \Parsedown $markdown
     * @param $config
     */
    public function __construct(Lister $lister, Jail $jail, EngineInterface $template, \Parsedown $markdown, $config)
    {
        $this->lister = $lister;
        $this->jail = $jail;
        $this->config = $config;
        $this->template = $template;
        $this->markdown = $markdown;
    }

    /**
     * @param Request $request
     * @param array $params
     * @return Response
     */
    public function show(Request $request, array $params)
    {
        return $this->showPath($params['path'] ?? '');
    }

    /**
     * @param $path
     * @return Response
     */
    private function showPath($path): Response
    {
        $path = $this->resolvePath($path ?? '');
        if (!$path) {
            throw new ResourceNotFoundException();
        }

        if (is_file($path)) {
            if ($this->isBinaryFile($path)) {
                return new BinaryFileResponse($path);
            }
            $file = $path;
            $dir = dirname($file);
        } else {
            $file = $path . '/index.md';
            $dir = $path;

            if (is_file($file)) {
                $relativePath = $this->jail->resolveAbsolute($file, true);
                $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                return RedirectResponse::create($link);
            }
        }

        list($files, $directories) = $this->lister->listDirectory($dir);
        $files = array_map(function ($path) use ($file) {
            return array_merge($path, [
                'is_current' => $path['absolute_path'] === $file
            ]);
        }, $files);

        $ups = $this->lister->listUps($dir);

        if (is_file($file)) {
            list($content, $title, $subtitle) = $this->renderFile($file);
        } else {
            $content = $title = $subtitle = '';
        }

        $params = [
            'app_name' => $this->config['app_name'],
            'files' => $files,
            'directories' => $directories,
            'content' => $content,
            'title' => $title,
            'subtitle' => $subtitle,
            'ups' => $ups,
        ];

        return new Response($this->template->render('show.php', $params));
    }

    /**
     * @param $path
     * @return false|string
     */
    private function resolvePath($path)
    {
        return $this->jail->resolveRelative($path);
    }


    /**
     * @param string $filename
     * @return array
     */
    private function renderFile(string $filename): array
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array($extension, $this->config['markdown_extensions'])) {
            return $this->markdown->myParse(file_get_contents($filename));
        }

        if (in_array($extension, $this->config['extra_extensions'])) {
            $markup = '<pre><code>' . htmlspecialchars(file_get_contents($filename)) . '</code></pre>';
        }

        return [$markup ?? '', '', '',];
    }

    private function getColor($path)
    {
        $basename = basename($path);
        $dot = strrpos($basename, '.');
        $ext = $dot === false ? '' : strtolower(substr($basename, $dot));
        return RandomColor::one(['luminosity' => 'dark', 'prng' => function ($a, $b) use ($ext) {
            srand(crc32($ext) + 7);
            return rand($a, $b);
        }]);
    }

    private function isBinaryFile(string $path)
    {
        return in_array(pathinfo($path, PATHINFO_EXTENSION), $this->config['binary_extensions']);
    }
}