<?php

namespace SendGrid;


class Spam extends Api
{
    /**
     * @var array
     */
    protected static $AllParams = array(
        'getSpamReports' => array(
            'date'          => false,
            'days'          => false,
            'start_date'    => false,
            'end_date'      => false,
            'limit'         => false,
            'offset'        => false,
            'email'         => false
        ),
        'deleteSpamReports' => array(
            'start_date'    => false,
            'end_date'      => false,
            'email'         => false,
            'delete_all'    => false
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
        'getSpamReports'    => null,
        'deleteSpamReports' => null,
        'getCount'          => null
    );

    /**
     * @param array $params
     * @return array
     */
    public function getSpamReports(array $params = array())
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'spamreports.get.'.$this->config->getOutput(),
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
            'spamreports.count.'.$this->config->getOutput(),
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
    public function deleteSpamReports(array $params)
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'spamreports.delete.'.$this->config->getOutput(),
            $params,
            Api::CALL_POST
        );
    }

    /**
     * Shortcut to deleteSpamReport(['delete_all' => 1]);
     * @param array $params
     * @return array
     */
    public function deleteAllSpamReports(array $params = array())
    {
        if ($params) {
            $params = $this->sanitizeParams(
                $params,
                str_replace(
                    'All',
                    '',
                    __FUNCTION__
                )
            );
            //you cann't expect to pass a single email address, and call delete_all
            if (isset($params['email'])) {
                unset($params['email']);
            }
        }
        $params['delete_all'] = 1;
        return $this->deleteSpamReports(
            array(
                'delete_all'    => 1
            )
        );
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