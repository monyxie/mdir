<?php


namespace Monyxie\Mdir\Http;


use Monyxie\Mdir\Filesystem\Jail;
use Monyxie\Mdir\Filesystem\Lister;
use Monyxie\Mdir\Markdown\ParserInterface as MarkdownParser;
use Monyxie\Mdir\ViewModel\ShowViewModel;
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
     * @var MarkdownParser
     */
    private $markdown;

    /**
     * Controller constructor.
     * @param Lister $lister
     * @param Jail $jail
     * @param EngineInterface $template
     * @param MarkdownParser $markdown
     * @param $config
     */
    public function __construct(
        Lister $lister,
        Jail $jail,
        EngineInterface $template,
        MarkdownParser $markdown,
        $config
    ) {
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
    public function show(Request $request, array $params): Response
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
            if (!$this->isDirExcluded($path)) {
                $file = $path . '/index.md';
                $dir = $path;

                if (is_file($file)) {
                    $relativePath = $this->jail->resolveAbsolute($file, true);
                    $link = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                    return RedirectResponse::create($link);
                }
            } else {
                throw new ResourceNotFoundException();
            }
        }

        list($files, $directories) = $this->lister->listDirectory($dir);
        $files = array_map(
            function ($path) use ($file) {
                return array_merge(
                    $path,
                    [
                        'is_current' => $path['absolute_path'] === $file
                    ]
                );
            },
            $files
        );

        $ups = $this->lister->listUps($dir);

        if (is_file($file)) {
            list($content, $title) = $this->renderFile($file);
        } else {
            $content = $title = '';
        }

        $viewModel = new ShowViewModel();
        $viewModel->app_name = $this->config['app_name'];
        $viewModel->code_highlight_style = $this->config['code_highlight_style'];
        $viewModel->title = $title;
        $viewModel->files = $files;
        $viewModel->directories = $directories;
        $viewModel->content = $content;
        $viewModel->ups = $ups;

        return new Response($this->template->render('show.php', ['viewModel' => $viewModel,]));
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
        ['extension' => $extension, 'basename' => $basename] = pathinfo($filename);

        if (in_array($extension, $this->config['markdown_extensions'])) {
            $parseResult = $this->markdown->parse(file_get_contents($filename));
            return [$parseResult->markup, $parseResult->title ?: $basename];
        }

        if (in_array($extension, $this->config['extra_extensions'])) {
            $markup = '<pre><code>' . htmlspecialchars(file_get_contents($filename)) . '</code></pre>';
        }

        return [$markup ?? '', $basename];
    }

    private function isBinaryFile(string $path)
    {
        return in_array(pathinfo($path, PATHINFO_EXTENSION), $this->config['binary_extensions']);
    }

    private function isDirExcluded(string $path)
    {
        return in_array(basename($path), $this->config['excluded_dirs']);
    }
}