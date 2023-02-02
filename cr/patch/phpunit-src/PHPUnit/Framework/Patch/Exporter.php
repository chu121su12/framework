<?php

namespace PHPUnit\Framework\Patch;

use SebastianBergmann\Exporter\Exporter as ExporterClass;

trait Exporter
{
    protected $_exporterPatch;

    protected function exporter()/*: Exporter*/
    {
        if (property_exists($this, 'exporter')) {
            if ($this->exporter === null) {
                $this->exporter = new ExporterClass;
            }

            return $this->exporter;
        }

        if ($this->_exporterPatch === null) {
            $this->_exporterPatch = new ExporterClass;
        }

        return $this->_exporterPatch;
    }
}
