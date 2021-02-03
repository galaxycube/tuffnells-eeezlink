<?php

namespace Tuffnells;

use bdk\CssXpath\CssSelect;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Tuffnells\Exceptions\AccountDetailsInvalid;
use Tuffnells\Exceptions\ConsignmentNotFound;
use Tuffnells\Exceptions\EndpointError;
use Tuffnells\Exceptions\InvalidCache;
use Tuffnells\Exceptions\InvalidDispatchDate;
use Tuffnells\Exceptions\InvalidURN;
use Tuffnells\Exceptions\PostcodeNotValid;
use Tuffnells\Exceptions\ViewstateNotFound;
use Tuffnells\Models\Address;
use Tuffnells\Models\CityRegion;
use Tuffnells\Models\Consignment;
use Tuffnells\Models\Package;
use Tuffnells\Models\Consignment\History;
use Tuffnells\Models\Consignment\History\Log;

class Application
{
    const CACHE_COOKIE_JAR = 'CJ';
    const CACHE_URN_PREFIX = 'URN';
    const CACHE_LABEL_PREFIX = 'LABEL';
    const CACHE_POSTCODE_PREFIX = 'PC';

    private CookieJar $_cookie;
    private string $_accountId;
    private string $_username;
    private string $_password;
    private LoggerInterface $_logger;
    private CacheInterface $_cache;
    private string $_cachePrefix = 'TUFFNELLS-';
    private \DateInterval $_cacheConsignmentInterval;
    private Client $_guzzleClient;

    public function __construct(string $accountId,string $username,string $password, Client $guzzleClient = null){
        $this->_accountId = $accountId;
        $this->_username = $username;
        $this->_password = $password;
        $this->_cacheConsignmentInterval = new \DateInterval('PT5H');
        $this->_guzzleClient = $guzzleClient ?? new Client();
    }

    /**
     * @param Client $guzzleClient
     * @return Application
     */
    public function setGuzzleClient(Client $guzzleClient): Application
    {
        $this->_guzzleClient = $guzzleClient;
        return $this;
    }

    /**
     * @param CacheInterface $cache
     * @return Application
     */
    public function setCache(CacheInterface $cache): Application
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * @param string $cachePrefix
     * @return Application
     * @throws InvalidCache
     */
    public function setCachePrefix(string $cachePrefix): Application {
        if(empty($cachePrefix)) {
           throw new InvalidCache('Prefix cannot be empty.');
        }
        $this->_cachePrefix = $cachePrefix;
        return $this;
    }

    /**
     * @param $key
     * @return false|object
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getCachedObject($key){
        if(empty($this->_cache)) {
            return false;
        }
        $this->debug('Cache Hit - ' . str_replace(" ","",strtolower(trim($this->_cachePrefix . $key))));
        return $this->_cache->get(str_replace(" ","",strtolower(trim($this->_cachePrefix . $key))));
    }

    /**
     * @param $key
     * @param $value
     * @param \DateInterval|null $expiration
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function setCachedObject($key, $value, \DateInterval $expiration = null): bool {
        if(empty($this->_cache)) {
            return false;
        }

        return $this->_cache->set(str_replace(" ","",strtolower(trim($this->_cachePrefix . $key))), $value, $expiration);
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): Application {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * @param string $msg
     */
    public function debug(string $msg): void {
        if(!empty($this->_logger)) {
            $this->_logger->debug($msg);
        }
    }

