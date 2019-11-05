<?php
/**
 * Created by PhpStorm.
 * User: Alberto Patiño
 * Date: 20-08-2015
 * Time: 02:29 PM
 */

namespace App\Report;


class NotFoundException extends \Exception{

    function __toString()
    {
        return $this->getMessage();
    }

} 