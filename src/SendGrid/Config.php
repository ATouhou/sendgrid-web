<?php
namespace SendGrid;

class Config
{
    const OUTPUT_JSON = 'json';
    const OUTPUT_XML = 'xml';

    /**
     * @var string
     */
    protected $baseUrl = 'https://sendgrid.com/api/';

    /**
     * @var string
     */
    protected $user = null;

    /**
     * @var string
     */
    protected $pass = null;

    /**
     * @var string
     */
    protected $output = self::OUTPUT_JSON;

    /**
     * Simple constructor, pass assoc array of settings
     */
    public function __construct(array $params = array())
    {
        foreach ($params as $name => $param) {
            $setter = 'set'.ucfirst($name);
            if (method_exists($this, $setter)) {
                $this->{$setter}($param);
            }
        }
    }

    /**
     * @param $output
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setOutput($output)
    {
        if ($output !== self::OUTPUT_JSON && $output !== self::OUTPUT_XML) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s is not a valid output type, use %s constants',
                    $output,
                    __CLASS__
                )
            );
        }
        $this->output = $output;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $pass
     * @return $this
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return $this->pass;
    }
}
