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
     * Tab
     * @return string
     */
    function getTab() : string
    {
        $connection = Propel::getConnection();
        $profile = $this->getProfile($connection);

        $html = "

            <span title=\"Propel ORM\">
                <img alt=\"Propel ORM\" src=\"data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTUuOTkzcHgiIGhlaWdodD0iMTUuOTkzcHgiIHZpZXdCb3g9IjcxMi41MDcgNDg4LjUwNyAxNS45OTMgMTUuOTkzIg0KCSBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDcxMi41MDcgNDg4LjUwNyAxNS45OTMgMTUuOTkzIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiMxMjk2REIiIGQ9Ik03MjYuODgsNDkxLjQ1OXYxLjA1MmMwLDEuMTU3LTIuODIsMi4xLTYuMjk5LDIuMXMtNi4yOTktMC45NDItNi4yOTktMi4xdi0xLjA1Mg0KCWMwLTEuMTU1LDIuODItMi4wOTgsNi4yOTktMi4wOThTNzI2Ljg4LDQ5MC4zMDQsNzI2Ljg4LDQ5MS40NTl6IE03MjYuODgsNDk0LjI2NXYxLjM5NmMwLDEuMTU3LTIuODIsMi4xMDEtNi4yOTksMi4xMDENCglzLTYuMjk5LTAuOTQzLTYuMjk5LTIuMTAxdi0xLjM5NmMxLjM1NCwwLjk1MywzLjgyOCwxLjM5Niw2LjI5OSwxLjM5NkM3MjMuMDUxLDQ5NS42Niw3MjUuNTI3LDQ5NS4yMTgsNzI2Ljg4LDQ5NC4yNjV6DQoJIE03MjYuODgsNDk3LjQxNnYxLjM5NWMwLDEuMTU3LTIuODIsMi4xLTYuMjk5LDIuMXMtNi4yOTktMC45NDItNi4yOTktMi4xdi0xLjM5NWMxLjM1NCwwLjk1MSwzLjgyOCwxLjM5NSw2LjI5OSwxLjM5NQ0KCUM3MjMuMDUxLDQ5OC44MTEsNzI1LjUyNyw0OTguMzY3LDcyNi44OCw0OTcuNDE2eiBNNzI2Ljg4LDUwMC41NjZ2MS4zOTNjMCwxLjE1OC0yLjgyLDIuMTAxLTYuMjk5LDIuMTAxcy02LjI5OS0wLjk0Mi02LjI5OS0yLjEwMQ0KCXYtMS4zOTNjMS4zNTQsMC45NTEsMy44MjgsMS4zOTMsNi4yOTksMS4zOTNDNzIzLjA1MSw1MDEuOTU5LDcyNS41MjcsNTAxLjUxOCw3MjYuODgsNTAwLjU2NnoiLz4NCjwvc3ZnPg0K\" />
                <span class=\"tracy-label\">{$connection->getQueryCount()} queries | {$this->getTime($connection, $profile)}</span>
            </span>
            
        ";

        return $html;
    }


    /**
     * Panel
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