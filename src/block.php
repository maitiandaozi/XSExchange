<?php
class XSEXchange {

    protected $xs    = null;
    protected $key   = null;
    protected $id    = 885;
    protected $perms = 0644;
    protected $limit = 3;
    protected $sign  = "XSEX";

    public function __construct($id = null, $key = null)
    {
        if($id)
            $this->id = $id;

        if($key)
            $this->key = $key;
    }

    public function exists($id)
    {
        $status = @shmop_open($id, "a", 0, 0);

        return $status;
    }

    public function setLimit($num = 10) 
    {
        $this->limit = $num;
    }

    public function addProcess($weight = 3)
    {
        $data = $this->_getContent($weight);

        if($data == -1) {
            return false;
        }

        $data = $this->_add($data);

        if($data === -1) {
            return false;
        }

        $this->_writeConent($data);

        return true;
    }

    public function removeProcess($num = 1) 
    {
        $data = $this->_getContent($this->limit);
        if($data == -1) {
            return false;
        }

        $data = $this->_remove($data);

        if($data == -1) {
            return false;
        }

        $this->_writeConent($data);

        return true;
    }

    public function readProcess() 
    {
        $data = $this->_getContent($this->limit);
        return $data;
    }

    protected function _add($data) 
    {
        $num = $this->_check($data);

        if($num === -1) {
            return -1;
        }

        if($num == 0) $data = "";

        $num++;

        for($i = 0; $i < $this->limit; $i++) {
            $rand = mt_rand(1, 65535);
            $data .= $num . ":" . $rand . ";";
        }

        return $data;
    }

    protected function _remove($data) 
    {
        if(strlen($data) == 0) {
            return -1;
        }

        $result = explode(";", $data);

        if((count($result) - 1) <= 0)
            return "";

        $same = null;
        $sum  = count($result);
        for($i = 0; $i < $sum; $i++) {

            $v = explode(":", $result[$i]);
            if(!$same) {
                $same = $v[0];
            }

            if($same == $v[0]) {
                unset($result[$i]);
            }

            if($same != $v[0]) break;
        }

        if(empty($result)) return "";

        return implode(";", $result);
    }

    protected function _check($data) 
    {
        if(strlen($data) + 15* $this->limit > 1024) {
            return -1;
        }

        if(strlen($data) == 0) return 0;

        $result = explode(";", $data);

        if(count($result) <= 1) return -1;

        $v = explode(":", $result[count($result) - 2]);
        return $v[0];
    }

    protected function _writeConent($data) 
    {
        if($this->exists($this->id)) {

            shmop_delete($this->xs);
            shmop_close($this->xs);
        }

        if(strlen($data) > 0) {

            $data = "XSEX" . $this->key . "-" . $data;
            $size = strlen($data);
            $this->shmid = shmop_open($this->id, "c", $this->perms, $size);
            shmop_write($this->shmid, $data, 0);
        }
    }

    protected function _getContent($weight)
    {
        if($weight != $this->limit)
            $this->setLimit($weight);

        if($this->exists($this->id)) {

            $this->xs  = shmop_open($this->id, "a", 0, 0);
            $size      = @shmop_size($this->xs);
            $data = "";

            if($size && $size > 0)  {

                $data = shmop_read($this->xs, 0, $size);
                $data = $this->isvilidate($data);
            }

        } else {

            $data = "";
        }

        return $data;
    }

    protected function isvilidate($data) 
    {
        $result = explode("-", $data);

        if(count($result) == 2 && $result[0] == "XSEX" . $this->key) 
            return $result[1];

        return -1;
    }

    public function __destruct()
    {
        @shmop_close($this->shmid);
    }
}