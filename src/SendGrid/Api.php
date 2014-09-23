<?php
namespace SendGrid;

abstract class Api
{
    const CALL_GET = 'GET';
    const CALL_POST = 'POST';

    const HTTP_CODE     = \CURLINFO_HTTP_CODE;
    const LAST_URL      = \CURLINFO_EFFECTIVE_URL;
    const CONTENT_TYPE  = \CURLINFO_CONTENT_TYPE;

    const API_BLOCK = 1;
    const API_BOUNCE = 2;
    const API_INVALID = 3;
    const API_SPAM = 4;

    /**
     * @var Config
     */
    protected $config = null;

    /**
     * @var array
     */
    protected $lastRequest = array(
        self::HTTP_CODE     => null,
        self::LAST_URL      => null,
        self::CONTENT_TYPE  => null
    );

    /**
     * @param Config $conf = null
     */
    public function __construct(Config $conf = null)
    {
        $this->config = $conf;
    }

    /**
     * Factory-like method, to make using this wrapper easier
     * in (my) project
     * @param int $section
     * @param Config $conf
     * @return Block|Bounce|Invalid|Spam
     * @throws \InvalidArgumentException
     */
    public static function GetApiSection($section, Config $conf)
    {
        if ($section === self::API_BLOCK) {
            return new Block($conf);
        }
        if ($section === self::API_BOUNCE) {
            return new Bounce($conf);
        }
        if ($section === self::API_INVALID) {
            return new Invalid($conf);
        }
        if ($section === self::API_SPAM) {
            return new Spam($conf);
        }
        throw new \InvalidArgumentException(
            sprintf(
                '%s is not a valid API type, use %s::API_* constants',
                $section,
                __CLASS__
            )
        );
    }

    /**
     * This method MUST be implemented, seeing as methods here assume arguments have been sanitized
     * @param array $params
     * @param string $method
     * @return array
     */
    abstract protected function sanitizeParams(array $params, $method);

    /**
     * @param null $key
     * @return array|string|int
     * @throws \InvalidArgumentException
     */
    public function checkLastRequest($key = null)
    {
        if ($key === null) {
            return $this->lastRequest;
        }
        if (!isset($this->lastRequest[$key])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'invalid argument for %s: %s, use constants',
                    __METHOD__,
                    $key
                )
            );
        }
        return $this->lastRequest[$key];
    }

    /**
     * @param $call
     * @param array $params
     * @param string $method
     * @return array|null|\stdClass|mixed
     * @throws \RuntimeException
     */
    protected function callApi($call, array $params = array(), $method = self::CALL_GET)
    {
        if (!isset($params['api_user'])) {
            $params['api_user'] = $this->config->getUser();
        }
        if (!isset($params['api_key'])) {
            $params['api_key'] = $this->config->getPass();
        }
        $options = array(
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_HEADER         => false,
            \CURLOPT_ENCODING       => '',
            \CURLOPT_SSL_VERIFYPEER => $this->config->getVerifySSL(),
            \CURLOPT_USERPWD        => sprintf(
                '%s:%s',
                $params['api_user'],
                $params['api_key']
            )
        );
        $output = $this->config->getOutput();
        if (substr($call, -1*strlen($output)) !== $output) {
            $call .= $output;
        }
        $url = $this->config->getBaseUrl().$call;
        if ($method === self::CALL_GET) {
            $url .= '?'.http_build_query(
                $params
            );
        } else {
            $options[\CURLOPT_HTTPHEADER] = array(
                'expect:',
                'user-agent: evodelavega-sendgrid/1.0;php'
            );
            $options[\CURLOPT_POST] = true;
            $options[\CURLOPT_POSTFIELDS] = $params;
        }
        $ch = $this->initCurl(
            $url,
            $options
        );
        //we've set CURL_RETURNTRANSFER, curl_exec returns the result, or false
        $response = curl_exec($ch);
        return $this->processResult(
            $response,
            $ch
        );
    }

    /**
     * @param string $url
     * @param array $options
     * @return resource
     * @throws \RuntimeException
     */
    protected function initCurl($url, array $options)
    {
        $ch = curl_init($url);
        //this should be impossible, but you never know...
        if (!is_resource($ch)) {
            throw new \RuntimeException(
                'Failed to create cUrl resource'
            );
        }
        curl_setopt_array(
            $ch,
            $options
        );
        return $ch;
    }

    /**
     * @param mixed $response
     * @param resource $ch
     * @return mixed|null|\stdClass
     * @throws \RuntimeException
     */
    protected function processResult($response, $ch)
    {
        if (!$response) {
            //false was returned, throw exception
            $errMsg = sprintf(
                'Request failed due to cUrl error: %d - %s',
                curl_errno($ch),
                curl_error($ch)
            );
            curl_close($ch);//close resource
            throw new \RuntimeException(
                $errMsg
            );
        }
        foreach ($this->lastRequest as $info => $val) {
            $this->lastRequest[$info] = curl_getinfo(
                $ch,
                $info
            );
        }
        curl_close($ch);
        return $this->responseToObject(
            $response
        );
    }

    /**
     * @param $response
     * @return \stdClass|null|mixed
     */
    protected function responseToObject($response)
    {
        if (!$response && $this->lastRequest[self::CONTENT_TYPE] === null) {
            //no response, and no valid content type to work out what was sent, return null
            return null;
        }
        if (is_array($response)) {
            //json_decode works recursively, an array doesn't
            $response = json_encode(
                json_decode(
                    $response
                )
            );
        }
        if (is_object($response)) {
            return $response;
        }
        if ($this->config->getOutput() === Config::OUTPUT_XML) {
            if (function_exists('simplexml_load_string')) {
                //simpleXML extension is installed
                return $this->parseSimpleXML(
                    simplexml_load_string($response)
                );
            }
            $dom = new \DOMDocument;
            $dom->loadXML($response);
            return $this->parseXMLDom($dom);
        }
        if ($this->config->getOutput() === Config::OUTPUT_JSON) {
            return json_decode(
                $response
            );
        }
        return $response;
    }

    /**
     * Recursive DOMDocument parse 2 stdClass
     * @param \DOMNode $node
     * @return \stdClass
     */
    protected function parseXMLDom(\DOMNode $node)
    {
        $root = $node;
        if ($node->firstChild->nodeName === 'document')
            $root = $node->firstChild;
        $result = new \stdClass();
        /** @var \DOMNode $child */
        foreach ($root->childNodes as $child)
        {
            if ($child->childNodes->length > 1)
                $result->{$child->nodeName} = $this->parseXMLDom($child);
            elseif ($child->nodeName{0} !== '#')
                $result->{$child->nodeName} = trim($child->textContent);
        }
        return $result;
    }

    /**
     * @param \SimpleXMLElement $dom
     * @param bool $toObject
     * @return array|\stdClass
     */
    protected function parseSimpleXML(\SimpleXMLElement $dom)
    {
        $array = array();
        /** @var \SimpleXMLElement $val */
        foreach ($dom as $tag => $val) {
            if ($val->children()->count())
                $array[$tag] = $this->parseSimpleXML($val);
            else
                $array[$tag] = (string) $val;
        }
        return json_decode(
            json_encode(
                $array
            )
        );
    }

    /**
     * @param Config $conf
     * @return $this
     */
    public function setConfig(Config $conf)
    {
        $this->config = $conf;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
