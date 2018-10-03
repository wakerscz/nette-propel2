<?php
/**
 * Copyright (c) 2018 Wakers.cz
 *
 * @author Jiří Zapletal (http://www.wakers.cz, zapletal@wakers.cz)
 *
 */


namespace Wakers\Propel\Debugger;


use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Propel;
use Tracy\IBarPanel;


class DebuggerPanel implements IBarPanel
{
    /**
     * Pouze odstraní string SLOW
     * @param ConnectionInterface $connection
     * @return string
     */
    private function getProfile(ConnectionInterface $connection) : string
    {
        return str_replace('SLOW', '',  $connection->getProfiler()->getProfile());
    }


    /**
     * Fix pro nulový čas - jinak ukazuje: 1,540,000,000s
     * Né jen z tohoto důvodu by bylo dobré udělat vlastní Profiler
     * Pokud někdo tuší, jak jej do Propelu dostat, ať se ozve, nebo pošle pull-request.
     * @param ConnectionInterface $connection
     * @param string $profile
     * @return string
     */
    private function getTime(ConnectionInterface $connection, string $profile) : string
    {
        $realTime = str_replace('Time:', '', explode('|', $profile)[0]);

        if ($connection->getQueryCount() === 0)
        {
            $realTime = '0ms';
        }

        return $realTime;
    }


    /**
     * Renders HTML code for custom tab.
     * @return string
     */
    function getTab() : string
    {
        $connection = Propel::getConnection();
        $profile = $this->getProfile($connection);

        $html = "

            <span title=\"Propel ORM\">
                <img height=\"16px\" src=\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyBjbGFzcz0iaWNvbiIgaGVpZ2h0PSI1MTIiIHZpZXdCb3g9IjAgMCAxMDI0IDEwMjQiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNODk2LjQ4MzI1NiAxOTEuNDY3NzUzdjY0LjA4MTQ4MWMwIDcwLjU4NDU5Mi0xNzIuMjE0MjQ3IDEyOC4xNjA5MTUtMzg0LjQ4NDc5MSAxMjguMTYwOTE1LTIxMi4yNjc0NzUgMC0zODQuNDgzNzY4LTU3LjU3NzM0Ny0zODQuNDgzNzY4LTEyOC4xNjA5MTV2LTY0LjA4MTQ4MWMwLTcwLjU4MzU2OCAxNzIuMjE2MjkzLTEyOC4xNjA5MTUgMzg0LjQ4Mzc2OC0xMjguMTYwOTE1IDIxMi4yNzA1NDUgMCAzODQuNDg0NzkxIDU3LjU3NzM0NyAzODQuNDg0NzkxIDEyOC4xNjA5MTV6IG0wIDE3MS4yMTM0NTN2ODUuMTA5NGMwIDcwLjU4MzU2OC0xNzIuMjE0MjQ3IDEyOC4xNjA5MTUtMzg0LjQ4NDc5MSAxMjguMTYwOTE1LTIxMi4yNjc0NzUgMC0zODQuNDgzNzY4LTU3LjU3NzM0Ny0zODQuNDgzNzY4LTEyOC4xNjA5MTV2LTg1LjEwOTRjODIuNjA4NDM5IDU4LjA3MzY1IDIzMy43OTQ3NjcgODUuMTA5NCAzODQuNDgzNzY4IDg1LjEwOTQgMTUwLjY5MTA0NyAwIDMwMS44NzgzOTgtMjcuMDM1NzUgMzg0LjQ4NDc5MS04NS4xMDk0eiBtMCAxOTIuMjQxMzcydjg1LjExMDQyNGMwIDcwLjU4NDU5Mi0xNzIuMjE0MjQ3IDEyOC4xNTg4NjgtMzg0LjQ4NDc5MSAxMjguMTU4ODY4LTIxMi4yNjc0NzUgMC0zODQuNDgzNzY4LTU3LjU3NTMtMzg0LjQ4Mzc2OC0xMjguMTU4ODY4di04NS4xMTA0MjRjODIuNjA4NDM5IDU4LjA3MzY1IDIzMy43OTQ3NjcgODUuMTEwNDIzIDM4NC40ODM3NjggODUuMTEwNDI0IDE1MC42OTEwNDcgMCAzMDEuODc4Mzk4LTI3LjAzNjc3MyAzODQuNDg0NzkxLTg1LjExMDQyNHogbTAgMTkyLjI0MjM5NnY4NS4xMTI0N2MwIDcwLjU4MTUyMi0xNzIuMjE0MjQ3IDEyOC4xNTk4OTItMzg0LjQ4NDc5MSAxMjguMTU5ODkxLTIxMi4yNjc0NzUgMC0zODQuNDgzNzY4LTU3LjU3OTM5My0zODQuNDgzNzY4LTEyOC4xNTk4OTF2LTg1LjExMjQ3YzgyLjYwODQzOSA1OC4wNzU2OTcgMjMzLjc5NDc2NyA4NS4xMTI0NyAzODQuNDgzNzY4IDg1LjExMjQ3IDE1MC42OTEwNDcgMCAzMDEuODc4Mzk4LTI3LjAzNjc3MyAzODQuNDg0NzkxLTg1LjExMjQ3eiIgZmlsbD0iIzEyOTZkYiIgLz48L3N2Zz4=\">
                <span class=\"tracy-label\">{$connection->getQueryCount()} queries | {$this->getTime($connection, $profile)}</span>
            </span>
            
        ";

        return $html;
    }

