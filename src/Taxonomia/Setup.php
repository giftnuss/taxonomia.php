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
        $folderconcept = $model->concept("folder");
        $contains = $model->concept("contains");
        $segment = $model->concept("is segment in");

        $shelf->collectFolders(function ($shelf,$folder)
            use ($model,$isa,$folderconcept,$contains,$segment)
        {
            $uri = $model->uri($shelf->makeUri($folder));
            $model->triple($uri,$isa,$folderconcept);
            if($folder['dirname']) {
                $pf = array(
                    'path' => dirname($folder['path']),
                    'type' => 'dir'
                );
                $parent = $model->uri($shelf->makeUri($pf));
                $model->triple($parent,$contains,$uri);
            }

            $model->triple(['term' => $folder['filename']],$segment,$uri);
        });
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
