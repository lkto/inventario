<?php
/**
 * Created by PhpStorm.
 * User: Alberto Patiño
 * Date: 18-08-2015
 * Time: 10:41 AM
 */

namespace App\Report;

use App\Report\Http\HttpResponseTrait;
use PHPExcel;
use PHPExcel_Cell_DataValidation;
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Cell;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet;

class ExcelExportWithSources
{
    use HttpResponseTrait;

    /**
     * @var PHPExcel
     */
    private $excelObj;

    /**
     * @var
     */
    private $activeWorksheet;

    /**
     * @var int
     */
    private $firstRow = 1;

    /**
     * @var int
     */
    private $firstCol = 0;

    /**
     * @var array
     */
    private $properties;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @param $data
     * @param $headers
     * @param $name
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     */
    public function export($data, $headers, $sources, $name)
    {
        $this->excelObj = new \PHPExcel();
        $this->setProperties();
        $this->excelObj->setActiveSheetIndex(0);
        $this->activeWorksheet = $this->excelObj->getActiveSheet();
        $this->createHeaderStyle($headers, $name);
        $writer = $this->setData($data, $headers, $sources, $name);

        return $writer;
    }

    /**
     * @param ExportReportInterface $report
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateReport(ExportReportInterface $report)
    {
        $writer = $this->export($report->getData(), $report->getHeaders(), $report->getName(),'');

        return $this->createResponseFromWriter($writer, $report->getName());
    }

    /**
     * @param $headers
     * @param $name
     */
    public function createHeaderStyle($headers, $name)
    {
        $lightGrey = array(
            'font' => array(
                'bold' => true,
            )
        );
        $tableHeading = array(
            'font' => array(
                'bold' => true,
                'color' => array(
                    'argb' => PHPExcel_Style_Color::COLOR_BLACK)),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );

        $this->activeWorksheet->getDefaultStyle()->applyFromArray(array('font' => array('name' => 'arial','size' => 10)));
        $this->activeWorksheet->getStyle('A1')->applyFromArray(array('font' => array('bold' => true)));
        $this->activeWorksheet->getStyle('A2')->applyFromArray(array('font' => array('bold' => true)));
        $this->activeWorksheet->getStyle('A3')->applyFromArray($lightGrey);
        $this->activeWorksheet->getStyle('A4')->applyFromArray($lightGrey);

        //SHEET HEADINGS
        $this->activeWorksheet->setCellValue('A1', $this->properties['application_name']);
        $this->activeWorksheet->setCellValue('A2', $name);
        $this->activeWorksheet->setCellValue('A3', 'Date ' . date("m-d-Y h:i:sa"));
        //SHEET DATA
        $this->firstRow = 5;
        $this->firstCol = 1;

        //TABLE HEADINGS
        $i = $this->firstCol;
        $this->activeWorksheet->getRowDimension($this->firstRow)->setRowHeight(30);

        foreach ($headers as $key => $value) {
            //Dimensions
            $this->activeWorksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
            $this->activeWorksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($i) . $this->firstRow)->applyFromArray($tableHeading);
            $this->activeWorksheet->setCellValueByColumnAndRow($i++, $this->firstRow, $value);
        }
    }

    /**
     * @param $data
     * @param $headers
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Reader_Exception
     */
    public function setData($data, $headers, $sources, $name)
    {
        $borderStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => 000)
                )
            )
        );

        //TABLE DATA
        $i = $this->firstRow + 1;
        foreach ($data as $dataItem) {
            $j = $this->firstCol;
            foreach ($headers as $key => $value) {
                $this->activeWorksheet->setCellValueByColumnAndRow($j, $i, $dataItem["$key"]);
                $this->activeWorksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($j++) . $i)->applyFromArray($borderStyle);
            }
            $i++;
        }

        $this->excelObj->getActiveSheet()->setTitle($name);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $this->excelObj->setActiveSheetIndex(0);

        $this->setSources($sources,$data);

        // create the writer
        $writer = \PHPExcel_IOFactory::createWriter($this->excelObj, 'Excel2007');

        return $writer;
    }

    function getCellByCoordinates($x,$y){
        return PHPExcel_Cell::stringFromColumnIndex($x).$y;
    }

    /**
     * $sources array mus contain the following indexes
     * 'rowIndex' => (number) index of the row to start placing the data source
     * 'columnHeaderIndex' => (string) letter of the column to start placing the data
     * 'hide' => (boolean) to hide the sheet of not
     * 'sourceSheetName' => (string) name of the sheet
     * 'data' => (array) with the source data for the dropdown list
     *
     * @param $sources
     * @param $data
     * @return PHPExcel_Worksheet
     */
    public function setSources($sources, $data)
    {
        /** loop through the sources array data*/
        foreach($sources as $key => $source){
            /** create the new sheet */
            $objWorkSheet = $this->excelObj->createSheet($key + 1);

            /** insert the data in the new sheet*/
            foreach ($source['data'] as $dataKey => $dataItem) {
                $objWorkSheet->setCellValueByColumnAndRow(0, $dataKey+1, $dataItem);
            }

            /** calculate the size of the data*/
            $sizeOfData = sizeof($source['data']);
            /** set the formula for the dropdown to create*/
            $validation = "'".$source['sourceSheetName']."'".'!$A$1:$A$'.$sizeOfData;

            // here we set the first row and column where we will start creating the dropdown lists in the main sheet for the given column/row range
            $j = $source['rowIndex'];
            foreach ($data as $dataItem) {
                $cell = $source['columnHeaderIndex'].$j;
                $objValidation = $this->excelObj->getActiveSheet()->getCell($cell)->getDataValidation();
                $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
                $objValidation->setAllowBlank(true);
                $objValidation->setShowDropDown(true);
                $objValidation->setFormula1($validation);
                $j++;
            }

            /** hide the data source sheet of the dropdown list */
            if($source['hide'] == true){
                $objWorkSheet->setSheetState(PHPExcel_Worksheet::SHEETSTATE_VERYHIDDEN);
            }

            $objWorkSheet->setTitle($source['sourceSheetName']);
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        return $this->excelObj->setActiveSheetIndex(0);
    }

    public function setProperties()
    {
        $this->excelObj->getProperties()
            ->setCreator($this->properties['creator'])
            ->setTitle($this->properties['title'])
//            ->setLastModifiedBy("")
//            ->setSubject("")
//            ->setDescription("")
//            ->setKeywords("")
//            ->setCategory("")
        ;
    }

}