    /**
     * Renders HTML code for custom panel.
     * @return string
     */
    function getPanel() : string
    {
        $connection = Propel::getConnection();
        $profile =  $this->getProfile($connection);

        // Queries ----------------

        $queries = [];

        foreach ($connection->getLogger()->getQueries() as $value)
        {
            $exploded = explode('|', $value['message']);

            $queries[] = [
                'time' => str_replace('Time:', '', $exploded[0]),
                'memory' => str_replace('Memory:', '', $exploded[1]),
                'memoryDelta' => str_replace('Memory Delta:', '', $exploded[2]),
                'memoryPeak' => str_replace('Memory Peak:', '', $exploded[3]),
                'sql' => $exploded[4],
                'backtrace' => $value['backtrace']
            ];
        }

        // Rows by Queries --------------

        $rows = '';

        foreach ($queries as $queryKey => $query)
        {
            $backtrace = '';

            foreach ($query['backtrace'] as $key => $bt)
            {
                $line = $class = $function = '';

                if (isset($bt['line'])) { $line = $bt['line'];}
                if (isset($bt['class'])) { $class = $bt['class'];}
                if (isset($bt['function'])) { $function = $bt['function'];}

                $backtrace .= "
                    <tr>
                        <td style='border: 0; padding: 2px 2px 0 0'>{$class}::<strong>{$function}()</strong></td>
                        <td style='border: 0; padding: 2px 2px 0 0'>&nbsp;line:&nbsp;<strong>{$line}</strong></td>
                    </tr>
                ";
            }

            $sql = trim(preg_replace('/\b([A-Z\040]{2,})\b/', '<br> <strong>\\1</strong>', trim($query['sql'])), "<br>");

            //var_dump($sql)

            $rows .= "
                <tr>
                    <td>{$query['time']}</td>
                    <td><code>{$sql}</code></td>
                    <td>{$query['memory']}</td>
                    <td>{$query['memoryDelta']}</td>
                    <td>{$query['memoryPeak']}</td>
                    <td>
                        <a href='#' data-tracy-ref='#nette-addons-wakers-propel-{$queryKey}' class='tracy-toggle tracy-collapsed'>
                            Detail
                        </a>
                        
                        <div id='nette-addons-wakers-propel-{$queryKey}' class='tracy-collapsed'>
                            <table>{$backtrace}</table>
                        </div> 
                    </td>
                </tr>
            ";
        }

        // Completed HTML ----------------

        $html = "

            <h1>Queries: {$connection->getQueryCount()} | Time: {$this->getTime($connection, $profile)} </h1>
           
            <div class=\"tracy-inner nette-ContainerPanel\">
                <div class=\"tracy-inner-container\">
	                <h2>{$profile}</h2>

                    <table>
                        <thead>
                        <tr>
                            <th>Time</th>
                            <th>SQL</th>
                            <th>Memory</th>
                            <th>Memory Delta</th>
                            <th>Memory Peak</th>
                            <th>Backtrace</th>
                        </tr>
                        </thead>
		                <tbody>
                            {$rows}
			            </tbody>
                    </table>
                </div>
            </div>
        ";

        return $html;
    }
}