<?php

namespace Ecg\Magniffer;

use SplFileInfo;

abstract class Inspector
{
    /**
     * @var SplFileInfo
     */
    protected $file;

    /**
     * @var array
     */
    protected $patterns = array();

    /**
     * @var Report
     */
    protected $report;

    /**
     * @param array $patterns
     * @param Report $report
     */
    public function __construct(array $patterns, Report $report)
    {
        $this->patterns = $patterns;
        $this->report   = $report;
    }

    /**
     * @param SplFileInfo $file
     * @return $this
     */
    public function setFile(SplFileInfo $file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return bool
     */
    abstract function canInspect();

    /**
     * @return Inspector
     */
    abstract function parse();

    /**
     * @return Inspector
     */
    abstract function inspect();
}
