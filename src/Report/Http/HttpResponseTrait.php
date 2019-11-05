<?php
/**
 * Created by PhpStorm.
 * User: Alberto Patiño
 * Date: 20-08-2015
 * Time: 11:24 AM
 */

namespace App\Report\Http;


use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait HttpResponseTrait {

    /**
     * @param \PHPExcel_Writer_IWriter $writer
     * @param $name
     * @param string $extension
     * @return StreamedResponse
     */
    public function createResponseFromWriter(\PHPExcel_Writer_IWriter $writer, $name, $extension = 'xls')
    {
        // adding headers
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name.'_'.date("m-d-Y_h:i:sa").'.'.$extension
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

} 