    /**
     * Logs into Tuffnells eezlink
     *
     * @throws AccountDetailsInvalid
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function login() {
        $this->debug('Check cache for cookie jar');
        $cookieJar = $this->getCachedObject(self::CACHE_COOKIE_JAR);

        if(!$cookieJar) {
            $this->debug('Attempting Login');

            //log in to eezlink
            $client = $this->_guzzleClient;
            try {
                $res = $client->post('https://www.tpeweb.co.uk/dotweb/default.aspx', [
                        'form_params' => [
                            'tbxAccount' => $this->_accountId,
                            'Username' => $this->_username,
                            'Password' => $this->_password,
                            'Button1' => 'Login',
                            'OsType' => 'NOT+VISTA',
                            '__EVENTTARGET' => '',
                            '__EVENTARGUMENT' => '',
                            '__VIEWSTATE' => 'dDwtNzg5NjYwMzQ0O3Q8O2w8aTwxPjs+O2w8dDw7bDxpPDQ+O2k8MTY+O2k8MTg+O2k8MjI+Oz47bDx0PHA8cDxsPFRleHQ7PjtsPFxlOz4+Oz47Oz47dDxwPHA8bDxOYXZpZ2F0ZVVybDs+O2w8aHR0cHM6Ly9jb25uZWN0Z3JvdXBwcm9kLnNlcnZpY2Utbm93LmNvbS9zcDs+Pjs+Ozs+O3Q8cDxwPGw8VmlzaWJsZTs+O2w8bzx0Pjs+Pjs+O2w8aTwxPjtpPDM+Oz47bDx0PHA8cDxsPFRleHQ7PjtsPE1lc3NhZ2VzOjs+Pjs+Ozs+O3Q8cDxwPGw8VGV4dDs+O2w8XDxiXD5JVCBTdXBwb3J0IFNlcnZpY2UgRGVzayBSZWdpc3RyYXRpb25cPC9iXD5cPGJyXD5QbGVhc2UgYmUgYXdhcmUgdGhhdCBpbiBvcmRlciB0byB1c2UgdGhlIHJlcXVlc3QgSVQgYXNzaXN0YW5jZSBmZWF0dXJlIG9yIHRvIGVtYWlsIG91ciBzZXJ2aWNlIGRlc2sgeW91IHdpbGwgbmVlZCB0byBiZSByZWdpc3RlcmVkLiBQbGVhc2UgYnJvd3NlIHRvIHRoZSBmb2xsb3dpbmcgYWRkcmVzcyB0byByZWdpc3RlciBmb3IgdGhlIHNlbGYgc2VydmljZSBwb3J0YWw6IGh0dHBzOi8vY29ubmVjdGdyb3VwcHJvZC5zZXJ2aWNlLW5vdy5jb20vc3A/aWQ9c3BfcmVnaXN0cmF0aW9uDQpZb3Ugd2lsbCBuZWVkIHlvdXIgOCBkaWdpdCBhY2NvdW50IG51bWJlciBhbmQgYW4gZW1haWwgYWRkcmVzc1w8YnJcPlw8YnJcPjs+Pjs+Ozs+Oz4+O3Q8O2w8aTwxPjs+O2w8dDxwPHA8bDxUZXh0Oz47bDwzMzAgODM4IDQyMzA7Pj47Pjs7Pjs+Pjs+Pjs+Pjs+EDIckjtw9xcvz9IPPsyhRIOEzrw=',
                        ],
                        'Origin' => 'https://www.tpeweb.co.uk',
                        'Referer' => 'https://www.tpeweb.co.uk/dotweb/',
                        'Upgrade-Insecure-Requests' => 1,
                        'allow_redirects' => false
                    ]
                );
            }
            catch(\Exception $e) {
                throw new EndpointError('Tuffnells Login Issue');
            }


            if($res->getStatusCode() === 200 || empty($res->getHeader('Set-Cookie')[0])) { //if not 403 or the cookie is not set the session is invalid
                throw new AccountDetailsInvalid();
            }

            //build the cookie jar for guzzle
            $cookies = $res->getHeader('Set-Cookie')[0];
            $cookie = SetCookie::fromString($cookies);
            $cookie->setDomain('www.tpeweb.co.uk');
            $cookieJar = new CookieJar(true);
            $cookieJar->setCookie($cookie);
            $this->setCachedObject(self::CACHE_COOKIE_JAR, $cookieJar, new \DateInterval('PT6H')); //cache for six hours
        }

        $this->_cookie = $cookieJar;
        $this->debug('Login Successful, Cookie Jar Set');
        return true;
    }

    /**
     * Makes request
     *
     * @param string $method
     * @param string $uri
     * @param array $settings
     * @return mixed
     */
    private function makeRequest(string $method, string $uri, array $settings = []) {
        if(isset($this->_cookie) === false) {
            $this->login();
        }

        $this->debug('Making ' . strtoupper($method) . ' request to ' . $uri);

        $settings['cookies'] = $this->_cookie;
        $client = $this->_guzzleClient;
        return $client->$method('https://www.tpeweb.co.uk/dotweb/' . $uri, $settings);
    }

