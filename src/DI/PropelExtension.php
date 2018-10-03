<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\Propel\DI;


use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;


class PropelExtension extends CompilerExtension
{
    /**
     * Nastartuje Propel
     * @param ClassType $class
     */
    public function afterCompile(ClassType $class) : void
    {
        $initialize = $class->methods['initialize'];
        $initialize->addBody(\Wakers\Propel\Setup\PropelSetup::class . '::setup($this);');
    }
}