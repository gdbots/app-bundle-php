<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class CompileTwigTemplatesCommand extends Command
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        parent::__construct('app:compile-twig-templates');
        $this->twig = $twig;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        foreach ($this->getIterator() as $template) {
            ++$count;
            try {
                $this->twig->loadTemplate($template);
            } catch (\Throwable $e) {
                // problem during compilation, give up
                // might be a syntax error or a non-Twig template
                $output->writeln($e->getMessage());
            }
        }

        $output->writeln(sprintf('Compiled %d twig templates.', number_format($count)));
    }

    private function getIterator(): \ArrayIterator
    {
        $loader = $this->twig->getLoader();
        if (!$loader instanceof FilesystemLoader) {
            throw new \InvalidArgumentException('Expected FilesystemLoader.');
        }

        $templates = [];
        foreach ($loader->getNamespaces() as $namespace) {
            foreach ($loader->getPaths($namespace) as $path) {
                $templates = array_merge($templates, $this->findTemplatesInDirectory($path, $namespace));
            }
        }

        return new \ArrayIterator(array_unique($templates));
    }

    private function findTemplatesInDirectory(string $dir, string $namespace): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $templates = [];
        foreach (Finder::create()->files()->followLinks()->in($dir) as $file) {
            $templates[] = (FilesystemLoader::MAIN_NAMESPACE !== $namespace ? '@' . $namespace . '/' : '') . str_replace('\\', '/', $file->getRelativePathname());
        }

        return $templates;
    }
}