    /**
     * @param string $url
     * @return array
     */
    public function getFormdata(string $url): array {
        $response = $this->makeRequest('get',$url);
        $cssSelect = new CssSelect($response->getBody()->getContents());

        $this->debug('Converting Form Data');

        $formdata = [];
        $found = $cssSelect->select('input', false); //find the viewstate attribute

        foreach($found as $input) {
            $formdata[$input['attributes']['name']] = empty($input['attributes']['value']) ? '' : $input['attributes']['value'];
        }

        $found = $cssSelect->select('select', false); //find the viewstate attribute

        foreach($found as $input) {
            $formdata[$input['attributes']['name']] = '';

            $cssSelect = new CssSelect($input['innerHTML']);
            $options = $cssSelect->select('option[selected]', false); //find the viewstate attribute
            if(sizeof($options) !== 0)
                $formdata[$input['attributes']['name']] = $options[0]['attributes']['value'];
        }

        return $formdata;
    }

    /**
     * Gets the viewstate for a page
     * @param string $url
     * @return string
     */
    private function getViewstate(string $url) : string {
        $formdata = $this->getFormdata($url);

        $this->debug('Getting VIEWSTATE Data Hack');

        if(empty($formdata['__VIEWSTATE'])) {
            throw new ViewstateNotFound();
        }

        return $formdata['__VIEWSTATE'];
    }

    /**
     * @param Response $response
     * @return array
     * @throws EndpointError
     */
    private function parseRedirectQueryString(Response $response): array {
        $this->debug('Get redirect data as array');

        if($response->getStatusCode() !== 302) {
            throw new EndpointError('Unknown' . $response->getStatusCode(), $response->getStatusCode());
        }

        $result = [];
        parse_str(parse_url($response->getHeader('Location')[0])['query'], $result); //convert the redirect url to a url array and then parse the query string
        return $result;
    }

    /**
     * Returns the city depending on the entered postcode
     *
     * @param string $postcode
     * @return CityRegion
     * @throws EndpointError
     * @throws PostcodeNotValid
     */
    public function getCityRegion(string $postcode): CityRegion {
        $this->debug('Check cache for City and Region from Postcode - ' . $postcode);
        $cityRegion = $this->getCachedObject(self::CACHE_POSTCODE_PREFIX . $postcode);
        if($cityRegion) {
            return $cityRegion;
        }

        $this->debug('Getting City and Region from Postcode - ' . $postcode);

        $url = 'postsectorsearch.aspx?PostSector=' . $postcode;
        $viewstate = $this->getViewstate($url);

        $response = $this->makeRequest('post', $url, [
            'form_params' => [
                '__VIEWSTATE' => $viewstate,
                'tbxPostcode' => $postcode,
                'tbxCustName' => '',
                'tbxThoroughfare' => '',
                'tbxTownLocality' => '',
                'Datagrid2:_ctl2:_ctl0' => 'Select',
            ],
            'allow_redirects' => false
        ]);

        try {
            $result = $this->parseRedirectQueryString($response);
        }
        catch(\Exception $e) {
            throw new PostcodeNotValid();
        }


        $this->debug('Postcode Search Successful - ' . $result['Town'] . '/' . $result['County']);

        $return = new CityRegion($result['Town'],$result['County']);

        $this->setCachedObject(self::CACHE_POSTCODE_PREFIX . $postcode, $return); //save inthe cache indefinetly

        return $return;
    }

    /**
     * Creates a new consignment
     *
     * @param \Tuffnells\Models\Consignment $consignment
     * @return \Tuffnells\Models\Consignment $consignment
     */
    public function createConsignment(Consignment $consignment): Consignment {
        $url = 'consignment.aspx?type=newdel';
        return $this->insertAndAmendRequest($consignment, $url);
    }

    /**
     * Creates a new consignment
     *
     * @param \Tuffnells\Models\Consignment $consignment
     * @return \Tuffnells\Models\Consignment $consignment
     */
    public function amendConsignment(Consignment $consignment): Consignment {
        $url = 'consignment.aspx?type=amend&URN=' . $consignment->getUrn();
        return $this->insertAndAmendRequest($consignment, $url);
    }

