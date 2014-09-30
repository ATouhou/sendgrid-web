<?php

namespace SendGrid;


class Bounce extends Api
{

    const BOUNCE_TYPE_SOFT = 'soft';
    const BOUNCE_TYPE_HARD = 'hard';

    /**
     * @var array
     */
    protected static $AllParams = array(
        'getBounces' => array(
            'date'          => false,
            'days'          => false,
            'start_date'    => false,
            'end_date'      => false,
            'limit'         => false,
            'offset'        => false,
            'type'          => false,
            'email'         => false
        ),
        'deleteBounce'      => array(
            'start_date'    => false,
            'end_date'      => false,
            'type'          => false,
            'email'         => false,
            'delete_all'    => false
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
        'getBounces'    => null,
        'deleteBounce'  => null,
        'getCount'      => null
    );

    /**
     * @param array $params
     * @return array
     */
    public function getBounces(array $params = array())
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'bounces.get.'.$this->config->getOutput(),
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
            'bounces.count.'.$this->config->getOutput(),
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
     * At least one argument is required!
     * @param array $params
     * @return array|mixed|null|\stdClass
     */
    public function deleteBounce(array $params)
    {
        $params = $this->sanitizeParams(
            $params,
            __FUNCTION__
        );
        return $this->callApi(
            'bounces.delete.'.$this->config->getOutput(),
            $params,
            Api::CALL_POST
        );
    }

    /**
     * Shortcut to delete all bounces at once
     * @param array $params
     * @return array
     */
    public function deleteAllBounces(array $params = array())
    {
        if ($params) {
            $params = $this->sanitizeParams(
                $params,
                str_replace(
                    'All',
                    '',
                    substr(
                        __FUNCTION__,
                        0,-1
                    )
                )
            );
            //email + delete_all makes no sense
            if (isset($params['email'])) {
                unset($params['email']);
            }
        }
        $params['delete_all'] = 1;
        return $this->deletebounce(
            $params
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
        if (isset(static::$RequiredParams[$method])) {
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
        if (isset($sanitized['type'])) {
            if  ($sanitized['type'] !== self::BOUNCE_TYPE_HARD && $sanitized['type'] !== self::BOUNCE_TYPE_SOFT) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s is not a valid type, use %s::BOUNCE_TYPE_* constants',
                        $sanitized['type'],
                        __CLASS__
                    )
                );
            }
        }
        return $sanitized;
    }

} 
