<?php


namespace Tuffnells\Models;


use Tuffnells\Application;
use Tuffnells\Exceptions\InvalidConsignment;
use Tuffnells\Exceptions\InvalidDispatchDate;
use Tuffnells\Models\Consignment\History;
use Tuffnells\Models\Consignment\Label;
use Tuffnells\Models\Consignment\Signatures;

class Consignment implements \Serializable
{
    const STATUS_AWAITING_PICKUP = 1;

    const STATUS_IN_TRANSIT = 2;
    const STATUS_OUT_FOR_DELIVERY = 3;
    const STATUS_DELIVERED = 4;

    const SERVICE_TYPE_NEXTDAY = 1;
    const SERVICE_TYPE_PRE12 = 2;
    const SERVICE_TYPE_PRE10 = 3;
    const SERVICE_TYPE_SATAM = 4;
    const SERVICE_TYPE_2DAY = 5;
    const SERVICE_TYPE_3DAY = 6;
    const SERVICE_TYPE_OFFSHORE = 7;
    const SERVICE_TYPE_OFFSHORE_NEXT_DAY = 8;
    const SERVICE_TYPE_SATURDAY = 9;
    const SERVICE_TYPE_PRE930 = 10;


    private Application $_application;
    private \DateTime $_dispatchDate;
    private Package $_package_1;
    private Package $_package_2;
    private Package $_package_3;
    private string $_tuffnellsReference = '';
    private string $_customerReference = '';
    private Address $_collectionAddress;
    private Address $_deliveryAddress;
    private string $_urn;
    private $_serviceType = self::SERVICE_TYPE_NEXTDAY;
    private int $_status = self::STATUS_AWAITING_PICKUP;
    private History $_logs;
    private Label $_labels;
    private string $_consignmentNumber = '';
    private Signatures $_signatures;


    public function __construct(Application $application, string $urn = null) {
        $this->_application = $application;

        if(!empty($urn)) {
             $this->_urn = $urn;
        }
            
        //set the blank package type
        $this->_dispatchDate = new \DateTime();
        $this->_package_1 = new Package();
        $this->_package_2 = new Package();
        $this->_package_3 = new Package();
    }

    /**
     * @param Application $application
     * @return Consignment
     */
    public function setApplication(Application $application): Consignment
    {
        $this->_application = $application;
        if(!empty($this->_deliveryAddress))
            $this->_deliveryAddress->setApplication($application);

        if(!empty($this->_collectionAddress))
            $this->_collectionAddress->setApplication($application);

        return $this;
    }

    /**
     * @return Signatures
     */
    public function getSignatures(): Signatures
    {
        if($this->_status !== self::STATUS_DELIVERED) {
            throw new InvalidConsignment('Consignment Not Delivered Yet');
        }
        return $this->_signatures;
    }

    /**
     * @param Signatures $signatures
     * @return Consignment
     */
    public function setSignatures(Signatures $signatures): Consignment
    {
        $this->_signatures = $signatures;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->_status;
    }

    /**
     * @param int $status
     * @return Consignment
     */
    public function setStatus(int $status): Consignment
    {
        if($status < 1 || $status > 4) {
            throw new InvalidConsignment('Status Not Valid');
        }

        $this->_status = $status;
        return $this;
    }

    /**
     * @return History
     */
    public function getLogs(): History
    {
        if(empty($this->_logs)) {
            throw new InvalidConsignment('Logs Not Set');
        }

        return $this->_logs;
    }

    /**
     * @param History $logs
     * @return Consignment
     */
    public function setLogs(History $logs): Consignment
    {
        $this->_logs = $logs;
        return $this;
    }

    public function getConsignmentNumber(): string {
        if(empty($this->_consignmentNumber)) { //if the consignment number is empty then load from the server
            $this->_consignmentNumber = $this->_application->getConsignment($this->getUrn())->getConsignmentNumber();
        }
        return $this->_consignmentNumber;
    }

    public function getServiceType(): int
    {
        return $this->_serviceType;
    }

    /**
     * @param string $consignmentNumber
     * @return Consignment
     * @throws InvalidConsignment
     */
    public function setConsignmentNumber(string $consignmentNumber): Consignment
    {
        if(empty($consignmentNumber)) {
            throw new InvalidConsignment('Consignment Number Cannot Be Blank');
        }
        $this->_consignmentNumber = $consignmentNumber;
        return $this;
    }

    /**
     * @param int $serviceType
     * @return Consignment
     * @throws InvalidConsignment
     */
    public function setServiceType(int $serviceType): Consignment
    {
        if($serviceType < 1 || $serviceType > 10) {
            throw new InvalidConsignment('Invalid Service Type Set');
        }

        $this->_serviceType = $serviceType;
        return $this;
    }

    /**
     * Returns the average package weight
     *
     * @return int
     */
    public function getAveragePackageWeight() : int {
        $weight = 0;
        $quantity = 0;
        for($i =1; $i<= 3; $i++) {
            $package = '_package_' . $i;
            $weight += $this->$package->getWeight() * $this->$package->getQuantity();
            $quantity += $this->$package->getQuantity();
        }

        return $quantity < 1 ? 0 : ceil($weight/$quantity);
    }

    /**
     * @return Package
     */
    public function getPackage1(): Package
    {
        return $this->_package_1;
    }

