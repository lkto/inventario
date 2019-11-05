<?php

/**
 * Created by apetit on 29/7/2017.
 */

namespace App\Report\Sales;

class SalesReport
{
    /**
     * @var array Detail report.
     */
    private $results;

    /**
     * @var array Totals report.
     */
    private $totals;

    /**
     * Construct of class.
     */
    public function __construct()
    {
        $this->totals = array
        (
            'expected_revenue' => 0,
            'expected_points' => 0,
            'expected_ytd_revenue' => 0,
            'expected_ytd_points' => 0,
            'uploaded_revenue' => 0,
            'uploaded_points' => 0
        );
    }

    /**
     *
     * @param type $regions
     * @return $this
     */
    public function addRegionsToArray($regions)
    {
        foreach($regions as $region)
        {
            $region_name = $region['region_name'];

            $this->results[$region_name] = array
            (
                'expected_revenue' => $region['expected_revenue'],
                'expected_points' => $region['expected_points'],
                'expected_ytd_revenue' => $region['expected_ytd_revenue'],
                'expected_ytd_points' => $region['expected_ytd_points'],
                'uploaded_revenue' => $region['uploaded_revenue'],
                'percentage_uploaded_revenue' => (($region['expected_revenue'] > 0) ? (($region['uploaded_revenue'] / $region['expected_revenue']) * 100) : 0),
                'uploaded_points' => $region['uploaded_points'],
                'percentage_uploaded_points' => (($region['expected_revenue'] > 0) ? (($region['uploaded_points'] / $region['expected_points']) * 100) : 0)
            );
        }

        return $this;
    }

    /**
     *
     * @param type $subregions
     * @return $this
     */
    public function addSubregionsToArray($subregions)
    {
        foreach($subregions as $subregion)
        {
            $region_name = $subregion['region_name'];
            $subregion_name = $subregion['sub_region_name'];

            if (isset($this->results[$region_name]) === TRUE)
            {
                $this->results[$region_name]['subregions'][$subregion_name] = array
                (
                    'expected_revenue' => $subregion['expected_revenue'],
                    'expected_points' => $subregion['expected_points'],
                    'expected_ytd_revenue' => $subregion['expected_ytd_revenue'],
                    'expected_ytd_points' => $subregion['expected_ytd_points'],
                    'uploaded_revenue' => $subregion['uploaded_revenue'],
                    'percentage_uploaded_revenue' => (($subregion['expected_revenue'] > 0) ? (($subregion['uploaded_revenue'] / $subregion['expected_revenue']) * 100) : 0),
                    'uploaded_points' => $subregion['uploaded_points'],
                    'percentage_uploaded_points' => (($subregion['expected_revenue'] > 0) ? (($subregion['uploaded_points'] / $subregion['expected_points']) * 100) : 0)
                );
            }
        }

        return $this;
    }

    /**
     *
     * @param type $units
     * @return $this
     */
    public function addUnitsToArray($units)
    {
        foreach($units as $unit)
        {
            $region_name = $unit['region_name'];
            $subregion_name = $unit['sub_region_name'];
            $unit_name = $unit['business_unit_name'];

            if (isset($this->results[$region_name]['subregions'][$subregion_name]) === TRUE)
            {
                $this->results[$region_name]['subregions'][$subregion_name]['units'][$unit_name] = array
                (
                    'expected_revenue' => $unit['expected_revenue'],
                    'expected_points' => $unit['expected_points'],
                    'expected_ytd_revenue' => $unit['expected_ytd_revenue'],
                    'expected_ytd_points' => $unit['expected_ytd_points'],
                    'uploaded_revenue' => $unit['uploaded_revenue'],
                    'percentage_uploaded_revenue' => (($unit['expected_revenue'] > 0) ? (($unit['uploaded_revenue'] / $unit['expected_revenue']) * 100) : 0),
                    'uploaded_points' => $unit['uploaded_points'],
                    'percentage_uploaded_points' => (($unit['expected_revenue'] > 0) ? (($unit['uploaded_points'] / $unit['expected_points']) * 100) : 0)
                );
            }
        }

        return $this;
    }

    /**
     *
     * @param type $regions
     * @return $this
     */
    public function addTotalsToArray($regions)
    {
        foreach($regions as $region)
        {
            $this->totals['expected_revenue'] += $region['expected_revenue'];
            $this->totals['expected_points'] += $region['expected_points'];
            $this->totals['expected_ytd_revenue'] += $region['expected_ytd_revenue'];
            $this->totals['expected_ytd_points'] += $region['expected_ytd_points'];
            $this->totals['uploaded_revenue'] += $region['uploaded_revenue'];
            $this->totals['uploaded_points'] += $region['uploaded_points'];
        }

        $this->totals['percentage_uploaded_revenue'] = (($this->totals['expected_revenue'] > 0) ? (($this->totals['uploaded_revenue'] / $this->totals['expected_revenue']) * 100) : 0);
        $this->totals['percentage_uploaded_points'] = (($this->totals['expected_points'] > 0) ? (($this->totals['uploaded_points'] / $this->totals['expected_points']) * 100) : 0);

        return $this;
    }

    /**
     *
     * @return type
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     *
     * @return type
     */
    public function getTotals()
    {
        return $this->totals;
    }
}
