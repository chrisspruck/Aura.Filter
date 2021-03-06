<?php
namespace Aura\Filter\Spec;

use Aura\Filter\Filter;
use Aura\Filter\Rule\Locator\ValidateLocator;
use Aura\Filter\Rule\Validate;

class ValidateSpecTest extends \PHPUnit_Framework_TestCase
{
    protected $spec;

    protected function setUp()
    {
        $this->spec = new ValidateSpec(new ValidateLocator(array(
            'strlen' => function () { return new Validate\Strlen; },
        )));
    }

    public function testGetField()
    {
        $this->spec->field('foo');
        $this->assertSame('foo', $this->spec->getField());
    }

    public function testFailureModes()
    {
        $this->assertSame(Filter::HARD_RULE, $this->spec->getFailureMode());

        $this->spec->asSoftRule('soft failure message');
        $this->assertSame(Filter::SOFT_RULE, $this->spec->getFailureMode());
        $this->assertSame('soft failure message', $this->spec->getMessage());

        $this->spec->asHardRule('hard failure message');
        $this->assertSame(Filter::HARD_RULE, $this->spec->getFailureMode());
        $this->assertSame('hard failure message', $this->spec->getMessage());

        $this->spec->asStopRule('stop failure message');
        $this->assertSame(Filter::STOP_RULE, $this->spec->getFailureMode());
        $this->assertSame('stop failure message', $this->spec->getMessage());
    }

    public function testIs()
    {
        $this->spec->field('foo')->is('strlen', 3);

        $subject = (object) array('foo' => 'bar');
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = 'zimgir';
        $this->assertFalse($this->spec->__invoke($subject));
    }

    public function testIsNot()
    {
        $this->spec->field('foo')->isNot('strlen', 3);

        $subject = (object) array('foo' => 'bar');
        $this->assertFalse($this->spec->__invoke($subject));

        $subject->foo = 'doom';
        $this->assertTrue($this->spec->__invoke($subject));
    }

    public function testGetMessage_is()
    {
        $this->spec->field('foo')->is('strlen', 3);
        $expect = 'foo should have validated as strlen(3)';
        $this->assertSame($expect, $this->spec->getMessage());
    }

    public function testGetMessage_isNot()
    {
        $this->spec->field('foo')->isNot('strlen', 3);
        $expect = 'foo should not have validated as strlen(3)';
        $this->assertSame($expect, $this->spec->getMessage());
    }

    public function testIsBlankOr()
    {
        $this->spec->field('foo')->isBlankOr('strlen', 3);

        $subject = (object) array();
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = null;
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = 123;
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = 'bar';
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = 'zimgir';
        $this->assertFalse($this->spec->__invoke($subject));
        $expect = 'foo should have been blank or have validated as strlen(3)';
        $actual = $this->spec->getMessage();
        $this->assertSame($expect, $actual);
    }


    public function testIsBlankOrNot()
    {
        $this->spec->field('foo')->isBlankOrNot('strlen', 3);

        $subject = (object) array();
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = null;
        $this->assertTrue($this->spec->__invoke($subject));

        $subject->foo = 123;
        $this->assertFalse($this->spec->__invoke($subject));
        $expect = 'foo should have been blank or not have validated as strlen(3)';
        $actual = $this->spec->getMessage();
        $this->assertSame($expect, $actual);

        $subject->foo = 'bar';
        $this->assertFalse($this->spec->__invoke($subject));
        $expect = 'foo should have been blank or not have validated as strlen(3)';
        $actual = $this->spec->getMessage();
        $this->assertSame($expect, $actual);

        $subject->foo = 'zimgir';
        $this->assertTrue($this->spec->__invoke($subject));
    }
}
