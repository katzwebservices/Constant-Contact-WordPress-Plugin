<?php

class ExceptionalRemote {
    /*
     * Does the actual sending of an exception
     */
    static function send_exception($exception) {
        $uniqueness_hash = $exception->uniqueness_hash();
        $hash_param = ($uniqueness_hash) ? null : "&hash={$uniqueness_hash}";
        $url = "/api/errors?api_key=".Exceptional::$api_key."&protocol_version=".Exceptional::$protocol_version.$hash_param;
        $compressed = gzencode($exception->to_json(), 1);
        self::call_remote($url, $compressed);
    }

    /*
     * Sends a POST request
     */
    static function call_remote($path, $post_data) {
        $default_port = Exceptional::$use_ssl ? 443 : 80;

        $host = Exceptional::$proxy_host ? Exceptional::$proxy_host : Exceptional::$host;
        $port = Exceptional::$proxy_port ? Exceptional::$proxy_port : $default_port;

        if (Exceptional::$use_ssl === true) {
            $s = fsockopen("ssl://".$host, $port, $errno, $errstr, 4);
            $protocol = "https";
        }
        else {
            $s = fsockopen($host, $port, $errno, $errstr, 2);
            $protocol = "http";
        }

        if (!$s) {
            echo "[Error $errno] $errstr\n";
            return false;
        }

        $url = "$protocol://".Exceptional::$host."$path";

        $request  = "POST $url HTTP/1.1\r\n";
        $request .= "Host: ".Exceptional::$host."\r\n";
        $request .= "Accept: */*\r\n";
        $request .= "User-Agent: ".Exceptional::$client_name." ".Exceptional::$version."\r\n";
        $request .= "Content-Type: text/json\r\n";
        $request .= "Connection: close\r\n";
        $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n";
        $request .= "$post_data\r\n";

        fwrite($s, $request);

        $response = "";
        while (!feof($s)) {
            $response .= fgets($s);
        }

        fclose($s);
    }

}
