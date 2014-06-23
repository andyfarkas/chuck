<?php

namespace DixonsCz\Chuck\Svn;

class Panel implements \Nette\Diagnostics\IBarPanel
{
    private $lastCommand;
    private static $commands = array();

    public function startCommand($command)
    {
        $this->lastCommand = array(
            'time' => microtime(true),
            'command' => $command,
        );
    }

    public function endCommand($result = '')
    {
        self::$commands[] = array(
            'time' => round((microtime(true) - $this->lastCommand['time']), 4) . " s",
            'command' => $this->lastCommand['command'],
            'result' => $result,
        );
    }

    public function getTab()
    {
        return '<span title="Panel to show information about cache and session content">
                <img src="data:image/jpeg;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnhJREFUeNqkU01PE1EUPTOdtjPT7xalVEoKBIjBGg0kgsZEo0v3fiQu3PgXXPpzWBhdqJEYiS6QqCAxJiwgCgoEUGgpbafz2Znnfa+JIUFJjDe5uS9v7jn3vPPmSYwx/E8o8vhDKpIOSbpJi0uU6T81ZnUoegSNgRNs73wRz5Mqm6PhrtL5zG6NnRm8f2VitBzTojrfySSiyCYiKHXHUMrHkIwpSOkKdvYa1emZ2d7p1x/ysoQpBUHA+y9fmxg5XerR9YFCAoOUmXiUSCKoNV1KB/Pfq1jdbqJmOLlKo/vG40VmEm5KmSy5SKjYfXDnXDJFUyzHx3a1hYXl3Q6AwJzkcOiaptPgrPBgvGjzusHBL+c3CVgRoOPC930g8EOCwPM8XmV+Gy/erR9pDisy1IiCmKYgroYRp5pO6jjbY6+TB1BcV8iTAvLi5tV+7NP0cIhAUQKpHNgB6bTWiIgTyrKM6aKxIhQ4jvNbwdhIF9p+IHxo2R4M00PDsLFT8cSe1w6QS0YxWS6AcLIgWPwWBmNS+dGbVdTqJgzLRdPy0LLasJ02PD84cqwLo3lOIAmC5a0Ir0Ozn7dhWdax5uVSKiYIzI9LR+8oAHcUCHFn/c76EEAjySqG+tIY7k2j92QcGnlTOTCx9kMZtNshImiLW6gbDcPpymjRLE0ZLmYo02IiJ6nWLVQbNmYWNvBls4a1zZq5tBEvCQWh/WfIdPU/SRingnu3714c6MtnxM9juPi4UsXXrToR2IKEh2WY7qe5xR1mNl/BfAtuRB9dS05VU2WfFa77QboQsHAyYBHpL1YckOD3aC89BWv95E0pSpX78I8vmRtm/xJgAFdmGqMQQ0DFAAAAAElFTkSuQmCC"/>
                SVN
            </span>';
    }

    public function getPanel()
    {
        $output = '<h1>SVN commands</h1><table>
            <tr><th>Time</th><th>Command</th><th>Result</th></tr>';
        foreach (self::$commands as $command) {
            $output .= "<tr>
                    <td>{$command['time']}</td>
                    <td>{$command['command']}</td>
                    <td>" . \Nette\Diagnostics\Debugger::dump($command['result'], true) . "</td>
                </tr>";
        }
        $output .= '</table>';


        return $output;
    }
}
