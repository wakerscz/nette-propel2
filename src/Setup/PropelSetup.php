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
     * Podpora pro ::getenv() v neon souborech
     * @param $content
     * @return string
     */
    protected static function replaceEnviromentVariables($content) : string
    {
        preg_match_all("/\:\:getenv\(\'(.*)\'\)/", $content, $result);

        $findEnvs = [];

        foreach ($result[1] as $envVar)
        {
            if(!in_array($envVar, $findEnvs))
            {
                $toReplace = "::getenv('{$envVar}')";
                $replaceWith = getenv($envVar);
                $content = str_replace($toReplace, $replaceWith, $content);
                unset($replaceWith);
            }
        }

        return $content;
    }


    /**
     * Vrací nastavení Propelu ze souboru db.local.neon
     * @return array
     */
    public static function getAsArray() : array
    {
        $configPath = realpath(self::NEON_CONFIG_PATH);
        $content = file_get_contents('nette.safe://' . $configPath);
        $content = self::replaceEnviromentVariables($content);
        $config = Neon::decode($content)['wakers-propel'];

        return $config;
    }


    /**
     *
     * Připojí propel k DB a nastaví výchozí připojení.
     * @param Container $container
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