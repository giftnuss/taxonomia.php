<?php
/*
 * @author Sebastian Knapp
 * @version 0.1
 */

class ScriptTests extends \PHPUnit_Framework_TestCase
{
    protected $tests;

    protected function setUp()
    {
        $this->tests = array(
            '00-basics' => array(
                '01_load.t',
                '02_shelf.t',
                '03-model.t',
                '04-setup.t'
            ),
            '01-app' => array(
                '02_shelf.t'
            )
        );
    }

    public function testScripts()
    {
        $base = dirname(__DIR__);
        foreach($this->tests as $dir => $tests) {
            foreach($tests as $script) {
                ob_start();
                include "$base/$dir/$script";
                $out = ob_get_contents();
                ob_end_clean();
                $this->assertNotRegExp('/not ok/',$out,"$dir/$script");
            }
        }
    }
}
