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
use PHPExcel_Style_Border;
use PHPExcel_Style_Color;
use PHPExcel_Style_Fill;
use PHPExcel_Cell;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet_Drawing;


class ExcelExport
{
    use HttpResponseTrait;

    /**
     * @var
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
     * @var
     */
    private $objDrawing ;

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
    public function export($data, $headers, $name)
    {
        $this->excelObj = new \PHPExcel();
        $this->objDrawing = new \PHPExcel_Worksheet_Drawing();
        $this->setProperties();
        $this->excelObj->setActiveSheetIndex(0);
        $this->activeWorksheet = $this->excelObj->getActiveSheet();
        $this->createHeaderStyle($headers, $name);
        $writer = $this->setData($data, $headers, $name);
        

        return $writer;
    }

    /**
     * @param ExportReportInterface $report
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateReport(ExportReportInterface $report)
    {
        $writer = $this->export($report->getData(), $report->getHeaders(), $report->getName());

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
                'color' => array(
                    'rgb' => '9D9D9D'
                )
            )
        );

        $tableHeading = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array(
                    'rgb' => '014886'
                )
            ),
            'font' => array(
                'bold' => true,
                'color' => array(
                    'argb' => PHPExcel_Style_Color::COLOR_WHITE)),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => 333)
                )
            )
        );
        
        
        
        $this->objDrawing->setName('Logo');
        $this->objDrawing->setDescription('Logo');
        $logo =  realpath($this->properties['path'] . '/../public/assets/img/logo_final_2.png'); // Provide path to your logo file
        $this->objDrawing->setPath($logo);
        $this->objDrawing->setOffsetX(8);    // setOffsetX works properly
        $this->objDrawing->setOffsetY(300);  //setOffsetY has no effect
        $this->objDrawing->setCoordinates('A1');
        $this->objDrawing->setHeight(75); // logo height
        $this->objDrawing->setWorksheet($this->activeWorksheet);

        $this->activeWorksheet->getDefaultStyle()->applyFromArray(array('font' => array('name' => 'arial','size' => 10)));
        $this->activeWorksheet->getStyle('C2')->applyFromArray(array('font' => array('bold' => true, 'color' => array('rgb' => '014886'))));
        $this->activeWorksheet->getStyle('C3')->applyFromArray(array('font' => array('bold' => true)));
        $this->activeWorksheet->getStyle('C4')->applyFromArray($lightGrey);
        $this->activeWorksheet->getStyle('C6')->applyFromArray($lightGrey);

        //SHEET HEADINGS
        $this->activeWorksheet->setCellValue('C2', $this->properties['application_name']);
        $this->activeWorksheet->setCellValue('C3', $name);
        $this->activeWorksheet->setCellValue('C4', 'Date ' . date("m-d-Y"));
        //SHEET DATA
        $this->firstRow = 6;
        $this->firstCol = 2;

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
    public function setData($data, $headers, $name)
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
                $this->activeWorksheet->setCellValueByColumnAndRow($j, $i, $dataItem[$key]);
                $this->activeWorksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($j++) . $i)->applyFromArray($borderStyle);
            }
            $i++;
        }

        $this->excelObj->getActiveSheet()->setTitle($name);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $this->excelObj->setActiveSheetIndex(0);

        // create the writer
        $writer = \PHPExcel_IOFactory::createWriter($this->excelObj, 'Excel5');

        return $writer;
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