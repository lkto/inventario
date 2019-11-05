<?php
/**
 * Created by PhpStorm.
 * User: ohernandez
 * Date: 8/8/2017
 * Time: 1:08 PM
 */

namespace App\Report;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExcelReportReloaded
{
    private $appName;

    private $name;

    private $titles;

    private $data;

    private $workbook;

    private $activeSheet;

    private $firstColumn = 1;

    private $firstRow = 5;

    public function __construct($appName)
    {
        $this->appName = $appName;

        //date_default_timezone_set('America/Caracas');

        $this->init();

    }

    public function setName($name)
    {
        $this->activeSheet->setCellValue('A2', $name);
        return $this->name = $name;

    }

    public function setTitles(array $titles)
    {
        return $this->titles = $titles;
    }

    public function setData(array $data)
    {
        return $this->data = $data;
    }

    public function init() {

        $this->workbook = new PHPExcel();
        $this->workbook->setActiveSheetIndex(0);
        $this->activeSheet = $this->workbook->getActiveSheet();

        $this->activeSheet->setCellValue('A1', $this->appName);
        $this->activeSheet->setCellValue('A3', 'Date Downloaded: '.date("Y/m/d h:i:s A"));


    }

    private function setHeaders()
    {

        if($this->data) {

        $tableHeading = array(
            'font' => array(
                'bold' => true
                ));

        if($this->titles) {

        }else {

            $pCol = 1;
            $pRow = 5;

            foreach ($this->data[0] as $title => $datum) {

                $title = ucwords(str_replace("_", " ", $title));

                $this->activeSheet->setCellValueByColumnAndRow($pCol, $pRow, $title);
                //$this->activeSheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($pCol))->setAutoSize(true);
                $pCol++;
            }

            $firstColString = PHPExcel_Cell::stringFromColumnIndex($this->firstColumn);
            $lastColString = PHPExcel_Cell::stringFromColumnIndex($pCol-1);
            $this->activeSheet
                ->getStyle($firstColString.$pRow.":".$lastColString.$pRow)
                ->applyFromArray($tableHeading);
        }
        }else {

            $this->activeSheet->setCellValue('B5', 'No data was obtained with the values supplied in the filter');
        }

    }

    private function setCells() {

        $pCol = 1;
        $pRow = 6;

        try {
            foreach ($this->data as $row) {
                $pCol = 1;
                foreach ($row as $value) {

                    $this->activeSheet->setCellValueByColumnAndRow($pCol, $pRow, $value);
                    //dump(PHPExcel_Cell::stringFromColumnIndex($pCol));
                    $pCol++;
                }
                $pRow++;
            }

        }catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            throw $e;
        }

        return ['col' => $pCol, 'row' => $pRow];
    }

    public function write()
    {

        $request = new Request();

        $this->setHeaders();

        if($this->data) $this->setCells();

        $writer = PHPExcel_IOFactory::createWriter($this->workbook, "Excel2007");

        $filename = strtolower(str_replace(" ","_",$this->name));
        $filename = $filename."_".date("m-d-Y_h:i:s_A").'.xlsx';



        ob_start();
        $writer->save("php://output");

        return new Response(
            ob_get_clean(),
            200,
            array(
                'Server' => $request->getHost(),
                'Date' => date(DATE_RFC2822),
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Connection' => 'keep-alive',
                'Cache-Control' => 'private',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Set-Cookie' => 'fileDownload=true; path=/',
            )
        );

    }


}