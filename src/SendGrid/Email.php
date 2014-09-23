<?php

namespace SendGrid;


class Email extends Api
{
    /**
     * @param Model\Email $mail
     * @return array|mixed|null|\stdClass
     */
    public function sendMail(Model\Email $mail)
    {
        return $this->callApi(
            'mail.send.json',
            $mail->toWebFormat(),
            Api::CALL_POST
        );
    }


    /**
     * Seeing as this method has to be implemented, it's an alias for array_filter
     * Alternatively, add logging here?
     * @param array $params
     * @param string $method
     * @return array
     */
    protected function sanitizeParams(array $params, $method)
    {
        return array_filter(
            $params
        );
    }
} 