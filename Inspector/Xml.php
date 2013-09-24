<?php

namespace Ecg\Magniffer\Inspector;

use Ecg\Magniffer\Inspector,
    Ecg\Magniffer\Report,
    DOMDocument,
    DOMXPath;

class Xml extends Inspector
{
    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var DOMXPath
     */
    protected $domXpath;

    /**
     * @param array $patterns
     * @param Report $report
     */
    public function __construct(array $patterns, Report $report)
    {
        parent::__construct($patterns, $report);
        $this->dom = new DOMDocument();
    }

    /**
     * @return bool
     * @todo use Magento Finder component, like so: $file->isConfigXml()
     */
    public function canInspect()
    {
        return $this->file->getFilename() == 'config.xml';
    }

    /**
     * @return Inspector
     */
    public function parse()
    {
        $this->dom->loadXML(file_get_contents($this->file->getRealPath()));
        return $this;
    }

    /**
     * @return Inspector
     */
    public function inspect()
    {
        $this->domXpath = new DOMXPath($this->dom);
        foreach ($this->patterns as $pattern) {
            foreach ($this->domXpath->query($pattern['xpath']) as $node) {
                $this->report->addIssue($this->file->getRealPath(), array(
                    'line'      => $node->getLineNo(),
                    'source'    => $node->C14N(),
                    'message'   => $pattern['message'],
                    'inspector' => get_class($this)
                ));
            }
        }
        return $this;
    }
}