    private function insertAndAmendRequest(Consignment $consignment, string $url): Consignment {
        $consignment->isValid(); //make sure consignment is valid

        if ($consignment->getDispatchDate() < new \DateTime(date('Y-m-d'))) { //set the midnight time
            throw new InvalidDispatchDate('Cannot update a consignment older than todays date');
        }

        $viewstate = $this->getViewstate($url);
        $response = $this->makeRequest('post', $url,
            [
                'form_params' => [
                    '__VIEWSTATE' => $viewstate,
                    'CustomerAccount' => $this->_accountId,
                    'OurRef' => $consignment->getCustomerReference(),
                    'YourRef' => $consignment->getTuffnellsReference(),
                    'ServiceType' => $consignment->getServiceType(),
                    'DespatchDate' => $consignment->getDispatchDate()->format('d/m/Y'),
                    'ConRef' => $consignment->getConsignmentNumber(),
                    'Weight' => $consignment->getAveragePackageWeight(),
                    'PackageType1' => $consignment->getPackage1()->getType(),
                    'Package1Qty' => $consignment->getPackage1()->getQuantity(),
                    'PackageType2' => $consignment->getPackage2()->getType(),
                    'Package2Qty' => $consignment->getPackage2()->getQuantity(),
                    'PackageType3' => $consignment->getPackage3()->getType(),
                    'Package3Qty' => $consignment->getPackage3()->getQuantity(),
                    'ColAddressRef' => '',
                    'ColPostcode' => $consignment->getCollectionAddress()->getPostcode(),
                    'ColCustomerName' => $consignment->getCollectionAddress()->getCompany(),
                    'ColAddress1' => $consignment->getCollectionAddress()->getAddressLine1(),
                    'ColAddress2' => $consignment->getCollectionAddress()->getAddressLine2(),
                    'ColAddress3' => $consignment->getCollectionAddress()->getAddressLine3(),
                    'ColTown' => $consignment->getCollectionAddress()->getCity(),
                    'ColCounty' => $consignment->getCollectionAddress()->getRegion(),
                    'ColContactName' => $consignment->getCollectionAddress()->getContactName(),
                    'ColTelephone' => $consignment->getCollectionAddress()->getContactPhone(),
                    'ColSpecialInstructions' => $consignment->getCollectionAddress()->getInstructions(),
                    'DelAddressRef' => '',
                    'DelPostcode' => $consignment->getDeliveryAddress()->getPostcode(),
                    'DelCountry' => $consignment->getDeliveryAddress()->getCountryCode(),
                    'DelCustomerName' => $consignment->getDeliveryAddress()->getCompany(),
                    'DelAddress1' => $consignment->getDeliveryAddress()->getAddressLine1(),
                    'DelAddress2' => $consignment->getDeliveryAddress()->getAddressLine2(),
                    'DelAddress3' => $consignment->getDeliveryAddress()->getAddressLine3(),
                    'DelTown' => $consignment->getDeliveryAddress()->getCity(),
                    'DelCounty' => $consignment->getDeliveryAddress()->getRegion(),
                    'DelContactName' => $consignment->getDeliveryAddress()->getContactName(),
                    'DelTelephone' => $consignment->getDeliveryAddress()->getContactPhone(),
                    'DelEmailAddress' => $consignment->getDeliveryAddress()->getContactEmail(),
                    'DelSpecialInstructions' => $consignment->getDeliveryAddress()->getInstructions(),
                    'tbxCopies' => '1',
                    'Okay' => 'Ok'
                ],
                'allow_redirects' => false
            ]);

        $result = $this->parseRedirectQueryString($response);
        if(empty($result['URN'])) {
            throw new EndpointError('URN Not Created');
        }

        $this->debug('Consignment successfully created with URN - ' . $result['URN']);

        $consignment->setURN($result['URN']);

        //lets update the cache
        $this->setCachedObject(self::CACHE_URN_PREFIX . $consignment->getUrn(), $consignment, $this->_cacheConsignmentInterval); //lets store for 5 days

        return $consignment;
    }

    /**
     * Voids a consignment
     *
     * @param \Tuffnells\Models\Consignment $consignment
     * @return bool
     */
    public function deleteConsignment(Consignment $consignment): bool {
        $url = 'consignment.aspx?type=delete&URN=' . $consignment->getUrn();
        $formdata = $this->getFormdata($url);
        $response = $this->makeRequest('post', $url,
        [
            'form_params' => $formdata,
            'allow_redirects' => false
        ]);

        if($response->getStatusCode() !== 302) {
            throw new EndpointError('Failed to Delete Consignment - ' . $consignment->getUrn());
        }
        $this->debug('Consignment ' . $consignment->getUrn() . ' Deleted');

        //lets unset the cache
        $this->setCachedObject(self::CACHE_URN_PREFIX . $consignment->getUrn(), false); //lets store for 5 days

        return true;
    }

