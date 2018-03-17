<?php

namespace tests\Server;

use PHPUnit\Framework\TestCase;
use Reamp\Server\ConnectionInterface;
use Reamp\Server\FlashPolicy;

/**
 * @covers \Reamp\Server\FlashPolicy
 */
class FlashPolicyComponentTest extends TestCase {
    protected $_policy;

    public function setUp() {
        $this->_policy = new FlashPolicy();
    }

    public function testPolicyRender() {
        $this->_policy->setSiteControl('all');
        $this->_policy->addAllowedAccess('example.com', '*');
        $this->_policy->addAllowedAccess('dev.example.com', '*');

        self::assertInstanceOf('SimpleXMLElement', $this->_policy->renderPolicy());
    }

    public function testInvalidPolicyReader() {
        $this->expectException('UnexpectedValueException');
        $this->_policy->renderPolicy();
    }

    public function testInvalidDomainPolicyReader() {
        $this->expectException('UnexpectedValueException');
        $this->_policy->setSiteControl('all');
        $this->_policy->addAllowedAccess('dev.example.*', '*');
        $this->_policy->renderPolicy();
    }

    /**
     * @dataProvider siteControl
     */
    public function testSiteControlValidation($accept, $permittedCrossDomainPolicies) {
        self::assertEquals($accept, $this->_policy->validateSiteControl($permittedCrossDomainPolicies));
    }

    public static function siteControl() {
        return [
            [true, 'all'], [true, 'none'], [true, 'master-only'], [false, 'by-content-type'], [false, 'by-ftp-filename'], [false, ''], [false, 'all '], [false, 'asdf'], [false, '@893830'], [false, '*']
        ];
    }

    /**
     * @dataProvider URI
     */
    public function testDomainValidation($accept, $domain) {
        self::assertEquals($accept, $this->_policy->validateDomain($domain));
    }

    public static function URI() {
        return [
            [true, '*'], [true, 'example.com'], [true, 'exam-ple.com'], [true, '*.example.com'], [true, 'www.example.com'], [true, 'dev.dev.example.com'], [true, 'http://example.com'], [true, 'https://example.com'], [true, 'http://*.example.com'], [false, 'exam*ple.com'], [true, '127.0.255.1'], [true, 'localhost'], [false, 'www.example.*'], [false, 'www.exa*le.com'], [false, 'www.example.*com'], [false, '*.example.*'], [false, 'gasldf*$#a0sdf0a8sdf']
        ];
    }

    /**
     * @dataProvider ports
     */
    public function testPortValidation($accept, $ports) {
        self::assertEquals($accept, $this->_policy->validatePorts($ports));
    }

    public static function ports() {
        return [
            [true, '*'], [true, '80'], [true, '80,443'], [true, '507,516-523'], [true, '507,516-523,333'], [true, '507,516-523,507,516-523'], [false, '516-'], [true, '516-523,11'], [false, '516,-523,11'], [false, 'example'], [false, 'asdf,123'], [false, '--'], [false, ',,,'], [false, '838*']
        ];
    }

    public function testAddAllowedAccessOnlyAcceptsValidPorts() {
        $this->expectException('UnexpectedValueException');

        $this->_policy->addAllowedAccess('*', 'nope');
    }

    public function testSetSiteControlThrowsException() {
        $this->expectException('UnexpectedValueException');

        $this->_policy->setSiteControl('nope');
    }

    public function testErrorClosesConnection() {
        $conn = $this->createMock(ConnectionInterface::class);
        $conn->expects($this->once())->method('close');

        $this->_policy->onError($conn, new \Exception);
    }

    public function testOnMessageSendsString() {
        $this->_policy->addAllowedAccess('*', '*');

        $conn = $this->createMock(ConnectionInterface::class);
        $conn->expects($this->once())->method('send')->with($this->isType('string'));

        $this->_policy->onMessage($conn, ' ');
    }

    public function testOnOpenExists() {
        self::assertTrue(\method_exists($this->_policy, 'onOpen'));
        $conn = $this->createMock(ConnectionInterface::class);
        $this->_policy->onOpen($conn);
    }

    public function testOnCloseExists() {
        self::assertTrue(\method_exists($this->_policy, 'onClose'));
        $conn = $this->createMock(ConnectionInterface::class);
        $this->_policy->onClose($conn);
    }
}
