<?php
namespace Tuffnells\Models;

class CityRegion {

    private string $city;
    private string $region;

    public function __construct(string $city, string $region) {
        $this->city = $city;
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return CityRegion
     */
    public function setCity(string $city): CityRegion
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return CityRegion
     */
    public function setRegion(string $region): CityRegion
    {
        $this->region = $region;
        return $this;
    }

}