    /**
     * Gets the consignment for the URN
     *
     * @param string $urn
     * @return Consignment
     */
    public function getConsignment(string $urn): Consignment {
        if(empty($urn)) { //check to make sure the urn is valid
            throw new InvalidURN();
        }

        $this->debug('Check if Cache has consignment - ' . $urn . '|');
        $consignment = $this->getCachedObject(self::CACHE_URN_PREFIX . $urn);
        if($consignment) {
            $this->debug('Consignment - ' . $urn . ' retreived from cache');
            $consignment->setApplication($this);
            return $consignment;
        }

        $this->debug('Retrieving Consignment - ' . $urn);

        $formdata = $this->getFormdata('consignment.aspx?type=view&URN=' . $urn);
        if(empty($formdata['ConRef'])) {
            throw new ConsignmentNotFound('URN - ' . $urn);
        }

        $consignment = new Consignment($this, $urn);
        $consignment->setTuffnellsReference($formdata['OurRef']);
        $consignment->setCustomerReference($formdata['YourRef']);

        //set the packages
        for($i = 1; $i <= 3; $i++) {
            $package = new Package();

            if(!empty($formdata['Package' . $i . 'Qty'])) {
                $package->setWeight($formdata['Weight']);
                $package->setQuantity($formdata['Package' . $i . 'Qty']);
                $package->setType($formdata['PackageType' . $i]);
            }

            $method = 'setPackage' . $i;
            $consignment->$method($package);
        }

        $consignment->setServiceType($formdata['ServiceType']);
        $consignment->setConsignmentNumber($formdata['ConRef']);
        $consignment->setDispatchDate(\DateTime::createFromFormat('d/m/Y',$formdata['DespatchDate']));

        $deliveryAddress = new Address($this);
        $deliveryAddress->setCompany($formdata['DelCustomerName']);
        $deliveryAddress->setAddressLine1($formdata['DelAddress1']);
        $deliveryAddress->setAddressLine2($formdata['DelAddress2']);
        $deliveryAddress->setAddressLine3($formdata['DelAddress3']);
        $deliveryAddress->setPostcode($formdata['DelPostcode'], new CityRegion($formdata['DelTown'],$formdata['DelCounty']));
        $deliveryAddress->setContactName($formdata['DelContactName']);
        $deliveryAddress->setContactPhone($formdata['DelTelephone']);
        $deliveryAddress->setCountryCode($formdata['DelCountry']);

        if(!empty($formdata['DelEmailAddress'])) {
            $deliveryAddress->setContactEmail($formdata['DelEmailAddress']);
        }

        $deliveryAddress->setRequiredTailLift(empty($formdata['DelTailLift']) ? false : true);
        $consignment->setDeliveryAddress($deliveryAddress);

        $pickupAddress = new Address($this);
        $pickupAddress->setCompany($formdata['ColCustomerName']);
        $pickupAddress->setAddressLine1($formdata['ColAddress1']);
        $pickupAddress->setAddressLine2($formdata['ColAddress2']);
        $pickupAddress->setAddressLine3($formdata['ColAddress3']);
        $pickupAddress->setPostcode($formdata['ColPostcode'], new CityRegion($formdata['ColTown'],$formdata['ColCounty']));
        $pickupAddress->setContactName($formdata['ColContactName']);
        $pickupAddress->setContactPhone($formdata['ColTelephone']);
        $pickupAddress->setCountryCode($formdata['ColCountry']);

        $pickupAddress->setRequiredTailLift(empty($formdata['ColTailLift']) ? false : true);
        $consignment->setCollectionAddress($pickupAddress);

        $this->debug('Consignment retrieved - ' . $urn);

        //lets update the cache
        if(!$this->setCachedObject(self::CACHE_URN_PREFIX . $urn, $consignment, $this->_cacheConsignmentInterval)) //lets store for 5 days
            $this->debug('Failed to save cache');

        return $consignment;
    }