    /**
     * @param Package $package_1
     * @return Consignment
     */
    public function setPackage1(Package $package_1): Consignment
    {
        $this->_package_1 = $package_1;
        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage2(): Package
    {
        return $this->_package_2;
    }

    /**
     * @param Package $package_2
     * @return Consignment
     */
    public function setPackage2(Package $package_2): Consignment
    {
        $this->_package_2 = $package_2;
        return $this;
    }

    /**
     * @return Package
     */
    public function getPackage3(): Package
    {
        return $this->_package_3;
    }

    /**
     * @param Package $package_3
     * @return Consignment
     */
    public function setPackage3(Package $package_3): Consignment
    {
        $this->_package_3 = $package_3;
        return $this;
    }

    /**
     * @param string $urn
     * @return $this
     * @throws InvalidConsignment
     */
    public function setURN(string $urn): Consignment {
        if(empty($urn)) {
            throw new InvalidConsignment('Invalid URN');
        }
        $this->_urn = $urn;
        return $this;
    }

    /**
     * Returns URN
     *
     * @return string
     * @throws InvalidConsignment
     */
    public function getUrn() : string {
        if(empty($this->_urn)) {
            throw new InvalidConsignment('Urn not set');
        }

        return $this->_urn;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setCollectionAddress(Address $address) : Consignment {
        $this->_collectionAddress = $address;
        return $this;
    }

    /**
     * Gets the collection address
     *
     * @return Address
     */
    public function getCollectionAddress() : Address {
        return $this->_collectionAddress;
    }

    /**
     * Returns the delivery address
     *
     * @return Address
     */
    public function getDeliveryAddress() : Address {
        return $this->_deliveryAddress;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setDeliveryAddress(Address $address) : Consignment {
        $this->_deliveryAddress = $address;
        return $this;
    }

    /**
     * Sets the tuffnells reference
     *
     * @param string $reference
     * @return $this
     */
    public function setTuffnellsReference(string $reference) : Consignment {
        $this->_tuffnellsReference = $reference;
        return $this;
    }

    /**
     * Gets the tuffnells reference
     *
     * @return string
     */
    public function getTuffnellsReference() : string {
        return $this->_tuffnellsReference;
    }

    /**
     * @param $reference
     * @return string
     */
    public function setCustomerReference(string $reference) : Consignment {
        $this->_customerReference = $reference;
        return $this;
    }

    /**
     * Returns the customer reference
     *
     * @return string
     */
    public function getCustomerReference() : string {
        return $this->_customerReference;
    }

    /**
     * Sets the dispatch date
     *
     * @param \DateTime $dispatchDate
     * @return $this
     * @throws InvalidDispatchDate
     */
    public function setDispatchDate(\DateTime $dispatchDate) {
        if(empty($this->_urn)) {
            if ($dispatchDate < new \DateTime(date('Y-m-d'))) { //set the midnight time
                throw new InvalidDispatchDate();
            }
        }

        $this->_dispatchDate = $dispatchDate;
        return $this;
    }

    /**
     * Returns the dispatch date
     *
     * @return \DateTime
     */
    public function getDispatchDate() {
        return $this->_dispatchDate;
    }

    /**
     * Validates the consignment
     *
     *
     */
    public function isValid(): bool
    {
        //check if packages are valid
        if(!$this->_package_1->isValid() &&
            !$this->_package_2->isValid() &&
            !$this->_package_3->isValid() ) {
            throw new InvalidConsignment('No Valid Packages');
        }

        //check if collection address is valid
        if(!$this->_collectionAddress->isValid())
            throw new InvalidConsignment('Collection Address Invalid');

        //check if collection address is valid
        if(!$this->_deliveryAddress->isValid())
            throw new InvalidConsignment('Delivery Address Invalid');

        //check if collection address is valid
        if(empty($this->_consignmentNumber))
            throw new InvalidConsignment('Consignment Number Invalid');

        return true;
    }

    /**
     * Tracks the consignment
     * @return Consignment
     */
    public function updateTracking(): Consignment {
        $this->_application->trackConsignment($this);
        return $this;
    }

    /**
     * Returns the labels for the consignment
     *
     * @return Label
     */
    public function getLabels(): Label {
        if(empty($this->_labels)) {
            $this->_labels = $this->_application->getLabels($this);
        }

        return $this->_labels;
    }

    /**
     * Saves the Consignment
     * @return Consignment
     * @throws InvalidConsignment|\Tuffnells\Exceptions\EndpointError
     */
    public function save(): Consignment {
        if(empty($this->_urn))
            return $this->_application->createConsignment($this);
        else
            return $this->_application->amendConsignment($this);
    }

    /**
     * @return bool
     * @throws \Tuffnells\Exceptions\EndpointError
     */
    public function delete(): bool {
        return $this->_application->deleteConsignment($this);
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->_package_1,
            $this->_package_2,
            $this->_package_3,
            $this->_customerReference,
            $this->_tuffnellsReference,
            $this->_dispatchDate,
            $this->_serviceType,
            $this->_status,
            empty($this->_urn) ? null : $this->_urn,
            empty($this->_consignmentNumber) ? null : $this->_consignmentNumber,
            empty($this->_collectionAddress) ? null : $this->_collectionAddress,
            empty($this->_deliveryAddress) ? null : $this->_deliveryAddress,
            empty($this->_logs) ? null : $this->_logs,
            empty($this->_signatures) ? null : $this->_signatures,
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $data = unserialize($data, [History::class, Signatures::class, Package::class, Address::class]);
        list(
            $this->_package_1,
            $this->_package_2,
            $this->_package_3,
            $this->_customerReference,
            $this->_tuffnellsReference,
            $this->_dispatchDate,
            $this->_serviceType,
            $this->_status,
        ) = $data;

        if(!empty($data[8]))
            $this->_urn = $data[8];

        if(!empty($data[9]))
            $this->_consignmentNumber = $data[9];

        if(!empty($data[10]))
            $this->_collectionAddress = $data[10];

        if(!empty($data[11]))
            $this->_deliveryAddress = $data[11];

        if(!empty($data[12]))
            $this->_logs = $data[12];

        if(!empty($data[13]))
            $this->_signatures = $data[13];
    }
}