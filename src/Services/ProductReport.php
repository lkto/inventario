<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 6/11/2019
 * Time: 9:15 AM
 */

namespace App\Services;

use App\Entity\Product;

ini_set('memory_limit', '-1');
set_time_limit(0);
class ProductReport
{
    public function generateExcel($products){
        $excel = [];
        $contador = 0;

        foreach ($products as $product){
            /**@var $product Product*/
            $excel[$contador]['b1'] = $product->getCategory()->getName();
            $excel[$contador]['c1'] = $product->getCategory()->getCode().' - '.$product->getCode();
            $excel[$contador]['d1'] = $product->getName();
            $excel[$contador]['e1'] = $product->getValue();
            $excel[$contador]['f1'] = $product->getIva();
            $excel[$contador]['g1'] = $product->getStock();
            $contador++;
        }

        $keys = [
            "b1" => "Categoria",
            "c1" => "Codigo",
            "d1" => "Nombre",
            "e1" => "Valor",
            "f1" => "Iva",
            "g1" => "Stock"
        ];

        $response= array();
        $response['data']=$excel;
        $response['key']=$keys;

        return $response;
    }

}