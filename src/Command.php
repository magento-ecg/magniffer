<?php

namespace Ecg\Magniffer;

use Ecg\Magniffer\Inspector\Xml as InspectorXml,
    Ecg\Magniffer\Inspector\Php as InspectorPhp,
    Symfony\Component\Console\Command\Command as SymfonyCommand,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Finder\Finder,
    SplFileInfo,
    Symfony\Component\Yaml\Yaml;

class Command extends SymfonyCommand
{
    protected function configure()
    {
        $this->setName('mgf')
            ->setDescription('Magniffer Code Inspection Tool')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to code')
            ->addOption('patterns-dir', null, InputOption::VALUE_OPTIONAL, 'Path to patterns directory', __DIR__ . '/../' . 'patterns')
            ->addOption('show-source', null, InputOption::VALUE_NONE, 'Show source code snippet in the report');
    }

    /**
     * @param $path
     * @param $extensions
     * @return Finder
     */
    protected function getFileIterator($path, $extensions)
    {
        $fileInfo = new SplFileInfo($path);
        $finder   = new Finder();
        if ($fileInfo->isFile()) {
            return $finder->append(array($fileInfo));
        }
        return $finder->files()->in($path)->name('/\.(' . implode('|', $extensions) . ')$/i');
    }

    /**
     * @param $patternsDir
     * @return array
     */
    protected function preparePatterns($patternsDir)
    {
        $yaml = new Yaml();
        $patterns = array('xml' => array(), 'php' => array());
        foreach ($this->getFileIterator($patternsDir, array('yml')) as $file) {
            foreach ($yaml->parse($file) as $pattern) {
                if (array_key_exists('inspector', $pattern)) {
                    $patterns[$pattern['inspector']][] = $pattern;
                }
            }
        }
        return $patterns;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $report    = new Report(array('show-source' => $input->getOption('show-source')));
        $magniffer = new Magniffer($this->getFileIterator($input->getArgument('path'), array('php', 'xml')));
        $patterns  = $this->preparePatterns($input->getOption('patterns-dir'));

        $magniffer->addInspector(new InspectorXml($patterns['xml'], $report))
            ->addInspector(new InspectorPhp($patterns['php'], $report))
            ->runInspection();

        $report->render($output);
    }
}
