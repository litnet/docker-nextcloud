<?php

namespace OCA\User_SimpleSAML;

use OCP\Authentication\IApacheBackend;
use OCP\ILogger;
use OCP\ISession;

class UserBackend extends \OC_User_Backend implements IApacheBackend
{
    private $authenticator;
    private $session;
    private $logger;
    private $userManager;
    private $uidAttribute;
    private $defaultLogoutUrl;

    public function __construct(
        \SimpleSAML_Auth_Simple $authenticator,
        ISession $session,
	ILogger $logger,
	$userManager,
        $uidKey,
        $defaultLogoutUrl)
    {
        $this->authenticator = $authenticator;
        $this->session = $session;
	$this->logger = $logger;
	$this->userManager = $userManager;
        $this->uidAttribute = $uidKey;
        $this->defaultLogoutUrl = $defaultLogoutUrl;
    }

    public function isSessionActive()
    {	
    	return $this->authenticator->isAuthenticated();
    }

    public function getCurrentUserId()
    {
        $attributes = $this->authenticator->getAttributes();

        if (!isset($attributes[$this->uidAttribute])) {
            $exception = new \InvalidArgumentException("Could not find attribute {$this->uidAttribute} in SAMLResponse");
            $this->logger->logException(
                $exception,
                ['message' => 'Exception during user authentication using User_SimpleSAML']
            );

            $this->session->set('loginMessages', [[], ['Missing user identity in SAMLResponse']]);
            return null;
	}

	$username = $attributes[$this->uidAttribute]['0'];
	
	if (!$this->userManager->userExists($username)) {
	    $this->session->set('loginMessages', [[], ['You are not authorized to access the system']]);
	    return null;	    
	}

	return $username;
    }

    public function getLogoutAttribute()
    {
        return "href='{$this->authenticator->getLogoutURL($this->defaultLogoutUrl)}'";
    }

    public function getLogoutUrl()
    {
	return $this->authenticator->getLogoutURL($this->defaultLogoutUrl);
    }

    public function getDisplayName($uid)
    {
        return $uid;
    }
}

