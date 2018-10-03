<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 * Propel pro supštění CLI příkazů potřebuje uvést cestu ke konfiguračnímu souboru, ovšem neumí přečíst NEON formát.
 * Nelze do Propelu přidat vlastní FileLoader (pro NEON) - je tedy potřeba .neon soubor převést do PHP Array a podstrčit
 * jej Propelu jako config napsaný v PHP. Ten již umí číst.
 *
 */

if (php_sapi_name() === 'cli')
{
    return \Wakers\Propel\Setup\PropelSetup::getConfigAsPhpArray();
}

return NULL;