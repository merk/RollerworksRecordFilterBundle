<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Tests;

use Rollerworks\Bundle\RecordFilterBundle\Type\Number;
use Rollerworks\Bundle\RecordFilterBundle\MessageBag;

class NumberTest extends \Rollerworks\Bundle\RecordFilterBundle\Tests\TestCase
{
    /**
     * @dataProvider getDataForSanitation
     */
    public function testSanitize($locale, $input, $expected, $expectFail = false)
    {
        if ($expectFail) {
            return;
        }

        \Locale::setDefault($locale);

        $type = new Number();

        $this->assertEquals($expected, $type->sanitizeString($input));
    }

    /**
     * @dataProvider getDataForSanitation
     */
    public function testValidation($locale, $input, $expected, $expectFail = false)
    {
        \Locale::setDefault($locale);

        $type = new Number();

        if ($expectFail) {
            $this->assertFalse($type->validateValue($input));
        } else {
            $this->assertTrue($type->validateValue($input));
        }
    }

    /**
     * @dataProvider getDataForAdvancedValidation
     */
    public function testValidationAdvanced($input, $options = array(), $expectMessage = false)
    {
        if ('en' !== \Locale::getDefault()) {
            \Locale::setDefault('en');
        }

        $type = new Number($options);

        $messageBag = new MessageBag($this->translator);

        if (is_array($expectMessage)) {
            $this->assertFalse($type->validateValue($input, $message, $messageBag), sprintf('Assert "%s" is invalid', $input));
            $this->assertEquals($expectMessage, $messageBag->get('error'), sprintf('Assert "%s" is invalid and messages are equal.', $input));
        } else {
            $this->assertTrue($type->validateValue($input, $message, $messageBag), sprintf('Assert "%s" is valid. %s ', $input, implode(', ', $messageBag->get('error'))));
        }
    }

    /**
     * @dataProvider getDataForCompare
     */
    public function testCompares($first, $second, $comparison = null)
    {
        $type = new Number();

        if ('==' === $comparison) {
            $this->assertTrue($type->isEqual($first, $second), sprintf('"%s" should equal "%s"', $first, $second));
        } elseif ('!=' === $comparison) {
            $this->assertFalse($type->isEqual($first, $second), sprintf('"%s" should not equal "%s"', $first, $second));
        } else {
            $this->assertTrue($type->isLower($second, $first), sprintf('"%s" should be lower then "%s"', $second, $first));
            $this->assertFalse($type->isLower($first, $second), sprintf('"%s" should not be lower then "%s"', $first, $second));

            $this->assertTrue($type->isHigher($first, $second), sprintf('"%s" should be higher then "%s"', $first, $second));
            $this->assertFalse($type->isHigher($second, $first), sprintf('"%s" should not be higher then "%s"', $second, $first));
        }
    }

    /**
     * @dataProvider getDataForGetHigherValue
     */
    public function testGetHigherValue($input, $expected)
    {
        $type = new Number();
        $this->assertEquals($expected, $type->getHigherValue($input));
    }

    public static function getDataForSanitation()
    {
        return array(
            // $locale, $input, $expected, $expectFail
            array('nl_NL', '4446546', '4446546'),
            array('nl_NL', '004446546', '004446546'),
            array('nl_NL', '4446546000000000000000000000', '4446546000000000000000000000'),
            array('en_US', '4446546', '4446546'),
            array('uz_Arab', '۰۵', '05'),
            array('uz_Arab', '۵۵۵۵۵۵۵۵۵۵۵۵۵۵۵۵۵۵۰', '5555555555555555550'),
            array('en_US', '۰۵', '05'), // Not really valid, but the validation must past

            array('en_US', '4446546.00', '', true),
            array('en_US', 'D4446546.00', '', true),
            array('en_US', 'A03', '', true),
        );
    }

    public static function getDataForAdvancedValidation()
    {
        return array(
            // $input, $options, $expectMessage
            array('12000', array('min' => '12000')),
            array('12000', array('min' => '11000')),

            array('12000', array('max' => '12000')),
            array('12000', array('max' => '12001')),

            array('70000000000000000', array('max' => '70000000000000000')),
            array('70000000000000001', array('max' => '80000000000000000')),

            array('70000000000000000', array('min' => '70000000000000000')),
            array('70000000000000000', array('min' => '60000000000000001')),

            array('12000', array('min' => '13000'), array('This value should be 13,000 or more.')),
            array('15000', array('max' => '12000'), array('This value should be 12,000 or less.')),

            array('12000000', array('min' => '13000000'), array('This value should be 13,000,000 or more.')),
            array('15000000', array('max' => '12000000'), array('This value should be 12,000,000 or less.')),

            array('50000000000000000', array('min' => '60000000000000000'), array('This value should be 60,000,000,000,000,000 or more.')),
            array('70000000000000000', array('max' => '60000000000000000'), array('This value should be 60,000,000,000,000,000 or less.')),

            array('90000000000000000', array('min' => '70000000000000000', 'max' => '80000000000000000'), array('This value should be 80,000,000,000,000,000 or less.')),
            array('70000000000000000', array('min' => '80000000000000000', 'max' => '90000000000000000'), array('This value should be 80,000,000,000,000,000 or more.')),
        );
    }

    public static function getDataForCompare()
    {
        return array(
            // $first (higher), $second (lower), $comparison
            array('4554444644665', '4554444644665',   '=='),
            array('04554444644665', '04554444644665', '=='),
            array('04554444644665', '04554444644665', '=='),
            array('04554444644665', '455444464',      '!='),

            array('700', '600'),
            array('0700', '0600'),
            array('700', '-800'),
            array('0700', '-0800'),
            array('70000000000000000', '60000000000000000'),
            array('0070000000000000000', '0060000000000000000'),
            array('800000000000000', '-800000000000000'),
            array('44645464446544665', '446454644465'),
        );
    }

    public static function getDataForGetHigherValue()
    {
        return array(
            // $input, $expected
            array('700', '701'),
            array('0700', '0701'),
            array('700000000000000000000000000', '700000000000000000000000001'),
            array('-700000000000000000000000000', '-699999999999999999999999999'),
            array('-700', '-699'),
            array('-0700', '-0699'),
        );
    }
}
