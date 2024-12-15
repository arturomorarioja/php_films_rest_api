<?php

class Utils
{
    /**
     *  It debugs the received information to an HTML file.
     *  The HTML file will be named log/log_yyMMdd.htm
     */
    static public function debug(array|string $info): void 
    {
        $fileName = 'log/log_' . date('Ymd') . '.htm';
        
        $text = '';
        if (!file_exists($fileName)) {
            $text .= '<pre>';
        }
        $text .= '<p>--- ' . date('Y-m-d h:i:s A', time()) . ' ---</p>';
        
        $logFile = fopen($fileName, 'a');
        
        if (gettype($info) === 'array') {
            $text .= print_r($info, true);
        } else {
            $text .= $info;
        }
        fwrite($logFile, $text);
        
        fclose($logFile);
    }
}