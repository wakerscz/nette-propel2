<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


require __DIR__.'/../../../../vendor/autoload.php';

$app = new \Symfony\Component\Console\Application;

$finder = (new \Symfony\Component\Finder\Finder())->files()->name('*.php')
    ->in(__DIR__.'/../../../../vendor/propel/propel/src/Propel/Generator/Command')->depth(0);

$ns = '\\Propel\\Generator\\Command\\';

foreach ($finder as $file)
{
    /**
     * @var SplFileInfo $file
     */
    $r  = new \ReflectionClass($ns.$file->getBasename('.php'));

    if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract())
    {
        /**
         * @var $command \Symfony\Component\Console\Command\Command
         */

        $command = $r->newInstance();
        $command->setName('propel:' . $command->getName());
        $command->setAliases([]);

        $nativeDefinition = $command->getNativeDefinition();

        if(isset($nativeDefinition->getOptions()['config-dir']))
        {
            $nativeDefinition->getOptions()['config-dir']->setDefault(__DIR__ . '/../src/config/scam.php');
        }

        $app->add($command);
    }
}

$app->run();