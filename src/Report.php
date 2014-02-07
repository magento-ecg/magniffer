<?php

namespace Ecg\Magniffer;

use Symfony\Component\Console\Helper\TableHelper,
    Symfony\Component\Console\Output\OutputInterface;

class Report
{
    /**
     * @var array
     */
    public $issues = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var TableHelper
     */
    protected $tableHelper;

    /**
     * @todo use config object
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->tableHelper = new TableHelper();
        $this->config['displayed-columns'] = array(
            'line'    => 'Line',
            'message' => 'Message',
        );
        if (!empty($params['show-source'])) {
            $this->config['displayed-columns']['source'] = 'Source';
        }
    }

    /**
     * @param $file
     * @param $data
     */
    public function addIssue($file, $data)
    {
        $this->issues[$file][] = $data;
    }

    /**
     * @param $issue
     */
    protected function renderIssue($issue)
    {
        $this->tableHelper->addRow(array_intersect_key(array_merge($this->config['displayed-columns'], $issue),
            $this->config['displayed-columns']));
    }

    /**
     * @param $issue
     */
    protected function renderIssueWithSource($issue)
    {
        $source = $issue['source'];
        if (!is_array($source)) {
            $source = explode("\n", $source);
        }
        if (count($source) > 5) $source = array_slice($source, 0, 5);
        array_walk($source, function (&$item) {
            $item = trim($item);
            $item = strlen($item) > 50 ? substr($item, 0, 47) . '...' : $item;
        });
        $rendered = false;
        foreach ($source as $sourceRow) {
            $issue['source'] = $sourceRow;
            $this->renderIssue($issue);
            if (!$rendered) {
                array_walk($issue, function (&$item) {
                    $item = '';
                });
                $rendered = true;
            }
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function render(OutputInterface $output)
    {
        if (empty($this->issues)) {
            return;
        }

        foreach ($this->issues as $file => $issues) {
            $output->writeln(PHP_EOL . $file);
            $this->tableHelper->setHeaders($this->config['displayed-columns']);
            foreach ($issues as $issue) {
                if (empty($this->config['displayed-columns']['source']) || empty($issue['source'])) {
                    $this->renderIssue($issue);
                } else {
                    $this->renderIssueWithSource($issue);
                }
            }
            $this->tableHelper->render($output);
            $this->tableHelper->setRows(array());
        }
    }
}
