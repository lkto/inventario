<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 5/11/2019
 * Time: 2:58 PM
 */

namespace App\Services;

use App\Entity\SaleDetails;
use App\Entity\Sales;

ini_set('memory_limit', '-1');
set_time_limit(0);
class PrintInvoice
{
    public function generateExcel(Sales $sale){
        $excel = [];
        $contador = 0;
        $vTotal = 0;
        $detail = $sale->getSaleDetails();
        foreach($detail as $detail){
            /**@var $detail SaleDetails*/
            $total = $detail->getCount() * $detail->getValueUnit();
            $vTotal = $total + $vTotal;
            $excel[$contador]['b1'] = $detail->getCount();
            $excel[$contador]['c1'] = $detail->getProduct()->getName();
            $excel[$contador]['d1'] = $detail->getValueUnit();
            $excel[$contador]['e1'] = $detail->getProduct()->getIva();
            $excel[$contador]['f1'] = $total;
            $contador++;
        }

        $excel[$contador]['b1'] = "";
        $excel[$contador]['c1'] = "Fecha";
        $excel[$contador]['d1'] = $sale->getDate()->format('Y-m-d H:i:s');
        $excel[$contador]['e1'] = "Total";
        $excel[$contador]['f1'] = $vTotal;

        $keys = [
            "b1" => "Cantidad",
            "c1" => "Descripción",
            "d1" => "Valor Unitario",
            "e1" => "Iva",
            "f1" => "Valor Total"
        ];
        $response= array();
        $response['data']=$excel;
        $response['key']=$keys;

        return $response;
    }

}