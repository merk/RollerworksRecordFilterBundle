<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Tests\Modifier;

use Rollerworks\Bundle\RecordFilterBundle\Value\FilterValuesBag;
use Rollerworks\Bundle\RecordFilterBundle\Type\Number;
use Rollerworks\Bundle\RecordFilterBundle\Input\FilterQuery as QueryInput;
use Rollerworks\Bundle\RecordFilterBundle\Value\Range;
use Rollerworks\Bundle\RecordFilterBundle\Value\SingleValue;
use Rollerworks\Bundle\RecordFilterBundle\FilterField;

class ValuesToRangeTest extends ModifierTestCase
{
    public function testOptimizeValue()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=1,2,3,4,5,6,7');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '1,2,3,4,5,6,7', array(), array(), array(new Range('1', '7')));

        $this->assertEquals($expectedValues, $filters[0]);
    }

    public function testOptimizeValueUnordered()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=3,6,7,1,2,4,5');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '3,6,7,1,2,4,5', array(), array(), array(new Range('1', '7')));

        $this->assertEquals($expectedValues, $filters[0]);
    }

    public function testOptimizeValueMultipleRanges()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=1,2,3,4,5,6,7,10,11,12,13,14,15,18');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '1,2,3,4,5,6,7,10,11,12,13,14,15,18', array(13 => new SingleValue('18')), array(), array(new Range('1', '7'), new Range('10', '15')));

        $this->assertEquals($expectedValues, $filters[0]);
    }

    public function testOptimizeExcludes()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=!1,!2,!3,!4,!5,!6,!7');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '!1,!2,!3,!4,!5,!6,!7', array(), array(), array(), array(), array(new Range('1', '7')));

        $this->assertEquals($expectedValues, $filters[0]);
    }

    public function testOptimizeExcludesUnordered()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=!3,!6,!7,!1,!2,!4,!5');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '!3,!6,!7,!1,!2,!4,!5', array(), array(), array(), array(), array(new Range('1', '7')));

        $this->assertEquals($expectedValues, $filters[0]);
    }

    public function testOptimizeExcludesMultipleRanges()
    {
        $input = new QueryInput($this->translator);
        $input->setInput('User=!1,!2,!3,!4,!5,!6,!7,!10,!11,!12,!13,!14,!15,!18');

        $formatter = $this->newFormatter();
        $input->setField('user', FilterField::create('user', new Number(), false, true));

        if (!$formatter->formatInput($input)) {
            $this->fail(print_r($formatter->getMessages(), true));
        }

        $filters = $formatter->getFilters();

        $expectedValues = array();
        $expectedValues['user'] = new FilterValuesBag('user', '!1,!2,!3,!4,!5,!6,!7,!10,!11,!12,!13,!14,!15,!18', array(), array(13 => new SingleValue('18')), array(), array(), array(new Range('1', '7'), new Range('10', '15')));

        $this->assertEquals($expectedValues, $filters[0]);
    }
}
