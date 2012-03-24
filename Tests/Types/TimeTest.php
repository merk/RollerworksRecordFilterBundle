<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Rollerscapes
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link    http://projects.rollerscapes.net/RollerFramework
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 */

namespace Rollerworks\RecordFilterBundle\Tests;

use Rollerworks\RecordFilterBundle\Formatter\Type\Time;

class TimeTest extends \PHPUnit_Framework_TestCase
{
    function testSanitize()
    {
        $type = new Time();

        $this->assertEquals('11:17', $type->sanitizeString('11.17'));
        $this->assertEquals('11:17', $type->sanitizeString('11:17'));

        $this->assertEquals('11:17', $type->sanitizeString('11.17AM'));
        $this->assertEquals('11:17', $type->sanitizeString('11:17AM'));

        $this->assertEquals('23:17', $type->sanitizeString('11.17PM'));
        $this->assertEquals('23:17', $type->sanitizeString('11:17PM'));

        $this->assertEquals('23:17', $type->sanitizeString('11.17pm'));
        $this->assertEquals('23:17', $type->sanitizeString('11:17pm'));

        $this->assertEquals('11:17:00', $type->sanitizeString('11.17:00AM'));
        $this->assertEquals('11:17:00', $type->sanitizeString('11:17:00AM'));

        $this->assertEquals('23:17:00', $type->sanitizeString('11.17:00PM'));
        $this->assertEquals('23:17:00', $type->sanitizeString('11:17:00PM'));

        $this->assertEquals('23:17:00', $type->sanitizeString('11.17:00pm'));
        $this->assertEquals('23:17:00', $type->sanitizeString('11:17:00pm'));

        $this->assertEquals('00:17:00', $type->sanitizeString('12.17:00pm'));
        $this->assertEquals('00:17:00', $type->sanitizeString('12:17:00pm'));
    }

    function testValidation()
    {
        $type = new Time();

        $this->assertTrue($type->validateValue('11:17'));
        $this->assertTrue($type->validateValue('11:17+02:00'));
        $this->assertTrue($type->validateValue('11:17+02:30'));
        $this->assertTrue($type->validateValue('11:17+0200'));
        $this->assertTrue($type->validateValue('11.17'));

        $this->assertFalse($type->validateValue('1117'));
        $this->assertFalse($type->validateValue('11-17'));
        $this->assertFalse($type->validateValue('11+17'));

        $this->assertFalse($type->validateValue('25:17'));
        $this->assertFalse($type->validateValue('25.17'));
    }


    function testHigher()
    {
        $type = new Time();

        $this->assertTrue($type->isHigher('11:17', '10:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertTrue($type->isHigher(date('H:i:sP', time() + 1020), date('H:i:s')));
        $this->assertTrue($type->isHigher(date('H:i:sO', time() + 1020), date('H:i:s')));
        $this->assertTrue($type->isHigher('13:17+02:00', '11:12+01:00'));
    }


    function testNotHigher()
    {
        $type = new Time();

        $this->assertFalse($type->isHigher('10:17', '11:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertFalse($type->isHigher(date('H:i:s'), date('H:i:sP', time() + 1020)));
        $this->assertFalse($type->isHigher(date('H:i:s'), date('H:i:sO', time() + 1020)));
        $this->assertFalse($type->isHigher('10:17+01:00', '13:17+02:00'));
    }


    function testLower()
    {
        $type = new Time();

        $this->assertTrue($type->isLower('10:17', '11:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertTrue($type->isLower(date('H:i:s', time() - 1020), date('H:i:sP')));
        $this->assertTrue($type->isLower(date('H:i:s', time() - 1020), date('H:i:sO')));
        $this->assertTrue($type->isLower('10:12+01:00', '11:17+02:00'));
    }


    function testNotLower()
    {
        $type = new Time();

        $this->assertFalse($type->isLower('11:17', '10:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertFalse($type->isLower(date('H:i:sP'), date('H:i:s', time() - 1020)));
        $this->assertFalse($type->isLower(date('H:i:sO'), date('H:i:s', time() - 1020)));
        $this->assertFalse($type->isLower('13:17+02:00', '11:17+01:00'));
    }


    function testEquals()
    {
        $type = new Time();

        $this->assertTrue($type->isEquals('10:17', '10:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertTrue($type->isEquals('12:10+0100', '13:10+0200'));
        $this->assertTrue($type->isEquals('12:10+01:00', '13:10+02:00'));
    }


    function testNotEquals()
    {
        $type = new Time();

        $this->assertFalse($type->isEquals('10:17', '11:17'));

        // Timezone (note: will automatically convert to UTC)
        $this->assertFalse($type->isEquals(date('H:i:sO'), date('H:i:s', time() - 60)));
        $this->assertFalse($type->isEquals(date('H:i:sP'), date('H:i:s', time() - 60)));
        $this->assertFalse($type->isEquals(date('H:i:sO'), date('H:i:sO', time() - 60)));
        $this->assertFalse($type->isEquals(date('H:i:sP'), date('H:i:sP', time() - 60)));
    }
}