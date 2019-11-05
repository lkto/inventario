<?php
/**
 * Created by PhpStorm.
 * User: castway
 * Date: 12/7/2016
 * Time: 1:30 PM
 */

namespace App\Report;

use Exception;

class ExcelReader
{
    protected function createReaderForFile($fileName,$readDataOnly = true)
    {
        // Most common case
        $reader = new \PHPExcel_Reader_Excel5();

        $reader->setReadDataOnly($readDataOnly);

        if ($reader->canRead($fileName)) return $reader;

        // Make sure have zip archive
        if (class_exists('ZipArchive'))
        {
            $reader = new \PHPExcel_Reader_Excel2007();

            $reader->setReadDataOnly($readDataOnly);

            if ($reader->canRead($fileName)) return $reader;
        }

        // Note that csv does not actually check for a csv file
        $reader = new \PHPExcel_Reader_CSV();

        if ($reader->canRead($fileName)) return $reader;

        throw new Exception("No Reader found for $fileName");

    }
    public function load($fileName, $sheetIndex = 0, $nullableIndex = 0, $readDataOnly = true)
    {
        $reader = $this->createReaderForFile($fileName,$readDataOnly);
        $reader = $reader->load($fileName);

        $ws = $reader->getSheet($sheetIndex);

        return $this->removeEmptyItems($ws->toArray(),$nullableIndex);
    }

    public function removeEmptyItems($rows, $nullableIndex)
    {
        $arrayCodes = array_column($rows, $nullableIndex);
        $emptyItems = array_filter($arrayCodes, function($value) { return $value == null; });

        $emptyKeys = array_keys($emptyItems);

        foreach ($emptyKeys as $key){
            unset($rows[$key]);
        }
        unset($rows[0]);

        return $rows;
    }
}