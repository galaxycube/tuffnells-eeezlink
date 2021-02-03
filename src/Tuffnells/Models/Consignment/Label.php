<?php


namespace Tuffnells\Models\Consignment;


use GuzzleHttp\Client;
use Tuffnells\Exceptions\LabelaryError;
use Tuffnells\Models\Consignment;

class Label
{
    /**
     * @var Consignment
     */
    private Consignment $_consignment;

    /**
     * @var string
     */
    private string $_rawZpl;

    /**
     * @var Client
     */
    private Client $_guzzleClient;

    /**
     * Label constructor.
     * @param Consignment $consignment
     * @param string $rawZPL
     */
    public function __construct(Consignment $consignment, string $rawZpl, Client $client = null)
    {
        $this->_consignment = $consignment;
        $this->_rawZpl = $rawZpl;
        $this->_guzzleClient = $client ?? new Client();
    }

    /**
     * @param Client $guzzleClient
     * @return Label
     */
    public function setGuzzleClient(Client $guzzleClient): Label
    {
        $this->_guzzleClient = $guzzleClient;
        return $this;
    }

    /**
     * @param string $type
     * @return string
     * @throws LabelaryError
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Tuffnells\Exceptions\InvalidConsignment
     */
    private function makeLabelaryRequest(string $type) : string {
        try {
            $response = $this->_guzzleClient->post('http://api.labelary.com/v1/printers/8dpmm/labels/4x6/0/', [
                'headers' => [
                    'Accept' => $type,
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => $this->getZpl(),
                        'filename' => $this->_consignment->getUrn()
                    ],
                ]
            ]);
        }
        catch(\Exception $e) {
            throw new LabelaryError($e->getMessage());
        }

        if($response->getStatusCode() !== 200) {
            throw new LabelaryError('Response Code - ' . $response->getStatusCode());
        }

        return $response->getBody()->getContents();
    }

    /**
     * Returns PNG string
     *
     * @return string
     */
    public function getPng(): string {
        return $this->makeLabelaryRequest('image/png');
    }

    /**
     * Returns PDF string
     *
     * @return string
     */
    public function getPdf(): string {
        return $this->makeLabelaryRequest('application/pdf');
    }

    /**
     * Returns ZPL string
     *
     * @return string
     */
    public function getZpl():string {
        return $this->_rawZpl;
    }
}