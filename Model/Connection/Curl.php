<?php

class Liip_Shared_Model_Connection_Curl implements Liip_Shared_Model_Connection
{
    protected $url;

    protected $filename = null;

    protected $proxy;

    protected $proxyUserPwd;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function setProxyUserPwd($proxyUserPwd)
    {
        $this->proxyUserPwd = $proxyUserPwd;
    }

    protected function proxify($curl)
    {
        if ($this->proxy) {
            curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
            if ($this->proxyUserPwd) {
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxyUserPwd);
            }
        }
    }

    /**
     * GET request
     * 
     * @param string|array  $query        The url encoded string containing the params or an array with key=>value association
     */
    public function get($query = '')
    {
        if (is_array($query)) {
            $query = http_build_query($query);
        }
        $url = $this->url . '?' . $query;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // never cache
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

        $this->proxify($curl);
    
        // Execute, saving results in a variable
        $result = curl_exec($curl);
        curl_close($curl);
    
        return $result;
    }

    /**
     * @param string|array  $query        The url encoded string containing the params or an array with key=>value association
     * @param string        $contentType    The content type to set or FALSE for none, i.e., let cURL decide:
     *                                      if $query is a string = application/x-www-form-urlencoded
     *                                      if $query is an array = multipart/form-data
     */
    public function post($query, $contentType = 'text/xml; charset=UTF-8')
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        if ($contentType) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: $contentType"));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // never cache
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);

        $this->proxify($curl);

        // Execute, saving results in a variable
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * @param curl $ch
     * @param string $header
     * @return number
     */
    public function header($ch, $header)
    {
        $parts = explode(':', $header);

        if (count($parts) == 2) {
            if ($parts[0] == 'Content-Disposition') {

                $contentDisposition = explode('; ', $parts[1]);

                // attachment; filename="cat.jpg"
                if (!empty($contentDisposition[1]) && strpos($contentDisposition[1], 'filename=') !== false) {
                    $this->filename = trim(substr(trim($contentDisposition[1]), 9), ' "');
                }
                // filename="cat.jpg"
                if (!empty($contentDisposition[0]) && strpos($contentDisposition[0], 'filename=') !== false) {
                    $this->filename = trim(substr(trim($contentDisposition[0]), 9), ' "');
                }
            }
        }

        return strlen($header);
    }

    /**
     * @param string $local         Local file name or NULL to auto-generate (first tries Content-Disposition and then uses basename of the url)
     * @param string $remote        Remote file name (optional, will be appended to url)
     * @param string $varDirName    In case of auto-generated local name, the subdirectory to put it in
     * @param string $query         The url encoded string containing the params or an array with key=>value association (can be null)
     * @param string $contentType   The content type to set or FALSE for none, i.e., let cURL decide:
     *                              if $query is a string = application/x-www-form-urlencoded
     *                              if $query is an array = multipart/form-data
     * @return string|FALSE  The file name where it was saved or FALSE on failure
     * @throw Exception On access failure of local file
     */
    public function download($local = null, $remote = null, $varDirName = 'tmp', $query = null, $contentType = false)
    {
        $url = $this->url;

        // reset filename
        $this->filename = null;

        if ($remote != null) {
            $url .= $remote;
        }

        if (null == $local) {
            if ($var = Mage::getConfig()->getVarDir($varDirName)) {
                $download = tempnam($var, 'liip_download');
            } else {
                throw new Exception('Could not get var dir for file download. Verify dir is writable: var/'.$varDirName);
            }
        } else {
            $download = $local;
        }

        $ch = curl_init($url);
        $fp = fopen($download, 'w');
        if (!$fp) {
            throw new Exception('Cannot open file for writing: '.$download);
        }

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'header'));
        if ($query != null) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        }
        if ($contentType) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: $contentType"));
        }

        $this->proxify($curl);

        $result = curl_exec($ch);

        curl_close($ch);
        fclose($fp);

        if (null == $local) {
            if ($this->filename != null) {
                $local = $var . DS . $this->filename;
            } else {
                $local = $var . DS . basename($url);
            }
            rename($download, $local);
        }

        return $result === false ? false : $local;
    }
}

