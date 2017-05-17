<?php

namespace Taxonomia;

class Model
{
    protected $db;
    protected $sql;
    protected $schema;
    public $orm;

    protected $unique = array('concept','uri');
    protected $ambiguous = array('description','note','term');

    public function __construct($db, $schema)
    {
        $this->db = $db;
        $this->sql = $db->sql();
        $this->schema = $schema;
        $this->orm = $db->orm($schema);
    }

    protected function isUniqueIndex($name)
    {
        return in_array($name,$this->unique);
    }

    protected function isAmbiguousIndex($name)
    {
        return in_array($name,$this->ambiguous);
    }

    public function __call($name,$args)
    {
        if(stristr($name,'get') === $name) {
            $name = strtolower(substr($name,3));
            return $this->get($name,$args);
        }

        $ct = $this->orm->table($name);
        $qr = $this->orm->query($ct);
        $id = null;
        $it = $this->orm->table('id');

        $word = array_shift($args);
        $isunique = isset($args[0]) && $args[0];

        if($this->isUniqueIndex($name) || $isunique) {
            $qr->if_not(array($name => $word),function ($sql,$table)
                use ($it,$ct,$word,&$id,$name) {
                $sql->insert($it,array('id' => null,'tablename' => $ct->getName()));
                $id = $sql->lastInsertId('id');
                $sql->insert($ct,array('id' => $id,$name => $word));
            }, function ($row) use (&$id) {
                $id = $row['id'];
            });
            return $id;
        }
        if($this->isAmbiguousIndex($name)) {
            $this->sql->insert($it,array('id' => null, 'tablename' => $ct->getName()));
            $id = $this->sql->lastInsertId('id');
            $this->sql->insert($ct,array('id' => $id,$name => $word));
            return $id;
        }

        throw new \Exception("Unknown method $name");
    }

    public function get($name,$args)
    {
        $id = array_shift($args);
        $t = $this->orm->table($name);
        $qr = $this->orm->query($t);
        $result = $qr->pick(['id' => $id]);
        return $result[$name];
    }

    public function getTriple($id)
    {
        $t = $this->orm->table('triple');
        $qr = $this->orm->query($t);
        $triple = $qr->pick(['id' => $id]);
        $result = ['id' => $triple['id']];
        foreach(['s','p','o'] as $c) {
            $result[$c] = $this->record($triple[$c]);
        }

        return $result;
    }

    public function record($id)
    {
        $i = $this->orm->table('id');
        $qr = $this->orm->query($i);
        $row = $qr->pick(['id' => $id]);
        $t = $this->orm->table($row['tablename']);
        $record = $this->orm->query($t)->pick(['id' => $id]);
        // TODO - can not use now column names: type, value
        $record['type'] = $row['tablename'];
        $record['value'] = $record[$row['tablename']];
        return $record;
    }

    public function triple($s,$p,$o)
    {
        $tr = $this->orm->table('triple');
        $it = $this->orm->table('id');
        $qr = $this->orm->query($tr);
        $id = null;
        $args = array('s' => $s,'p' => $p,'o' => $o);
        $lookup = array('count' => 0);
        foreach($args as $k => $v) {
            if(is_array($v)) {
                $keys = array_keys($v);
                $key = array_shift($keys);
                $val = $v[$key];
                if($this->isUniqueIndex($key)) {
                    $args[$k] = $this->$key($val);
                }
                else {
                    $lookup['count']++;
                    $lookup['key'] = $key;
                    $lookup['val'] = $val;
                    $lookup['what'] = $k;
                }
            }
        }

        if($lookup['count'] === 0) {
           $qr->if_not($args,
               function ($sql,$table) use ($tr,$it,&$id,$s,$p,$o) {
                   $sql->insert($it,array('id' => null, 'tablename' => $tr->getName()));
                   $id = $sql->lastInsertId('id');
                   $sql->insert($tr,array('id' => $id,'s' => $s,'p' => $p,'o' => $o));
           }, function ($row) use (&$id) {
                   $id = $row['id'];
           });
           return $id;
        }
        elseif($lookup['count'] === 1) {
           $what = $lookup['what'];
           $searchargs = array();
           if($what !== 's') $searchargs['s'] = $args['s'];
           if($what !== 'p') $searchargs['p'] = $args['p'];
           if($what !== 'o') $searchargs['o'] = $args['o'];
           $qr->if_not($searchargs,
              function ($sql,$table) use ($lookup,$it,$tr,&$id,$searchargs) {
                 $searchargs[$lookup['what']] = $this->{$lookup['key']}($lookup['val']);
                 $sql->insert($it,array('id' => null, 'tablename' => $tr->getName()));
                 $id = $sql->lastInsertId('id');

                 $sql->insert($tr,array('id' => $id,
                     's' => $searchargs['s'],
                     'p' => $searchargs['p'],
                     'o' => $searchargs['o']));
           }, function ($row) use ($lookup) {
                 $lookupid = $row[$lookup['what']];
                 throw new \Exception("TODO - known lookup");
           });
           return $id;
        }
    }

    protected function prepareTripleArgs(&$args)
    {
        foreach($args as $k => $v) {
            if(is_array($v)) {
                $keys = array_keys($v);
                $key = array_shift($keys);
                $val = $v[$key];
                $query = $this->orm->query($key);
                $id = [];
                $query->search([$key => $val], function ($row) use (&$id) {
                    $id[] = $row['id'];
                });
                if(count($id) === 0) {
                    throw new \Exception("Argument $key = $val not found.");
                }
                elseif(count($id) === 1) {
                    $args[$k] = $id[0];
                }
                else {
                    $args[$k] = $id;
                }
            }
        }
    }

    public function searchTriples($args,callable $action)
    {
        $this->prepareTripleArgs($args);
        $this->orm->query('triple')->search($args,$action);
    }

    public function countTriples($args)
    {
        $this->prepareTripleArgs($args);
        return $this->orm->query('triple')->count($args);
    }
}
