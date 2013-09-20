<?php

namespace Ecg\Magniffer;

class Report
{
    /**
     * @var array
     */
    public $issues = array();

    public function output()
    {
        var_dump($this->issues);
    }
}
