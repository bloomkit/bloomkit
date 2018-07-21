<?php

namespace Bloomkit\Core\Utilities;

use Bloomkit\Core\Exceptions\NotFoundException;

class CsvParser
{
    private $currentDataset;

    private $headerSkipped;

    private $callback;

    private $counters = [];

    private $columnsPerLine = -1;

    private $delimiter = '|';

    private $enclosedBy = '';

    private $escapedBy = '';

    public $sHeader;

    public function __construct($delimiter = '|', $enclosedBy = '', $escapedBy = '', $columnsPerLine = -1)
    {
        $this->delimiter = $delimiter;
        $this->enclosedBy = $enclosedBy;
        $this->escapedBy = $escapedBy;
        $this->columnsPerLine = $columnsPerLine;
    }

    public function processLinesStart($key, $fileName, $callback)
    {
        $ret = false;

        $this->callback = $callback;

        if (!file_exists($fileName)) {
            throw new NotFoundException('File not found: '.$sFile);
        }
        if (!filesize($fileName)) {
            throw new NotFoundException('File not readable: '.$sFile);
        }
        $hFile = fopen($fileName, 'r');
        if (!$hFile) {
            throw new NotFoundException('File not readable: '.$sFile);
        }
        $this->currentDataset = 0;
        $lineNumber = 0;

        while (!feof($hFile)) {
            $this->currentLine = $lineNumber;
            $lineString = utf8_encode(fgets($hFile));
            $ret = $this->processLine($lineNumber, $lineString);
            ++$lineNumber;
            if ($ret == false) {
                break;
            }
        }

        $this->counters[$key] = $this->currentDataset;
        fclose($hFile);

        return $ret;
    }

    private function processLine($lineNumber, $sLine)
    {
        if ($lineNumber == 0 && !$this->headerSkipped) {
            $this->headerSkipped = true;

            return true;
        }

        $this->sLine = $sLine;
        $this->iLine = $lineNumber;

        if ($this->sHeader != '' && $lineNumber == 0) {
            // skip header
            return true;
        }

        $sLine = rtrim($sLine);
        $iDataset = $this->currentDataset; // dataset count
        $ret = false;

        if ($sLine != '') {
            $aFields = str_getcsv($sLine, $this->delimiter, $this->enclosedBy, $this->escapedBy);

            if ($this->sHeader != '') {
                $aHeaders = str_getcsv($this->sHeader, $sLine, $this->delimiter, $this->enclosedBy, $this->escapedBy);
                foreach ($aFields as $iKey => $sField) {
                    $sHeader = $aHeaders[$iKey];
                    $aAssocFields[$sHeader] = $sField;
                }
            }

            $n = count($aFields);
            $nFields = $this->columnsPerLine;

            if ($this->sHeader != '' && ($n == $nFields || $nFields == -1) && count($aHeaders) == $n) {
                $ret = $this->callback($iDataset, $aAssocFields);
                ++$iDataset;
            } elseif ($this->sHeader == '' && ($n == $nFields || $nFields == -1)) {
                $ret = call_user_func($this->callback, $iDataset, $aFields);
                ++$iDataset;
            } else {
                $this->mythrowAndStopLock(bds_imex_fileexception::EXCEPTION_DATA_FORMATERROR, $lineNumber.', nFields='.$n.' but must be '.$nFields);
            }
        }

        $this->currentDataset = $iDataset;

        return $ret;
    }
}
