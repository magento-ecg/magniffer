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

    public function __construct()
    {
        $this->tableHelper = new TableHelper();
        $this->config['displayed-columns'] = array(
            'message' => 'Message',
            'line'    => 'Line',
            'source'  => 'Source',
        );
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
                $this->tableHelper->addRow(array_intersect_key($issue, $this->config['displayed-columns']));
            }
            $this->tableHelper->render($output);
            $this->tableHelper->setRows(array());
        }
    }
}
