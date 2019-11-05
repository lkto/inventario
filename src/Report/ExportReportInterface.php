<?php
/**
 * Created by PhpStorm.
 * User: Alberto Patiño
 * Date: 20-08-2015
 * Time: 01:27 PM
 */

namespace App\Report;


interface ExportReportInterface {

    public function getName();

    public function getData();

    public function getHeaders();
}