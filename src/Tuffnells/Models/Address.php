<?php

namespace Tuffnells\Models;

use Tuffnells\Application;
use Tuffnells\Exceptions\EndpointError;
use Tuffnells\Exceptions\PostcodeNotValid;

class Address implements \Serializable
{
    private Application $_application;
    private string $_company = '';
    private string $_addressLine1;
    private string $_addressLine2 = '';
    private string $_addressLine3 = '';
    private string $_city;
    private string $_region;
    private string $_postcode;
    private int $_countryCode = 44;
    private string $_contactName;
    private string $_phoneNumber = '';
    private string $_contactEmail = '';
    private string $_instructions = '';
    private bool $_tailLift = false;

    /**
     * @param Application $application
     * @return Address
     */
    public function setApplication(Application $application): Address
    {
        $this->_application = $application;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequiredTailLift(): bool
    {
        return $this->_tailLift;
    }

    /**
     * @param bool $tailLift
     * @return Address
     */
    public function setRequiredTailLift(bool $tailLift): Address
    {
        $this->_tailLift = $tailLift;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstructions(): string
    {
        return $this->_instructions;
    }

    /**
     * @param string $instructions
     * @return Address
     */
    public function setInstructions(string $instructions): Address
    {
        $this->_instructions = $instructions;
        return $this;
    }

    public function __construct(Application $application)
    {
        $this->_application = $application;
    }


    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->_region;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->_company;
    }

    /**
     * @param string $company
     * @return Address
     */
    public function setCompany(string $company): Address
    {
        $this->_company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1(): string
    {
        return $this->_addressLine1;
    }

    /**
     * @param string $addressLine1
     * @return Address
     */
    public function setAddressLine1(string $addressLine1): Address
    {
        $this->_addressLine1 = $addressLine1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2(): string
    {
        return $this->_addressLine2;
    }

    /**
     * @param string $addressLine2
     * @return Address
     */
    public function setAddressLine2(string $addressLine2): Address
    {
        $this->_addressLine2 = $addressLine2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3(): string
    {
        return $this->_addressLine3;
    }

    /**
     * @param string $addressLine3
     * @return Address
     */
    public function setAddressLine3(string $addressLine3): Address
    {
        $this->_addressLine3 = $addressLine3;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->_city;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->_postcode;
    }

    /**
     * @param string $postcode
     * @param CityRegion $cityRegion
     * @return Address
     * @throws PostcodeNotValid|EndpointError
     */
    public function setPostcode(string $postcode, CityRegion $cityRegion = null): Address
    {
        $this->_postcode = $postcode;
        if ($cityRegion === null) { //if the city isn't provided get the city from tuffnells
            $cityRegion = $this->_application->getCityRegion($postcode);
        }

        $this->_city = $cityRegion->getCity();
        $this->_region = $cityRegion->getRegion();

        return $this;
    }

    /**
     * @return int
     */
    public function getCountryCode(): int
    {
        return $this->_countryCode;
    }

    /**
     * @param int $countryCode
     * @return Address
     */
    public function setCountryCode(int $countryCode): Address
    {
        $this->_countryCode = $countryCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->_contactName;
    }

    /**
     * @param string $contactName
     * @return Address
     */
    public function setContactName(string $contactName): Address
    {
        $this->_contactName = $contactName;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactPhone(): string
    {
        return $this->_phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return Address
     */
    public function setContactPhone(string $phoneNumber): Address
    {
        $this->_phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->_contactEmail;
    }

    /**
     * @param string $contactEmail
     * @return Address
     */
    public function setContactEmail(string $contactEmail): Address
    {
        $this->_contactEmail = $contactEmail;
        return $this;
    }

    /**
     * Checks if address is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !(empty($this->_addressLine1)          || empty($this->_city)            || empty($this->_contactName)            || empty($this->_region)            || empty($this->_postcode));
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->_postcode,
            $this->_region,
            $this->_contactName,
            $this->_city,
            $this->_addressLine1,
            $this->_addressLine2,
            $this->_addressLine3,
            $this->_company,
            $this->_contactEmail,
            $this->_countryCode,
            $this->_instructions,
            $this->_phoneNumber,
            $this->_tailLift
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->_postcode,
            $this->_region,
            $this->_contactName,
            $this->_city,
            $this->_addressLine1,
            $this->_addressLine2,
            $this->_addressLine3,
            $this->_company,
            $this->_contactEmail,
            $this->_countryCode,
            $this->_instructions,
            $this->_phoneNumber,
            $this->_tailLift) = unserialize($serialized, [false]);
    }
}