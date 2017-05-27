<?php

namespace Taxonomia;

use Siox\Db\Schema as Base;

class Schema extends Base
{
    protected function _construct()
    {
        $this->name = 'taxonomia';
        $this->withDefaultTypes();

        $this->type('id')->int->size(14);
        $this->type('word')->varchar->size(128);
        $this->type('uri')->char->size(4095);
        $this->type('tablename')->varchar->size(64);

        $this->table('concept')
            ->column->id('id')->word('concept')
            ->constraint->pk('id')->unique('concept');

        $this->table('description')
            ->column->id('id')->text('description')
            ->constraint->pk('id');

        $this->table('entity')
            ->column->id('id')->word('entity')
            ->constraint->pk('id')->unique('entity');

        $this->table('id')
            ->column->id('id')->tablename('tablename')
            ->constraint->pk('id');

        $this->table('note')
            ->column->id('id')->text('note')
            ->constraint->pk('id');

        $this->table('occurence')
            ->column->id('id')->uri('occurence')
            ->constraint->pk('id');

        $this->table('sha1')
            ->column->id('id')->digest_sha1('sha1')
            ->constraint->pk('id')->unique('sha1');

        $this->table('term')
            ->column->id('id')->word('term')
            ->constraint->pk('id');

        $this->table('triple')
            ->column->id('id')->id('s')->id('p')->id('o')
            ->constraint->pk('id');

        $this->table('uri')
            ->column->id('id')->uri('uri')
            ->constraint->pk('id')->unique('uri');
    }

    public function loadCoreData($db)
    {
        $model = new Model($db, $this);
        $db->beginTransaction();

        $category = $model->concept('category');
        $time = $model->concept('time');

        $opposite = $model->concept('is opposite of');
        $model->triple(
            $model->concept('is concept of term'),$opposite,
            $model->concept('uses concept'));

        $language = $model->concept('in language');
        $german = $model->entity('german');
        $english = $model->entity('english');

        $isa = $model->concept('is a');
        $language = $model->concept('language');

        $model->triple($language,$isa,$category);
        $model->triple($time,$isa,$category);

        $model->triple($german,$isa,$language);
        $model->triple($english,$isa,$language);

        $model->concept('document');
        $model->concept('folder');
        $model->concept('file');

        $model->triple($model->concept('contains'),$opposite,
            $model->concept('is contained in'));

        # URI-Path is build of segments
        $model->triple(
            $model->concept('is segment in'),$opposite,
            $model->concept('has segment'));

        # Predicate Logic?
        $every = $model->concept("every");
        $any = $model->concept("any");
        $none = $model->concept("none");

        $model->triple($isa,$every,$language);
        $model->concept('sha1 digest');
        $model->concept('title');
        $model->concept('author');

        $db->commit();
    }
}