    /**
     *
     * @param \Tuffnells\Models\Consignment $consignment
     */
    public function trackConsignment(Consignment $consignment): Consignment {
        $this->debug('Get consignment tracking details - ' . $consignment->getUrn());

        //check if consignment already delivered
        if($consignment->getStatus() === Consignment::STATUS_DELIVERED) {
            $this->debug('Consignment already delivered, tracking updated from cache');
            return $consignment;
        }

        //log in to eezlink
        $client = $this->_guzzleClient;
        $response = $client->get('https://www.tpeweb.co.uk/ezpod/tracking.aspx?acc=' . $this->_accountId . '&con=' . $consignment->getConsignmentNumber() . '&delpc=' . $consignment->getDeliveryAddress()->getPostcode());

        $this->debug('Parse consignment results - ' . $consignment->getUrn());
        $cssSelect = new CssSelect($response->getBody()->getContents());
        $formdata = [];
        $movements = $cssSelect->select('#grdMovements tr', false); //find the viewstate attribute

        if(count($movements) === 0) { //no tracking information
            $consignment->setStatus(Consignment::STATUS_AWAITING_PICKUP);
            return $consignment;
        }

        $history = new History();
        $count = count($movements);

        if($count > 0) {
            for($i=1; $i < $count; $i++) {
                $cssSelectTable = new CssSelect($movements[$i]['innerHTML']);
                $columns = $cssSelectTable->select('td');

                $log = new Log();
                $log->setDate(\DateTime::createFromFormat('d/m/y', $columns[0]['innerHTML']));
                $log->setDescription($columns[1]['innerHTML']);
                $log->setDeliveryDepot($columns[2]['innerHTML']);
                $log->setRoundNumber($columns[3]['innerHTML']);
                $log->setDeliveryDate(\DateTime::createFromFormat('d/m/y', $columns[4]['innerHTML']));
                $log->setPackagesReceived(intval($columns[5]['innerHTML']));
                $log->setPackagesDelivered(intval($columns[6]['innerHTML']));
                $history->add($log);
            }

            $consignment->setLogs($history);
            $consignment->setStatus($consignment->getLogs()->getStatus());
        }



        $status = $consignment->getStatus();
        if($status === Consignment::STATUS_AWAITING_PICKUP) {
            //lets check if the scans
            $scans = $cssSelect->select('#grdScans .GridItem', false); //find the viewstate attribute

            if(count($scans) > 0) {
                $consignment->setStatus(Consignment::STATUS_IN_TRANSIT);
            }
        }
        elseif($status === Consignment::STATUS_DELIVERED) {
            //it's been delivered so there should be signatures
            $signatures = $cssSelect->select('#grdTimed .GridItem', false); //find the viewstate attribute
            $signatureCollection = new Consignment\Signatures();
            $consignment->setSignatures($signatureCollection);
            $count = count($signatures);

            $this->debug('Parsing Signatures - ' . $count . ' Found');
            for($i=0; $i < $count; $i++) {
                $cssSelectTable = new CssSelect($signatures[$i]['innerHTML']);
                $columns = $cssSelectTable->select('td');

                print_r($columns[1]['innerHTML'] . ' ' . $columns[2]['innerHTML']);
                $signature = new Consignment\Signatures\Signature();
                $signature->setSignature($columns[0]['innerHTML']);
                $signature->setDatetime(\DateTime::createFromFormat('d/m/y H:i:s', $columns[1]['innerHTML'] . ' ' . $columns[2]['innerHTML']));
                $signatureCollection->add($signature);
            }
        }

        //lets update the cache
        $this->setCachedObject(self::CACHE_URN_PREFIX . $consignment->getUrn(), $consignment, $this->_cacheConsignmentInterval); //lets store for 5 days

        return $consignment;
    }

    /**
     * Gets the ZPL data from tuffnells
     *
     * @param Consignment $consignment
     * @return Consignment\Label
     * @throws EndpointError
     * @throws Exceptions\InvalidConsignment
     */
    public function getLabels(Consignment $consignment) {
        $this->debug('Check if consignment labels are in cache - ' . $consignment->getUrn());
        $label = $this->getCachedObject(self::CACHE_LABEL_PREFIX . $consignment->getUrn());
        if($label) {
            return  new Consignment\Label($consignment, $label);
        }

        $this->debug('Get consignment labels - ' . $consignment->getUrn());

        $url = 'VistaPrint.aspx?URN=' . $consignment->getUrn() . '&Copies=1&Printer=\\127.0.0.1\ZEBRA&StartQty=';
        $response = $this->makeRequest('get', $url);

        preg_match('/AxVistaPrint\.CreateFile \("(.+)","(.+)","(.+)"\)/', $response->getBody()->getContents(), $matches);

        if(empty($matches[1])) {
            throw new EndpointError('Endpoint Display Error');
        }

        //remove tuffnells javascript guff
        $zpl = str_replace( "CRLF","\n", $matches[1]);
        $zpl = str_replace("%%@@","", $zpl);
        $label = new Consignment\Label($consignment, $zpl);

        $this->setCachedObject(self::CACHE_LABEL_PREFIX . $consignment->getUrn(), $zpl, new \DateInterval('PT24H')); //save for 24 hours

        return $label;
    }
}
