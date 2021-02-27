<?php

namespace Iodev\Whois\Modules\Tld;

use Iodev\Whois\Factory;
use PHPUnit\Framework\TestCase;

class TldServerTest extends TestCase
{
    private static function getServerClass(): string
    {
        return \Iodev\Whois\Modules\Tld\TldServer::class;
    }

    private static function getParser(): TldParser
    {
        return Factory::get()->createTldParserByClass(self::getParserClass());
    }

    private static function getParserClass(): string
    {
        return \Iodev\Whois\Modules\Tld\Parsers\TestCommonParser::class;
    }


    public function testConstructValid(): void
    {
        $instance = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertInstanceOf(TldServer::class, $instance);
    }

    public function testConstructEmptyZone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TldServer("", "some.host.com", false, self::getParser());
    }

    public function testConstructEmptyHost(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TldServer(".abc", "", false, self::getParser());
    }

    public function testGetZone(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertEquals(".abc", $s->getZone());
    }

    public function testGetHost(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertEquals("some.host.com", $s->getHost());
    }

    public function testIsCentralizedTrue(): void
    {
        $s = new TldServer(".abc", "some.host.com", true, self::getParser());
        self::assertTrue($s->isCentralized());

        $s = new TldServer(".abc", "some.host.com", 1, self::getParser());
        self::assertTrue($s->isCentralized());
    }

    public function testIsCentralizedFalse(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertFalse($s->isCentralized());

        $s = new TldServer(".abc", "some.host.com", 0, self::getParser());
        self::assertFalse($s->isCentralized());
    }

    public function testGetParserViaInstance(): void
    {
        $p = self::getParser();
        $s = new TldServer(".abc", "some.host.com", false, $p);
        self::assertSame($p, $s->getParser());
    }

    public function testIsDomainZoneValid(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertTrue($s->isDomainZone("some.abc"));
    }

    public function testIsDomainZoneValidComplex(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertTrue($s->isDomainZone("some.foo.bar.abc"));
    }

    public function testIsDomainZoneInvalid(): void
    {
        $s = new TldServer(".abc", "some.host.com", false, self::getParser());
        self::assertFalse($s->isDomainZone("some.com"));
    }

    public function testIsDomainZoneInvalidEnd(): void
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser());
        self::assertFalse($s->isDomainZone("some.bar"));
    }

    public function testBuildDomainQueryDefault(): void
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser());
        self::assertEquals("domain.com\r\n", $s->buildDomainQuery("domain.com"));
    }

    public function testBuildDomainQueryNull()
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser(), null);
        self::assertEquals("site.com\r\n", $s->buildDomainQuery("site.com"));
    }

    public function testBuildDomainQueryEmpty(): void
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser(), "");
        self::assertEquals("some.com\r\n", $s->buildDomainQuery("some.com"));
    }

    public function testBuildDomainQueryCustom()
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser(), "prefix %s suffix\r\n");
        self::assertEquals("prefix domain.com suffix\r\n", $s->buildDomainQuery("domain.com"));
    }

    public function testBuildDomainQueryCustomNoParam(): void
    {
        $s = new TldServer(".foo.bar", "some.host.com", false, self::getParser(), "prefix suffix\r\n");
        self::assertEquals("prefix suffix\r\n", $s->buildDomainQuery("domain.com"));
    }

    public function testFromDataFullArgs(): void
    {
        $s = TldServer::fromData([
            "zone" => ".abc",
            "host" => "some.host",
            "centralized" => true,
            "parserClass" => self::getParserClass(),
            "queryFormat" => "prefix %s suffix\r\n",
        ]);

        self::assertEquals(".abc", $s->getZone());
        self::assertEquals("some.host", $s->getHost());
        self::assertTrue($s->isCentralized());
        self::assertInstanceOf(self::getParserClass(), $s->getParser());
        self::assertEquals("prefix %s suffix\r\n", $s->getQueryFormat());
    }

    public function testFromDataZoneHostOnly(): void
    {
        $s = TldServer::fromData(["zone" => ".abc", "host" => "some.host"], self::getParser());

        self::assertEquals(".abc", $s->getZone());
        self::assertEquals("some.host", $s->getHost());
        self::assertFalse($s->isCentralized());
        self::assertInstanceOf(self::getParserClass(), $s->getParser());
    }

    public function testFromDataMissingZone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TldServer::fromData(["host" => "some.host"], self::getParser());
    }

    public function testFromDataMissingHost(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TldServer::fromData(["zone" => ".abc"], self::getParser());
    }

    public function testFromDataMissingAll(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TldServer::fromData([], self::getParser());
    }

    public function testFromDataListOne(): void
    {
        $s = TldServer::fromDataList(
            [["zone" => ".abc", "host" => "some.host"]],
            self::getParser()
        );
        self::assertIsArray($s, "Array expected");
        self::assertCount(1, $s);
        self::assertInstanceOf(self::getServerClass(), $s[0]);
        self::assertEquals(".abc", $s[0]->getZone());
        self::assertEquals("some.host", $s[0]->getHost());
        self::assertInstanceOf(self::getParserClass(), $s[0]->getParser());
    }

    public function testFromDataListTwo(): void
    {
        $s = TldServer::fromDataList([
            ["zone" => ".abc", "host" => "some.host"],
            ["zone" => ".cde", "host" => "other.host", "centralized" => true, "queryFormat" => "prefix %s suffix\r\n"],
        ],
            self::getParser()
        );
        self::assertIsArray($s, "Array expected");
        self::assertCount(2, $s);

        self::assertInstanceOf(self::getServerClass(), $s[0]);
        self::assertEquals(".abc", $s[0]->getZone());
        self::assertEquals("some.host", $s[0]->getHost());
        self::assertFalse($s[0]->isCentralized());
        self::assertInstanceOf(self::getParserClass(), $s[0]->getParser());

        self::assertInstanceOf(self::getServerClass(), $s[1]);
        self::assertEquals(".cde", $s[1]->getZone());
        self::assertEquals("other.host", $s[1]->getHost());
        self::assertTrue($s[1]->isCentralized());
        self::assertInstanceOf(self::getParserClass(), $s[1]->getParser());
        self::assertEquals("prefix %s suffix\r\n", $s[1]->getQueryFormat());
    }
}
