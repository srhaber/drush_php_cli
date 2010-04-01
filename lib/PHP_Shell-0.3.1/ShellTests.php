<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHP/Shell.php';

class ShellTests extends PHPUnit2_Framework_TestCase {
    private $vars;

    public function setUp() {
        /* create a fresh shell object */

        $this->shell = new PHP_Shell();

        $this->vars = array();
    }

    public function tearDown() {
        foreach ($this->vars as $k => $v) {
            unset($GLOBALS[$k]);
        }
    }

    public function execute() {
        $__retval = null;

        ## get local vars
        foreach($this->vars as $__k => $__v) {
            ${$__k} = $GLOBALS[$__k];
        }
        unset($__k);
        unset($__v);
        
        if ($this->shell->parse() == 0) {
            $__retval = eval($this->shell->getCode()); 

            ## export vars to global scope
            foreach (array_diff_key(get_defined_vars(), $GLOBALS, array("__retval" => 1)) as $__k => $__v) {
                $GLOBALS[$__k] = $__v;
                $this->vars[$__k] = $__v;
            }
        }

        return $__retval;
    }

    public function testComments() {
        $tests = array(
            '## comment',
            '/* comment */',
            '// comment'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            if ($this->shell->parse() == 0) {
                eval($this->shell->getCode()); 
            }
        }
    }

    public function testUndefVars() {
        $tests = array(
            '$v',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testNewClass() {
        $tests = array(
            '$v = new ArrayObject()',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);
            $this->execute();
        }
    }

    public function testMethod() {
        $tests = array(
            '$v = new ArrayObject()',
            '$v->count()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);
            $this->execute();
        }
    }

    public function testNotExistingMethod() {
        $tests = array(
            '$v = new ArrayObject()',
            '$v->not_existing()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testNotExistingClass() {
        $tests = array(
            '$not_existing->not_existing()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testClass() {
        $tests = array(
            'class a { const a = "a"; }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            $this->execute();
        }
    }

    public function testClassExtends() {
        $tests = array(
            'class b extends a { }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            $this->execute();
        }
    }

    public function testClassExtendsNotExisting() {
        $tests = array(
            'class c extends not_existing { }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testClassDuplicate() {
        $tests = array(
            'class duplicate_class { }',
            'class duplicate_class { }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testClassDuplicateMethod() {
        $tests = array(
            'class d { function duplicate_method () { } function duplicate_method() {} }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testClassConstant() {
        $tests = array(
            'a::a',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testClassConstantNotExisting() {
        $tests = array(
            'a::not_existing',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testFunctionDuplicate() {
        $tests = array(
            'function duplicate_function() {}',
            'function duplicate_function() {}',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testFunctionDynamicNotExisting() {
        $tests = array(
            '$v = "vfunc"',
            '$v()',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testLoops() {
        $tests = array(
            '$a = array(0 => "2", 1 => "3")',
            'foreach ($a as $k => $v) { }',
            'do {} while(0)',
            'while(0) {}',
            'for ($i = 0; $i < count($a); $i++) {} '
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testIf() {
        $tests = array(
            'if (0) { } else if (1) { } else { } ',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testImplements() {
        $tests = array(
            'class e implements Serializable { function serialize() { } function unserialize($a) {} }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testInterfaceNotExists() {
        $tests = array(
            'class f implements not_existing_interface { }',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testAbstractClass() {
        $tests = array(
            'abstract class g { abstract function f(); }',
            '$f = new g()',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testStaticCall() {
        $tests = array(
            'class h { static function foo() { } }',
            'h::foo()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testStaticCallNotExists() {
        $tests = array(
            'h::not_exists()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testObjectArray() {
        $tests = array(
            'class obj_array { function params($p1, $p2) { } }',
            '$c = new ReflectionClass("obj_array")',
            '$m = $c->getMethods()',
            '$m[0]->getParameters()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testObjectArrayMethodNotExists() {
        $tests = array(
            '$c = new ReflectionClass("obj_array")',
            '$m = $c->getMethods()',
            '$m[0]->getPrototyp()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testVariableMethod() {
        $tests = array(
            '$c = new obj_array()',
            '$m = "params"',
            '$c->$m(1, 2)'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testVariableStaticMethod() {
        $tests = array(
            '$m = "params"',
            'obj_array::$m(1, 2)'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testVariableMethodNotExisting() {
        $tests = array(
            '$c = new obj_array()',
            '$m = "foo"',
            '$c->$m(1, 2)'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testInternalMethodCall() {
        $tests = array(
            'class thisclass { function a() { } function b () { $this->a(); }}',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testSingletonClass() {
        $tests = array(
            'class singleton { '.
            '  static private $inst = null; '.
            '  static function getInstance() { '.
            '    if (is_null(self::$inst)) { '.
            '       self::$inst = new singleton(); '.
            '    } '.
            '    return self::$inst; '.
            '  } '.
            '  public function get() { return 1; }'.
            '}',
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testGetInstance() {
        $tests = array(
            'singleton::getInstance()->get()'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testArrayAccessOnObject() {
        $tests = array(
            '$a = new stdClass()',
            '$a[0]'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            ## we should get a Exception
            try { $this->execute(); $this->fail(); }
            catch ( Exception $e ) { }
        }
    }

    public function testFunctionVars() {
        $tests = array(
            'function '.__FUNCTION__.'($a) { print $a; }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testMethodVars() {
        $tests = array(
            'class '.__FUNCTION__.' { function f2($a) { print $a; } }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testMethodDynamicConst() {
        $tests = array(
            'class '.__FUNCTION__.' { function f2($a) { print $a->foo; } }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }

    public function testMethodDynamicFunction() {
        $tests = array(
            'class '.__FUNCTION__.' { function f2($a) { print $a->foo(); } }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }
    public function testMethodDynamicFunctionDynamic() {
        $tests = array(
            'class '.__FUNCTION__.' { function f2($a) { print $a->$foo(); } }'
            );
        foreach ($tests as $code) {
            $this->shell->resetCode();
            $this->shell->appendCode($code);

            $this->execute();
        }
    }


}


