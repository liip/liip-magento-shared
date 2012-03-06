<?php

class Liip_Shared_Model_Connection_Mock implements Liip_Shared_Model_Connection
{
    protected $response;

    public $last;

    public $count = 0;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function post($request) {

        $this->last = $request;
        $this->count++;

        return $this->response;
    }

    public function download($local = null, $remote = null, $varDirName = 'tmp', $request = null, $contentType = false) {

        return $this->response;
    }
}

