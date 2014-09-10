<?php

namespace SendGrid;


class Block extends Api
{
    public function getBlocks(array $params = array())
    {
        return $this->callApi(
            'blocks.get.'.$this->config->getOutput(),
            $params
        );
    }

} 