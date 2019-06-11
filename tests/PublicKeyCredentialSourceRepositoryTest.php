<?php

namespace SilverStripe\WebAuthn\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Security\Member;
use SilverStripe\WebAuthn\CredentialRepository;
use SilverStripe\WebAuthn\PublicKeyCredentialSourceRepository;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorResponse;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;

class PublicKeyCredentialSourceRepositoryTest extends SapphireTest
{
    protected $usesDatabase = true;

    /**
     * @var Member
     */
    protected $member;

    /**
     * @var RegisteredMethod
     */
    protected $registeredMethod;

    /**
     * @var CredentialRepository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->member = new Member();
        $this->registeredMethod = new RegisteredMethod();
    }

    public function testFindOneByCredentialIdReturnsPublicKeyCredentialSource()
    {
        $this->registeredMethod->Data = json_encode([
            'data' => ['credentialId' => base64_encode('foobar')],
        ]);

        /** @var PublicKeyCredential&PHPUnit_Framework_MockObject_MockObject $credentialMock */
        $credentialMock = $this->createMock(PublicKeyCredential::class);
        $credentialMock->method('getResponse')->willReturn(
            $this->createMock(AuthenticatorAttestationResponse::class)
        );

        $repository = new PublicKeyCredentialSourceRepository(
            $credentialMock,
            $this->member,
            $this->registeredMethod
        );

        $this->assertInstanceOf(
            PublicKeyCredentialSource::class,
            $repository->findOneByCredentialId('foobar')
        );
    }

    public function testFindAllForUserEntity()
    {

    }


    public function testSaveCredentialSource()
    {

    }
}
