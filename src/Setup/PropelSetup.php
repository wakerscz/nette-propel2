<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\Propel\Setup;


use Nette\Neon\Neon;
use Propel\Common\Config\ConfigurationManager;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Nette\DI\Container;
use Propel\Runtime\Propel;
use Tracy\Debugger;
use Wakers\Propel\Debugger\DebuggerPanel;
use Wakers\Propel\Debugger\Logger;


class PropelSetup
{
    /**
     * Cesta ke konfiguračnímu souboru
     * Musí být definována konstantou (nesmí se měnit)
     */
    const NEON_CONFIG_PATH = __DIR__.'/../../../../../app/config/db.local.neon';


    /**
     * Vrací nastavení Propelu ze souboru db.local.neon
     * @param string $path
     * @return array
     */
    public static function getAsArray() : array
    {
        $configPath = realpath(self::NEON_CONFIG_PATH);
        $config = Neon::decode(file_get_contents('nette.safe://' . $configPath))['wakers-propel'];
        return $config;
    }


    /**
     *
     * Připojí propel k DB a nastaví výchozí připojení.
     *
     * @param Container $container
     * @throws \ReflectionException
     */
    public static function setup(Container $container) : void
    {
        $config = self::getAsArray();
        $configurationManager = new ConfigurationManager(NULL, $config);

        // Výchozí DB a její adapter
        $defaultConnection = $configurationManager->getConfigProperty('runtime.defaultConnection');
        $adapter = $configurationManager->getConfigProperty('database.connections')[$defaultConnection]['adapter'];


        // Nastavení connection manageru
        $manager = new ConnectionManagerSingle;
        $manager->setConfiguration($configurationManager->getConnectionParametersArray()[$defaultConnection]);
        $manager->setName($defaultConnection);


        // Připojení manageru do service containeru
        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->setAdapterClass($defaultConnection, $adapter);
        $serviceContainer->setConnectionManager($defaultConnection, $manager);
        $serviceContainer->setDefaultDatasource($defaultConnection);


        // Nastavení debug módu
        if ($container->parameters['debugMode'])
        {
            $connection = $serviceContainer->getConnection();
            $connection->useDebug(TRUE);

            $serviceContainer->setLogger('defaultLogger', new Logger);

            Debugger::getBar()->addPanel(new DebuggerPanel);
        }
    }
}