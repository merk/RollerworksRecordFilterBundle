<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\RecordFilterBundle\Tests\Input;

use Rollerworks\RecordFilterBundle\Input\ArrayInput;
use Rollerworks\RecordFilterBundle\Value\FilterValuesBag;
use Rollerworks\RecordFilterBundle\Value\SingleValue;
use Rollerworks\RecordFilterBundle\Tests\TestCase;

class ArrayTest extends TestCase
{
    public function testSingleField()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');

        $input->setInput(array('user' => '2'));
        $this->assertEquals(array(array('user' => new FilterValuesBag('user', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0))), $input->getGroups());
    }

    public function testSingleFieldWithUnicode()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('foo', 'ß');
        $input->setLabelToField('foo', 'ß');

        $input->setInput(array('ß' => '2'));
        $this->assertEquals(array(array('foo' => new FilterValuesBag('ß', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0))), $input->getGroups());
    }

    public function testMultipleFields()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');
        $input->setField('status');

        $input->setInput(array('User' => '2', 'Status' => 'Active'));
        $this->assertEquals(array(array(
            'user' => new FilterValuesBag('user', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0),
            'status' => new FilterValuesBag('status', 'Active', array(new SingleValue('Active')), array(), array(), array(), array(), 0)
        )), $input->getGroups());
    }

    // Field-name appears more then once
    public function testDoubleFields()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');
        $input->setField('status');

        $input->setLabelToField('status', 'status2');

        $input->setInput(array('User' => '2', 'Status' => 'Active', 'Status2' => 'NoneActive', 'user' => '3'));
        $this->assertEquals(array(array(
            'user' => new FilterValuesBag('user', '2,3', array(new SingleValue('2'), new SingleValue('3')), array(), array(), array(), array(), 1),
            'status' => new FilterValuesBag('status', 'Active,NoneActive', array(new SingleValue('Active'), new SingleValue('NoneActive')), array(), array(), array(), array(), 1),
        )), $input->getGroups());
    }

    // Test the escaping of the filter-delimiter
    public function testEscapedFilter()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');
        $input->setField('status');
        $input->setField('date');

        $input->setInput(array('User' => '2', 'Status' => '"Active;None"', 'date' => '"29-10-2010"'));

        $this->assertEquals(array(array(
            'user' => new FilterValuesBag('user', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0),
            'status' => new FilterValuesBag('status', '"Active;None"', array(new SingleValue('Active;None')), array(), array(), array(), array(), 0),
            'date' => new FilterValuesBag('date', '"29-10-2010"', array(new SingleValue('29-10-2010')), array(), array(), array(), array(), 0),
        )), $input->getGroups());
    }

    public function testOrGroup()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');
        $input->setField('status');
        $input->setField('date');

        $input->setInput(array(
            array('User' => '2', 'Status' => '"Active;None"', 'date' => '"29-10-2010"'),
            array('User' => '3', 'Status' => 'Concept', 'date' => '"30-10-2010"')
        ));

        $this->assertEquals(array(
            array(
                'user' => new FilterValuesBag('user', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0),
                'status' => new FilterValuesBag('status', '"Active;None"', array(new SingleValue('Active;None')), array(), array(), array(), array(), 0),
                'date' => new FilterValuesBag('date', '"29-10-2010"', array(new SingleValue('29-10-2010')), array(), array(), array(), array(), 0),
            ),
            array(
                'user' => new FilterValuesBag('user', '3', array(new SingleValue('3')), array(), array(), array(), array(), 0),
                'status' => new FilterValuesBag('status', 'Concept', array(new SingleValue('Concept')), array(), array(), array(), array(), 0),
                'date' => new FilterValuesBag('date', '"30-10-2010"', array(new SingleValue('30-10-2010')), array(), array(), array(), array(), 0),
            ),
        ), $input->getGroups());
    }

    public function testOrGroupValueWithBars()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('user');
        $input->setField('status');
        $input->setField('date');

        $input->setInput(array(
            array('User' => '2', 'Status' => '"(Active;None)"', 'date' => '"29-10-2010"'),
            array('User' => '3', 'Status' => 'Concept', 'date' => '"30-10-2010"')
        ));

        $this->assertEquals(array(
            array(
                'user' => new FilterValuesBag('user', '2', array(new SingleValue('2')), array(), array(), array(), array(), 0),
                'status' => new FilterValuesBag('status', '"(Active;None)"', array(new SingleValue('(Active;None)')), array(), array(), array(), array(), 0),
                'date' => new FilterValuesBag('date', '"29-10-2010"', array(new SingleValue('29-10-2010')), array(), array(), array(), array(), 0),
            ),
            array(
                'user' => new FilterValuesBag('user', '3', array(new SingleValue('3')), array(), array(), array(), array(), 0),
                'status' => new FilterValuesBag('status', 'Concept', array(new SingleValue('Concept')), array(), array(), array(), array(), 0),
                'date' => new FilterValuesBag('date', '"30-10-2010"', array(new SingleValue('30-10-2010')), array(), array(), array(), array(), 0),
            ),
        ), $input->getGroups());
    }

    public function testValidationNoRange()
    {
        $input = new ArrayInput($this->translator);
        $input->setField('User', null, null, true);
        $input->setField('status');
        $input->setField('date');

        $input->setInput(array(
            array('User' => '2-5', 'Status' => '"Active"', 'date' => '29.10.2010'),
        ));

        $this->assertFalse($input->getGroups());
        $this->assertEquals(array("Field 'user' does not accept ranges in group 1."), $input->getMessages());
    }

    public function testValidationNoCompare()
    {
        $input = new ArrayInput($this->translator);
        $input->setInput(array(
            array('User' => '2,3,10-20', 'Status' => '"Active"', 'date' => '25.05.2010,>25.5.2010'),
        ));

        $input->setField('user', null, null, true, true);
        $input->setField('status', null, null, true, true);
        $input->setField('date', null, null, true, true);

        $this->assertFalse($input->getGroups());
        $this->assertEquals(array("Field 'date' does not accept comparisons in group 1."), $input->getMessages());
    }
}
