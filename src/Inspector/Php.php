<?php

namespace Ecg\Magniffer\Inspector;

use Ecg\Magniffer\Inspector,
    Ecg\Magniffer\Exception\InvalidXpathException,
    Ecg\Magniffer\Report,
    DOMDocument,
    DOMXPath,
    PHPParser_Lexer,
    PHPParser_Parser,
    PHPParser_Serializer_XML,
    SimpleXMLElement;

class Php extends Inspector
{
    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var PHPParser_Parser
     */
    protected $parser;

    /**
     * @var PHPParser_Serializer_XML
     */
    protected $serializer;

    /**
     * @var DOMXPath
     */
    protected $domXpath;

    /**
     * @var array
     */
    protected $contentArray = array();

    /**
     * @param array $patterns
     * @param Report $report
     */
    public function __construct(array $patterns, Report $report)
    {
        parent::__construct($patterns, $report);
        $this->dom        = new DOMDocument();
        $this->parser     = new PHPParser_Parser(new PHPParser_Lexer());
        $this->serializer = new PHPParser_Serializer_XML();
    }

    /**
     * @return bool
     */
    public function canInspect()
    {
        return in_array($this->file->getExtension(), array('php', 'phtml'));
    }

    /**
     * @todo simplify
     * @param $xpath
     * @param SimpleXMLElement $node
     * @param array $pattern
     * @return array
     */
    protected function prepareIssue($xpath, SimpleXMLElement $node, array $pattern)
    {
        $issue = array(
            'message'   => $pattern['message'],
            'inspector' => get_class($this)
        );

        if ($node->xpath('./attribute:startLine/scalar:int/text()')) {
            $issue['start'] = (int)current($node->xpath('./attribute:startLine/scalar:int/text()'));
            $issue['end']   = (int)current($node->xpath('./attribute:endLine/scalar:int/text()'));
        } else {
            $issue['start'] = (int)$node->xpath('preceding::attribute:startLine[1]/scalar:int/text()')[0];
            $issue['end']   = (int)$node->xpath('preceding::attribute:endLine[1]/scalar:int/text()')[0];
        }

        $issue['line']  = $issue['start'] == $issue['end'] ? $issue['start'] : $issue['start'] . '-' . $issue['end'];

        if ($issue['start'] == $issue['end']) {
            $issue['source'] = array(trim($this->contentArray[$issue['start'] - 1]));
        } else {
            $issue['source'] = array_slice($this->contentArray, $issue['start'], $issue['end'] - $issue['start']);
            $issue['source'] = array_map('trim', $issue['source']);
        }

        $numParents = array_filter(explode('//', $xpath));
        if (count($numParents) > 1) {
            $path = str_repeat('../', count($numParents) + 1);
            $issue['parent_start'] = (int)current($node->xpath("{$path}/attribute:startLine/scalar:int/text()"));
            $issue['parent_end']   = (int)current($node->xpath("{$path}/attribute:endLine/scalar:int/text()"));
        }

        if (isset($issue['parent_start']) && isset($issue['parent_end']) &&
            $issue['parent_start'] != $issue['start'] &&
            $issue['parent_end'] != $issue['end']
        ) {
            $issue['source'] = array_merge(
                array(trim($this->contentArray[$issue['parent_start'] - 1]), '...'),
                $issue['source'],
                array('...', trim($this->contentArray[$issue['parent_end'] - 1]))
            );
        }
        $issue['source'] = implode('', $issue['source']);
        return $issue;
    }

    /**
     * @return Inspector
     */
    public function parse()
    {
        $this->contentArray = file($this->file->getRealPath());
        $this->simpleXml = new SimpleXMLElement($this->serializer->serialize($this->parser->parse(implode('', $this->contentArray))));
        return $this;
    }

    /**
     * @throws InvalidXpathException
     * @return Inspector
     */
    public function inspect()
    {
        $this->domXpath = new DOMXPath($this->dom);
        foreach ($this->patterns as $pattern) {
            $xpath = $this->simpleXml->xpath($pattern['xpath']);
            if (!is_array($xpath)) {
                throw new InvalidXpathException(sprintf('Invalid XPath "%s" given.', $pattern['xpath']));
            }
            foreach ($xpath as $node) {
                $this->report->addIssue($this->file->getRealPath(), $this->prepareIssue($pattern['xpath'], $node, $pattern));
            }
        }
        return $this;
    }
}
