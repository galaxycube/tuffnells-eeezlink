<?php

namespace Tuffnells\Models\Consignment;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tuffnells\Exceptions\LabelaryError;
use Tuffnells\Models\Consignment;

class LabelTest extends TestCase
{
    private Label $_label;
    private string $_zplExample = "^XA
~SD15^PR12^LH0,0
^FO25,15^GB690,790,2^FS
^FO35,35^A0N,130,120^FDP1^FS
^FO635,25^FPR^A0N,120,130^FDFHS^FS
^FO520,120^A0N,32,90^FDR022^FS
^FO25,160^GB690,0,2^FS
^FO35,165^A0N,18,18^FDConsignment Ref.^FS
^FO35,190^A0N,64,64^FD1022^FS
^FO410,160^GB0,120,2^FS
^FO415,165^A0N,20,20^FD^FS
^FO415,187^A0N,20,20^FD^FS
^FO415,209^A0N,20,20^FD^FS
^FO415,231^A0N,20,20^FD^FS
^FO415,253^A0N,20,20^FD^FS
^FO25,280^GB690,0,2^FS
^FO225,285^A0N,24,24^FDDeliver To^FS
^FO650,285^A0N,24,24^FDEz.W^FS
^FO225,321^A0N,44,28^FD ROB OSBORNE^FS
^FO225,367^A0N,44,28^FDWILDGOOSE CONSTUCTION LTD^FS
^FO225,413^A0N,44,28^FDCAWDOR WAY^FS
^FO225,459^A0N,44,28^FDSITE OFFICE C/O MATLOCK ^FS
^FO225,506^A0N,44,28^FDSPA ROAD ^FS
^FO225,553^A0N,44,28^FDMATLOCK DERBYSHIRE^FS
^FO225,615^A0N,72,64^FDDE4 3SP^FS
^FO225,700^A0N,24,24^FDTel:^FS
^FO270,685^A0N,44,36^FD 07740 281417^FS
^LRY^FO650,615^GB63,0,40^FS
^FO655,620^A0N,40,32^FDRPO^FS
^LRN^FO650,560^GB0,170,2^FS
^LRY^FO650,425^GB63,0,40^FS
^FO665,430^A0N,40,32^FDT/L^FS
^LRN^FO650,465^GB0,55,2^FS
^FO665,470^A0N,52,52^FD-^FS
^LRY^FO650,520^GB63,0,40^FS
^FO658,525^A0N,40,32^FDALT^FS
^FO655,575^A0N,40,32^FD - ^FS
^FO220,280^GB0,450,2^FS
^FO35,290^A0N,64,64^SN001,1,Y^FS
^FO80,345^A0N,36,36^FDof^FS
^FO110,345^A0N,64,64^FD001^FS

^FO35,460^A0N,24,24^FDTotal Weight Kg^FS
^FO35,485^A0N,64,64^FD15^FS
^FO35,545^A0N,24,24^FDDespatched^FS
^FO35,570^A0N,36,52^FD29/01/21^FS
^FO25,605^GB195,0,2^FS
^FO35,610^A0N,28,48^FDTuffnells^FS
^FO35,635^A0N,18,26^FDParcels Express^FS
^FO45,650^A0N,20,30^FD^FS
^FO70,675^A0N,18,30^FD1494862^FS
^FO35,695^A0N,36,52^FD14/R003^FS
^FO25,730^GB690,0,2^FS
^FO35,735^A0N,60,55^FD579240 579240^FS
^FO725,80^BY4^BCB,50,Y,N,N,A^SN01028149486201291269001,1,Y^FS
^FO60,830^BY4^BCN,150,Y,N,N,A^SN01028149486201291269001,1,Y^FS
^PQ1,0,1
^XZ
";

    public function setUp(): void
    {
        $mock = new MockHandler([
            new Response(200, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $mockConsignment = $this->getMockBuilder(Consignment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockConsignment->method('getUrn')->willReturn('DUMMYURN');

        $this->_label = new Label($mockConsignment, $this->_zplExample, $client);
    }

    public function testGetZpl() {
        self::assertEquals($this->_zplExample,$this->_label->getZpl());
    }

    public function testGetPdf() {
        self::assertIsString($this->_label->getPdf());
    }

    public function testGetPng() {
        self::assertIsString($this->_label->getPng());
    }

    public function testServerNotFoundError() {
        $mock = new MockHandler([
            new Response(404, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->_label->setGuzzleClient($client);

        $this->expectException(LabelaryError::class);
        $this->testGetPdf();
    }

    public function testServerUnknownError() {
        $mock = new MockHandler([
            new Response(302, [], ''),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->_label->setGuzzleClient($client);

        $this->expectException(LabelaryError::class);
        $this->testGetPdf();
    }
}
