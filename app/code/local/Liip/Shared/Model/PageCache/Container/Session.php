<?php

class Liip_Shared_Model_PageCache_Container_Session extends Liip_Shared_Model_Container_Abstract_Refresh
{
    /**
     * @return string
     */
    protected function _getName()
    {
        return 'LIIP_SESSION';
    }
}

