<?php

interface Liip_Shared_Model_Connection
{
    /**
     * @param string
     */
    public function post($query);

    public function download($local = null, $remote = null, $varDirName = 'tmp', $request = null, $contentType = false);
}
