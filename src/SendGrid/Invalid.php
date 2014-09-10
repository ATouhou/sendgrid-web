<?php

namespace SendGrid;


class Invalid extends Api
{
    /**
     * @var array
     */
    protected static $AllParams = array(
        'getInvalids' => array(
            'date'          => false,
            'days'          => false,
            'start_date'    => false,
            'end_date'      => false,
            'limit'         => false,
            'offset'        => false,
            'email'         => false
        ),
        'getCount'          => array(
            'start_date'    => false,
            'end_date'      => false,
        )
    );

    /**
     * @var array
     */
    protected static $RequiredParams = array(
        'getInvalids' => null,
        'getCount'  => null
    );

    /**
     * @param array $params
     * @return array
     */
    public function getInvalids(array $params = array())
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'invalidemails.get.'.$this->config->getOutput(),
            $params
        );
    }

    /**
     * @param array $param
     * @param bool $returnValue = false
     * @return \stdClass|int|null
     * @throws \InvalidArgumentException
     */
    public function getCount(array $param = array(), $returnValue = false)
    {
        $param = $this->sanitizeParams(
            $param,
            __FUNCTION__
        );
        $result = $this->callApi(
            'invalidemails.count.'.$this->config->getOutput(),
            $param
        );
        if ($returnValue === true) {
            if ($result && isset($result->count)) {
                return (int) $result->count;
            }
            return null;
        }
        return $result;
    }

    /**
     * @param string $email
     * @return array|mixed|null|\stdClass
     */
    public function deleteEmail($email)
    {
        $param = array(
            'api_user'  => $this->config->getUser(),
            'api_key'   => $this->config->getPass(),
            'email'     => $email
        );
        return $this->callApi(
            'invalidemails.delete.'.$this->config->getOutput(),
            $param,
            Api::CALL_POST
        );
    }

    /**
     * Pass an array of email addresses, each will be deleted
     * an assoc array is returned with the addresses as key,
     * the response of each delete call is the value
     * @param array $emailAddresses
     * @return array
     */
    public function deleteEmails(array $emailAddresses)
    {
        $return = array_fill_keys(
            $emailAddresses,
            null
        );
        foreach ($emailAddresses as $email) {
            $return[$email] = $this->deleteEmail($email);
        }
        return $return;
    }

    /**
     * @param array $params
     * @param string $method
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function sanitizeParams(array $params, $method)
    {
        //first, make sure required params are set
        if (static::$RequiredParams[$method]) {
            foreach  (static::$RequiredParams[$method] as $param) {
                if(!isset($params[$param])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '%s requires %s parameter',
                            $method,
                            $param
                        )
                    );
                }
            }
        }
        //then only retain those that actually exist
        //meanwhile, since we're here: set the user & pass up already
        $sanitized = array(
            'api_user'  => $this->config->getUser(),
            'api_key'   => $this->config->getPass()
        );
        $valid = static::$AllParams[$method];
        foreach ($params as $name => $v) {
            if(isset($valid[$name])) {
                if ($v instanceof \DateTime) {
                    $v = $v->format('Y-m-d');
                }
                $sanitized[$name] = $v;
            }
        }
        return $sanitized;
    }

} 