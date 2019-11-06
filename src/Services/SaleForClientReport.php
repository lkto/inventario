<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 6/11/2019
 * Time: 8:38 AM
 */

namespace App\Services;

use App\Entity\SaleDetails;
use App\Entity\Sales;

ini_set('memory_limit', '-1');
set_time_limit(0);
class SaleForClientReport
{
    public function generateExcel($sales){
        $excel = [];
        $contador = 0;
        foreach ($sales as $sale){
            /**@var $sale Sales*/
            $excel[$contador]['b1'] = $sale->getCode();
            $excel[$contador]['c1'] = $sale->getUser()->getEmail();
            $excel[$contador]['d1'] = $sale->getClient()->getFirstName().' '.$sale->getClient()->getLastName();
            $excel[$contador]['e1'] = $sale->getDate()->format('Y-m-d H:i:s');
            $vTotal = 0;
            foreach ($sale->getSaleDetails() as $detail){
                /**@var $detail SaleDetails*/

                $vTotal = $vTotal + ($detail->getCount() * $detail->getValueUnit());
            }

            $excel[$contador]['f1'] = $vTotal;

            $contador++;
        }

        $keys = [
            "b1" => "Codigo de factura",
            "c1" => "Vendedor",
            "d1" => "Cliente",
            "e1" => "Fecha",
            "f1" => "Valor Total"
        ];

        $response= array();
        $response['data']=$excel;
        $response['key']=$keys;

        return $response;
    }

}