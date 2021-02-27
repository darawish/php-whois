<?php

namespace Iodev\Whois\Modules\Tld;

use Iodev\Whois\Loaders\FakeSocketLoader;
use Iodev\Whois\Factory;
use PHPUnit\Framework\TestCase;

class TldModuleServerTest extends TestCase
{
    /**
     * @param $zone
     * @return TldServer
     */
    private static function createServer($zone): TldServer
    {
        $parser = Factory::get()->createTldParser();
        return new TldServer($zone, "some.host.net", false, $parser);
    }

    /** @var TldModule */
    private $mod;


    public function setUp(): void
    {
        $this->mod = new TldModule(new FakeSocketLoader());
    }

    public function tearDown(): void
    {
    }


    public function testAddServersReturnsSelf(): void
    {
        $res = $this->mod->addServers([self::createServer(".abc")]);
        self::assertSame($this->mod, $res, "Result must be self reference");
    }

    public function testMatchServersQuietEmpty(): void
    {
        $servers = $this->mod->matchServers("domain.com", true);
        self::assertIsArray($servers, "Result must be Array");
        self::assertCount(0, $servers, "Count must be zero");
    }

    public function testMatchServersOne(): void
    {
        $s = self::createServer(".com");
        $this->mod->addServers([$s]);
        $servers = $this->mod->matchServers("domain.com");
        self::assertIsArray($servers, "Result must be Array");
        self::assertCount(1, $servers, "Count must be 1");
        self::assertSame($servers[0], $s, "Wrong matched server");
    }

    public function testMatchServersSome(): void
    {
        $s = self::createServer(".com");
        $this->mod->addServers([
            self::createServer(".net"),
            self::createServer(".com"),
            self::createServer(".net"),
            self::createServer(".com"),
            self::createServer(".su"),
            $s,
            self::createServer(".com"),
            self::createServer(".gov"),
        ]);

        $servers = $this->mod->matchServers("domain.com");
        self::assertIsArray($servers, "Result must be Array");
        self::assertCount(4, $servers, "Count of matched servers not equals");
        self::assertContains($s, $servers, "Server not matched");
    }

    public function testMatchServersQuietNoneInSome(): void
    {
        $this->mod->addServers([
            self::createServer(".net"),
            self::createServer(".com"),
            self::createServer(".net"),
            self::createServer(".com"),
            self::createServer(".su"),
            self::createServer(".com"),
            self::createServer(".gov"),
        ]);

        $servers = $this->mod->matchServers("domain.xyz", true);
        self::assertIsArray($servers, "Result must be Array");
        self::assertCount(0, $servers, "Count of matched servers must be zero");
    }

    public function testMatchServersCollisionLongest(): void
    {
        $this->mod->addServers([
            self::createServer(".com"),
            self::createServer(".bar.com"),
            self::createServer(".foo.bar.com"),
        ]);
        $servers = $this->mod->matchServers("domain.foo.bar.com");

        self::assertCount(3, $servers, "Count of matched servers not equals");
        self::assertEquals(".foo.bar.com", $servers[0]->getZone(), "Invalid matched zone");
        self::assertEquals(".bar.com", $servers[1]->getZone(), "Invalid matched zone");
        self::assertEquals(".com", $servers[2]->getZone(), "Invalid matched zone");
    }

    public function testMatchServersCollisionMiddle(): void
    {
        $this->mod->addServers([
            self::createServer(".com"),
            self::createServer(".bar.com"),
            self::createServer(".foo.bar.com"),
        ]);
        $servers = $this->mod->matchServers("domain.bar.com");

        self::assertCount(2, $servers, "Count of matched servers not equals");
        self::assertEquals(".bar.com", $servers[0]->getZone(), "Invalid matched zone");
        self::assertEquals(".com", $servers[1]->getZone(), "Invalid matched zone");
    }

    public function testMatchServersCollisionShorter(): void
    {
        $this->mod->addServers([
            self::createServer(".com"),
            self::createServer(".bar.com"),
            self::createServer(".foo.bar.com"),
        ]);
        $servers = $this->mod->matchServers("domain.com");

        self::assertCount(1, $servers, "Count of matched servers not equals");
        self::assertEquals(".com", $servers[0]->getZone(), "Invalid matched zone");
    }

    public function testMatchServersCollisiondWildcard(): void
    {
        $this->mod->addServers([
            self::createServer(".com"),
            self::createServer(".*.com"),
        ]);
        $servers = $this->mod->matchServers("domain.com");

        self::assertCount(1, $servers, "Count of matched servers not equals");
        self::assertEquals(".com", $servers[0]->getZone(), "Invalid matched zone");
    }

    public function testMatchServersCollisionMissingZone(): void
    {
        $this->mod->addServers([
            self::createServer(".com"),
            self::createServer(".bar.com"),
        ]);
        $servers = $this->mod->matchServers("domain.foo.bar.com");

        self::assertCount(2, $servers, "Count of matched servers not equals");
        self::assertEquals(".bar.com", $servers[0]->getZone(), "Invalid matched zone");
        self::assertEquals(".com", $servers[1]->getZone(), "Invalid matched zone");
    }

    public function testMatchServersCollisionFallback(): void
    {
        $this->mod->addServers([
            self::createServer(".*"),
            self::createServer(".*.foo"),
            self::createServer(".*.com"),
            self::createServer(".bar.*"),
            self::createServer(".foo.*.*"),
            self::createServer(".bar.com"),
        ]);
        $servers = $this->mod->matchServers("domain.foo.bar.com");

        self::assertCount(5, $servers, "Count of matched servers not equals");
        self::assertEquals(".foo.*.*", $servers[0]->getZone(), "Invalid matched zone");
        self::assertEquals(".bar.com", $servers[1]->getZone(), "Invalid matched zone");
        self::assertEquals(".bar.*", $servers[2]->getZone(), "Invalid matched zone");
        self::assertEquals(".*.com", $servers[3]->getZone(), "Invalid matched zone");
        self::assertEquals(".*", $servers[4]->getZone(), "Invalid matched zone");
    }
}
