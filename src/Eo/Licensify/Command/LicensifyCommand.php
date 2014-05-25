<?php

/*
 * This file is part of the Licensify package.
 *
 * (c) Eymen Gunay <eymen@egunay.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eo\Licensify\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Licensify command
 */
class LicensifyCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('licensify')
            ->setDescription('Automatically add license headers to your PHP source files')
            ->addOption('cwd', 'w', InputOption::VALUE_REQUIRED, 'Current working directory', './')
            ->addOption('package', 'p', InputOption::VALUE_REQUIRED, 'Package name', 'Licensify')
            ->addOption('author', 'a', InputOption::VALUE_REQUIRED, 'The author to use.', 'Eymen Gunay <eymen@egunay.com>')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->name('*.php')->in($input->getOption('cwd'));

        $license = $this->getLicenseText($input->getOption('package'), $input->getOption('author'));

        $t = 0;
        foreach ($finder as $file) {
            $tokens = token_get_all(file_get_contents($file->getRealpath()));

            $content = '';
            $afterNamespace = $afterClass = $ignoreWhitespace = false;
            for ($i = 0, $c = count($tokens); $i < $c; $i++) {
                if (!is_array($tokens[$i])) {
                    $content .= $tokens[$i];
                    continue;
                }

                if ($ignoreWhitespace && T_WHITESPACE === $tokens[$i][0]) {
                    continue;
                }
                $ignoreWhitespace = false;

                if (!$afterNamespace && (T_COMMENT === $tokens[$i][0] || T_WHITESPACE === $tokens[$i][0])) {
                    continue;
                }

                if (T_NAMESPACE === $tokens[$i][0]) {
                    $content .= "\n".$license."\n\n";
                    $afterNamespace = true;
                }

                if (!$afterClass && T_COMMENT === $tokens[$i][0]) {
                    $ignoreWhitespace = true;
                    continue;
                }

                if (T_CLASS === $tokens[$i][0]) {
                    $afterClass = true;
                }

                $content .= $tokens[$i][1];
            }

            if ($afterNamespace === false) {
                continue;
            }

            file_put_contents($file->getRealpath(), $content);
            $output->writeln(sprintf('[Modify] <comment>%s</comment>', $file->getRelativePathname()));

            $t++;
        }

        $output->writeln("<info>Command finished successfully. Licensified $t files.</info>");
    }

    /**
     * Get license text
     *
     * @param  string $package
     * @param  string $author
     * @return string
     */
    protected function getLicenseText($package, $author)
    {
        $text = array(
            "/*",
            " * This file is part of the $package package.",
            " *",
            " * (c) $author",
            " *",
            " * For the full copyright and license information, please view the LICENSE",
            " * file that was distributed with this source code.",
            " */"
        );

        return implode(PHP_EOL, $text);
    }
}
