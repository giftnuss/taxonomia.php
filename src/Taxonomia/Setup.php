<?php

namespace Taxonomia;

use Siox\Db\Setup as DbSetup;

class Setup
{
    protected $db;
    protected $schema;

    public function __construct($db)
    {
        $this->db = $db;
        $this->schema = new Schema();
    }

    public function init($action)
    {
        $tables = $this->db->info()->listTableNames();
        if (count($tables) == 0) {
            $this->loadSchema();
            $action($this);
        }
    }

    protected function loadSchema()
    {
        $setup = new DbSetup($this->db);
        $setup->initSchema($this->schema);
        $this->schema->loadCoreData($this->db);
    }

    public function shelf($shelf)
    {
        $model = $this->getModel();
        $isa = $model->concept("is a");
        $folder = $model->concept("folder");
        foreach($shelf->listRootDirs() as $word) {
            $term = $model->term($word,1);
            $model->triple($term,$isa,$folder);
        }
    }

    public function getModel()
    {
        return new Model($this->db, $this->schema);
    }

    public function getTables()
    {
        return $this->schema->getTables();
    }

    public function getTable($name)
    {
        return $this->schema->getTable($name);
    }
}
