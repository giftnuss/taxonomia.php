<?php

namespace Taxonomia;

class Model
{
    protected $db;
    protected $sql;
    protected $schema;
    public $orm;

    public function __construct($db, $schema)
    {
        $this->db = $db;
        $this->sql = $db->sql();
        $this->schema = $schema;
        $this->orm = $db->orm($schema);
    }

    public function __call($name,$args)
    {
        if(stristr($name,'get') === $name) {
            $name = strtolower(substr($name,3));
            return $this->get($name,$args);
        }

        $unique = array('concept','uri');

        $ct = $this->orm->table($name);
        $qr = $this->orm->query($ct);
        $id = null;
        $it = $this->orm->table('id');

        $word = array_shift($args);
        $isunique = isset($args[0]) && $args[0];

        if(in_array($name,$unique) || $isunique) {
            $qr->if_not(array($name => $word),function ($sql,$table)
                use ($it,$ct,$word,&$id,$name) {
                $sql->insert($it,array('id' => null));
                $id = $sql->lastInsertId('id');
                $sql->insert($ct,array('id' => $id,$name => $word));
            }, function ($row) use (&$id) {
                $id = $row['id'];
            });
            return $id;
        }

        throw new \Exception("Unknown method $name");
    }

    public function get($name,$args)
    {
        $id = array_shift($args);
        $t = $this->orm->table($name);
        $qr = $this->orm->query($t);
        $result = '';
        $qr->search(['id' => $id],function ($row) use ($name,&$result) {
            $result = $row[$name];
        });
        return $result;
    }

    public function term2($word)
    {
        $ct = $this->orm->table('term');
        $qr = $this->orm->query($ct);
        $id = null;
        $it = $this->orm->table('id');
        $qr->if_not(array('term' => $word),function ($sql,$table)
            use ($it,$ct,$word,&$id) {
            $sql->insert($it,array('id' => null));
            $id = $sql->lastInsertId('id');
            $sql->insert($ct,array('id' => $id,'concept' => $word));
        }, function ($row) use (&$id) {
            $id = $row['id'];
        });
        return $id;
    }

    public function triple(int $s,int $p,int $o)
    {
        $tr = $this->orm->table('triple');
        $it = $this->orm->table('id');
        $qr = $this->orm->query($tr);
        $id = null;
        $qr->if_not(array('s' => $s,'p' => $p,'o' => $o),
           function ($sql,$table) use ($tr,$it,&$id,$s,$p,$o) {
              $sql->insert($it,array('id' => null));
              $id = $sql->lastInsertId('id');
              $sql->insert($tr,array('id' => $id,'s' => $s,'p' => $p,'o' => $o));
        }, function ($row) use (&$id) {
            $id = $row['id'];
        });
        return $id;
    }
}
