<?php

namespace Siox\Blob;

class Dba extends AbstractStorage implements Storage
{
    public function connect()
    {
        if($this->adapter === null) {
            $this->defaultHandler();
        }
        elseif(is_array($this->adapter)) {
            $adapter = call_user_func_array('dba_open',$this->adapter);
            if($adapter === false) {
                throw new Storage\Exception("Can not open dba handle from data.");
            }
            $this->storage = $adapter;
        }
        elseif(!is_resource($this->adapter)) {
                throw new Storage\Exception("Dba storage can not work without valid resource handle.");
        }
    }

    public function defaultHandler()
    {
        $handlers = dba_handlers();
        $first = array_shift($handlers);

        $file = tempnam ( null , 'php_dba' );
        $adapter = dba_open($file,'wl', 'db4');
        if($adapter === false) {
            throw new Storage\Exception("Can not open dba default handler.");
        }
        $this->adapter = $adapter;
    }
}
