<?php

/**
 * Created by apetit on 29/7/2017.
 */

namespace App\Report\Point;

class PointReport
{
    /**
     * @var array Detail report.
     */
    private $results;
    
    /**
     * @var array Detail report.
     */
    private $totals;

    /**
     * Construct of class.
     */
    public function __construct()
    {
        $this->totals = array
        (
            'company_credit' => 0,
            'company_debit' => 0,
            'company_credit_sales' => 0,
            'company_credit_behaviors' => 0,
            'user_credit' => 0,
            'user_debit' => 0,
            'activated' => 0
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
            $region_name = $region['company_region'];

            $this->results[$region_name] = array
            (
                'company_credit' => (double) $region['company_credit'],
                'company_debit' => (double) $region['company_debit'],
                'company_credit_sales' => (double) $region['company_credit_sales'],
                'percentage_credit_sales' => (($region['company_credit_sales'] > 0) ? round((($region['company_credit_sales'] / $region['company_credit']) * 100), 2) : 0),
                'company_credit_behaviors' => (double) $region['company_credit_behaviors'],
                'percentage_credit_behaviors' => (($region['company_credit_behaviors'] > 0) ? round((($region['company_credit_behaviors'] / $region['company_credit']) * 100), 2) : 0),
                'total_generated' => (double) $region['company_credit'],
                'user_credit' => (double) $region['user_credit'],
                'percentage_user_credit' => (($region['company_credit'] > 0) ? round((($region['user_credit'] / $region['company_credit']) * 100), 2) : 0),
                'activated' => (double) $region['activated'],
                'percentage_activated' => (($region['user_credit'] > 0) ? round((($region['activated'] / $region['user_credit']) * 100), 2) : 0),
                'user_debit' => (double) $region['user_debit'],
                'percentage_user_debit' => (($region['activated'] > 0) ? round((($region['user_debit'] / $region['activated']) * 100), 2) : 0),
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
            $region_name = $subregion['company_region'];
            $subregion_name = $subregion['company_subregion'];

            if (isset($this->results[$region_name]) === TRUE)
            {
                $this->results[$region_name]['subregions'][$subregion_name] = array
                (
                    'company_credit' => (double) $subregion['company_credit'],
                    'company_debit' => (double) $subregion['company_debit'],
                    'company_credit_sales' => (double) $subregion['company_credit_sales'],
                    'percentage_credit_sales' => (($subregion['company_credit_sales'] > 0) ? round((($subregion['company_credit_sales'] / $subregion['company_credit']) * 100), 2) : 0),
                    'company_credit_behaviors' => (double) $subregion['company_credit_behaviors'],
                    'percentage_credit_behaviors' => (($subregion['company_credit_behaviors'] > 0) ? round((($subregion['company_credit_behaviors'] / $subregion['company_credit']) * 100), 2) : 0),
                    'total_generated' => (double) $subregion['company_credit'],
                    'user_credit' => (double) $subregion['user_credit'],
                    'percentage_user_credit' => (($subregion['company_credit'] > 0) ? round((($subregion['user_credit'] / $subregion['company_credit']) * 100), 2) : 0),
                    'activated' => (double) $subregion['activated'],
                    'percentage_activated' => (($subregion['user_credit'] > 0) ? round((($subregion['activated'] / $subregion['user_credit']) * 100), 2) : 0),
                    'user_debit' => (double) $subregion['user_debit'],
                    'percentage_user_debit' => (($subregion['activated'] > 0) ? round((($subregion['user_debit'] / $subregion['activated']) * 100), 2) : 0),
                );
            }
        }

        return $this;
    }

    /**
     *
     * @param type $countries
     * @return $this
     */
    public function addCountriesToArray($countries)
    {
        foreach($countries as $country)
        {
            $region_name = $country['company_region'];
            $subregion_name = $country['company_subregion'];
            $country_name = $country['company_country'];

            if (isset($this->results[$region_name]['subregions'][$subregion_name]) === TRUE)
            {
                $this->results[$region_name]['subregions'][$subregion_name]['countries'][$country_name] = array
                (
                    'company_credit' => (double) $country['company_credit'],
                    'company_debit' => (double) $country['company_debit'],
                    'company_credit_sales' => (double) $country['company_credit_sales'],
                    'percentage_credit_sales' => (($country['company_credit_sales'] > 0) ? round((($country['company_credit_sales'] / $country['company_credit']) * 100), 2) : 0),
                    'company_credit_behaviors' => (double) $country['company_credit_behaviors'],
                    'percentage_credit_behaviors' => (($country['company_credit_behaviors'] > 0) ? round((($country['company_credit_behaviors'] / $country['company_credit']) * 100), 2) : 0),
                    'total_generated' => (double) $country['company_credit'],
                    'user_credit' => (double) $country['user_credit'],
                    'percentage_user_credit' => (($country['company_credit'] > 0) ? round((($country['user_credit'] / $country['company_credit']) * 100), 2) : 0),
                    'activated' => (double) $country['activated'],
                    'percentage_activated' => (($country['user_credit'] > 0) ? round((($country['activated'] / $country['user_credit']) * 100), 2) : 0),
                    'user_debit' => (double) $country['user_debit'],
                    'percentage_user_debit' => (($country['activated'] > 0) ? round((($country['user_debit'] / $country['activated']) * 100), 2) : 0),
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
            $this->totals['company_credit'] += $region['company_credit'];
            $this->totals['company_debit'] += $region['company_debit'];
            $this->totals['company_credit_sales'] += $region['company_credit_sales'];
            $this->totals['company_credit_behaviors'] += $region['company_credit_behaviors'];
            $this->totals['user_credit'] += $region['user_credit'];
            $this->totals['user_debit'] += $region['user_debit'];
            $this->totals['activated'] += $region['activated'];
        }

        $this->totals['percentage_credit_sales'] = (($this->totals['company_credit_sales'] > 0) ? round((($this->totals['company_credit_sales'] / $this->totals['company_credit']) * 100), 2) : 0);
        $this->totals['percentage_credit_behaviors'] = (($this->totals['company_credit_behaviors'] > 0) ? round((($this->totals['company_credit_behaviors'] / $this->totals['company_credit']) * 100), 2) : 0);
        $this->totals['percentage_user_credit'] = (($this->totals['company_credit'] > 0) ? round((($this->totals['user_credit'] / $this->totals['company_credit']) * 100), 2) : 0);
        $this->totals['percentage_activated'] = (($this->totals['user_credit'] > 0) ? round((($this->totals['activated'] / $this->totals['user_credit']) * 100), 2) : 0);
        $this->totals['percentage_user_debit'] = (($this->totals['activated'] > 0) ? round((($this->totals['user_debit'] / $this->totals['activated']) * 100), 2) : 0);

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
