<?php

namespace SendGrid;


class Block extends Api
{

    const BLOCK_TYPE_SOFT = 'soft';
    const BLOCK_TYPE_HARD = 'hard';

    /**
     * @var array
     */
    protected static $AllParams = array(
        'getBlocks' => array(
            'date'          => false,
            'days'          => false,
            'start_date'    => false,
            'end_date'      => false,
            'limit'         => false,
            'offset'        => false
        ),
        'getCount'          => array(
            'start_date'    => false,
            'end_date'      => false,
            'type'          => false
        )
    );

    /**
     * @var array
     */
    protected static $RequiredParams = array(
        'getBlocks' => null,
        'getCount'  => null
    );

    /**
     * @param array $params
     * @return array
     */
    public function getBlocks(array $params = array())
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'blocks.get.'.$this->config->getOutput(),
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
        if (isset($param['type'])) {
            if (!$param['type'] !== self::BLOCK_TYPE_HARD && $param['type'] !== self::BLOCK_TYPE_SOFT) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s is an invalid block type, use %s::BLOCK_TYPE_* constants',
                        $param['type'],
                        __CLASS__
                    )
                );
            }
        }
        $result = $this->callApi(
            'blocks.count.'.$this->config->getOutput(),
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
            'blocks.delete.'.$this->config->getOutput(),
            $param
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