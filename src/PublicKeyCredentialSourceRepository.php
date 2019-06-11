<?php

namespace SilverStripe\WebAuthn;

use InvalidArgumentException;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Security\Member;
use TypeError;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * This interface is required by the WebAuthn library but is too exhaustive for our "one security key per person"
 * registration. We only support one and it's stored on the registered method that's a dependency of the constructor.
 *
 * Note that this class currently extends CredentialRepository, which is deprecated and may be removed in a minor
 * version release of this module. As such, you should only rely on methods that are defined in the
 * \Webauthn\PublicKeyCredentialSourceRepository interface.
 */
class PublicKeyCredentialSourceRepository extends CredentialRepository
    implements \Webauthn\PublicKeyCredentialSourceRepository
{
    use Injectable;

    /**
     * @var PublicKeyCredential
     */
    protected $credential;

    /**
     * @var RegisteredMethod
     */
    protected $registeredMethod;

    /**
     * @var Member
     */
    protected $member;

    /**
     * @param PublicKeyCredential $credential
     * @param Member $member
     * @param RegisteredMethod $registeredMethod
     */
    public function __construct(
        PublicKeyCredential $credential,
        Member $member,
        RegisteredMethod $registeredMethod = null
    ) {
        $this->credential = $credential;
        $this->member = $member;
        if (!$registeredMethod) {
            $registeredMethod = RegisteredMethod::create();
        }
        $this->registeredMethod = $registeredMethod;
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::createFromPublicKeyCredential($this->credential, (string) $this->member->ID);
    }

    /**
     * Note: Our implementation only uses one credential at a time.
     *
     * @param PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return [
            $this->findOneByCredentialId($publicKeyCredentialUserEntity->getId()),
        ];
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $this->registeredMethod->Data = json_encode($publicKeyCredentialSource);
        $this->registeredMethod->write();
    }

    /**
     * Extracts the stored method data from the RegisteredMethod's serialised JSON store
     *
     * @return array
     */
    protected function getCredentialData(): array
    {
        if (!$this->registeredMethod) {
            return [];
        }

        try {
            return json_decode($this->registeredMethod->Data, true);
        } catch (TypeError $error) {
            return [];
        }
    }

    /**
     * @param string $credentialId
     * @throws InvalidArgumentException
     */
    protected function assertCredentialID(string $credentialId): void
    {
        if (!$this->has($credentialId)) {
            throw new InvalidArgumentException('Given credential ID does not match any database record');
        }
    }
}
