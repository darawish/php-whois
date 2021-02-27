<?php

namespace Iodev\Whois\Modules\Tld;

use PHPUnit\Framework\TestCase;

class TldResponseTest extends TestCase
{
    /** @var TldResponse */
    private $resp;

    public function setUp(): void
    {
        $this->resp = new TldResponse([
            "domain" => "domain.some",
            "host" => "whois.host.abc",
            "query" => "domain.some",
            "text" => "Test content",
        ]);
    }

    public function testGetDomain(): void
    {
        self::assertEquals("domain.some", $this->resp->domain);
    }

    public function testGetQuery(): void
    {
        self::assertEquals("domain.some", $this->resp->query);
    }

    public function testGetText(): void
    {
        self::assertEquals("Test content", $this->resp->text);
    }

    public function testGetHost(): void
    {
        self::assertEquals("whois.host.abc", $this->resp->host);
    }
}