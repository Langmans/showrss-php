<?php

namespace rubenvincenten\ShowRss;

class URL
{
    /**
     * @param $url
     * @param array $options
     * @return array|mixed|\SimpleXMLElement|string
     */
    public function cache($url, array $options = array())
    {
        $options += array(
            'ttl' => 3600
        );

        asort($options);
        $file = __DIR__ . '/cache/' . md5($url . serialize($options)) . '.url_cache.txt';
        if (!is_file($file) || filemtime($file) + $options['ttl'] < time()) {
            $data = $this->request($url, $options);
            file_put_contents($file, serialize($data));
            return $data;
        }
        return unserialize(file_get_contents($file));
    }

    /**
     * @param $url
     * @param array $options
     * @return array|mixed|\SimpleXMLElement|string
     */
    public function request($url, array $options = array())
    {
        $options += array(
            'force_content_type' > false,
            'auto_decode' => false,
            'verbose' => false,
//        'method' => 'GET',
            'raw' => false,
            'json_decode_assoc' => true
        );
        asort($options);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($options['verbose']) {
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
        }
        if (!empty($options['method'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);
        }
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($options['verbose'] == 'capture') {
            ob_start();
        }
        $response = curl_exec($ch);
        if ($options['verbose'] == 'capture') {
            $verbose = ob_get_clean();
        }
        $info = curl_getinfo($ch);
        $header_size = $info['header_size'];
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        if ($options['raw'] === true) {
            $return = $response;
        } elseif ($options['raw'] == 'array') {
            $return = compact('info', 'header', 'body', 'return', 'verbose');
        } elseif ($options['auto_decode'] === true) {
            switch ($options['force_content_type'] ?: $info['content_type']) {
                case 'text/xml':
                case 'application/xml':
                    $return = new \SimpleXMLElement($body);
                    break;
                case 'application/json':
                    $return = json_decode($body, $options['json_decode_array']);
                    break;
                default:
                    $return = $body;
                    break;
            }
        } elseif (is_callable($options['auto_decode'])) {
            $return = call_user_func($options['auto_decode'], $body);
        } else {
            $return = $body;
        }

        return $return;
    }
}