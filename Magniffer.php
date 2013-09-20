<?php

namespace Ecg\Magniffer;

use Symfony\Component\Finder\Finder;

class Magniffer
{
    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var array[Inspector]
     */
    protected $inspectors = array();

    /**
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param Inspector $inspector
     * @return $this
     */
    public function addInspector(Inspector $inspector)
    {
        $this->inspectors[] = $inspector;
        return $this;
    }

    /**
     * Run main process of inspection
     */
    public function runInspection()
    {
        $time_start = microtime(true);
        /** @var Inspector $inspector */
        foreach ($this->finder as $file) {
            foreach ($this->inspectors as $inspector) {
                $inspector->setFile($file);
                if ($inspector->canInspect()) {
                    $inspector->parse()->inspect();
                }
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;

        echo "Did in $time seconds\n";
    }
}
