<?php

if (\OC::$CLI) {
    return;
}

$urlGenerator = \OC::$server->getURLGenerator();
$config = \OC::$server->getConfig();
$logger = \OC::$server->getLogger();

$libPath = $config->getSystemValue('simplesamlphp_path');
$autoloader = $libPath . '/lib/_autoload.php';

if (!$libPath || !file_exists($autoloader)) {
    $logger->logException(
        new InvalidArgumentException('Missing or invalid SimpleSAMLphp path configuration'),
        ['app' => 'user_simplesamlphp']
    );

    return;
}

require_once $autoloader;

$logoutUrl = $urlGenerator->linkToRouteAbsolute('core.login.logout', [
    'requesttoken' => \OCP\Util::callRegister()
]);

$authenticator = new \SimpleSAML_Auth_Simple($config->getSystemValue('simplesamlphp_sp_id', 'default-sp'));

$userBackend = new OCA\User_SimpleSAML\UserBackend(
    $authenticator,
    \OC::$server->getSession(),
    $logger,
    \OC::$server->getUserManager(),
    $config->getSystemValue('simplesamlphp_uid_attribute', 'uid'),
    $logoutUrl
);


\OC_User::useBackend($userBackend);
\OC_User::handleApacheAuth();

$loginUrl = $authenticator->getLoginURL($urlGenerator->linkTo('', 'index.php'));
\OC_App::registerLogIn(array('name' => 'SAML SSO', 'href' => $loginUrl));

