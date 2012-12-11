<?php

class Liip_Shared_Test_Abstract extends PHPUnit_Framework_TestCase
{
    protected function debug($html = null)
    {
        if (!$html) {
            $html = $this->session->getPage()->getContent();
        }

        $file = tempnam(sys_get_temp_dir(), 'magento') . '.html';
        file_put_contents($file, $html);

        system('open ' . $file);
    }
}
