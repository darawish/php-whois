<?php

namespace Iodev\Whois\Modules\Tld;

use PHPUnit\Framework\TestCase;

class TldInfoTest extends TestCase
{
    public function testConstructEmptyData(): void
    {
        $instance = new TldInfo(self::getResponse(), []);
        $this->assertInstanceOf(TldInfo::class, $instance);
    }

    private static function getResponse(): TldResponse
    {
        return new TldResponse([
            "domain" => "domain.com",
            "query" => "domain.com",
            "text" => "Hello world",
        ]);
    }

    public function testGetResponse(): void
    {
        $r = self::getResponse();
        $i = new TldInfo($r, []);
        self::assertSame($r, $i->getResponse());
    }

    public function testGetDomainName(): void
    {
        $i = self::createInfo(["domainName" => "foo.bar"]);
        self::assertEquals("foo.bar", $i->domainName);
    }

    private static function createInfo($data = []): TldInfo
    {
        return new TldInfo(self::getResponse(), $data);
    }

    public function testGetDomainNameDefault(): void
    {
        $i = self::createInfo();
        self::assertSame("", $i->domainName);
    }


    public function testGetDomainNameUnicode(): void
    {
        $i = self::createInfo(["domainName" => "foo.bar"]);
        self::assertEquals("foo.bar", $i->getDomainNameUnicode());
    }

    public function testGetDomainNameUnicodePunnycode(): void
    {
        $i = self::createInfo(["domainName" => "xn--d1acufc.xn--p1ai"]);
        self::assertEquals("домен.рф", $i->getDomainNameUnicode());
    }

    public function testGetDomainNameUnicodeDefault(): void
    {
        $i = self::createInfo();
        self::assertSame("", $i->getDomainNameUnicode());
    }


    public function testGetWhoisServer(): void
    {
        $i = self::createInfo(["whoisServer" => "whois.bar"]);
        self::assertEquals("whois.bar", $i->whoisServer);
    }

    public function testGetWhoisServerDefault(): void
    {
        $i = self::createInfo();
        self::assertSame("", $i->whoisServer);
    }


    public function testGetNameServers(): void
    {
        $i = self::createInfo(["nameServers" => ["a.bar", "b.baz"]]);
        self::assertEquals(["a.bar", "b.baz"], $i->nameServers);
    }

    public function testGetNameServersDefault(): void
    {
        $i = self::createInfo();
        self::assertSame([], $i->nameServers);
    }


    public function testGetCreationDate(): void
    {
        $i = self::createInfo(["creationDate" => 123456789]);
        self::assertEquals(123456789, $i->creationDate);
    }

    public function testGetCreationDateDefault(): void
    {
        $i = self::createInfo();
        self::assertSame(0, $i->creationDate);
    }


    public function testGetExpirationDate(): void
    {
        $i = self::createInfo(["expirationDate" => 123456789]);
        self::assertEquals(123456789, $i->expirationDate);
    }

    public function testGetExpirationDateDefault(): void
    {
        $i = self::createInfo();
        self::assertSame(0, $i->expirationDate);
    }


    public function testGetStates(): void
    {
        $i = self::createInfo(["states" => ["abc", "def", "ghi"]]);
        self::assertEquals(["abc", "def", "ghi"], $i->states);
    }

    public function testGetStatesDefault(): void
    {
        $i = self::createInfo();
        self::assertSame([], $i->states);
    }


    public function testGetOwner(): void
    {
        $i = self::createInfo(["owner" => "Some Company"]);
        self::assertEquals("Some Company", $i->owner);
    }

    public function testGetOwnerDefault(): void
    {
        $i = self::createInfo();
        self::assertSame("", $i->owner);
    }


    public function testGetRegistrar(): void
    {
        $i = self::createInfo(["registrar" => "Some Registrar"]);
        self::assertEquals("Some Registrar", $i->registrar);
    }

    public function testGetRegistrarDefault(): void
    {
        $i = self::createInfo();
        self::assertSame("", $i->registrar);
    }
}
