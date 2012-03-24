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

namespace Rollerworks\RecordFilterBundle\Formatter;

/**
 * Filter ValueMatcherInterface Interface.
 *
 * An field-type can implement this to provide an regex-based matcher for the value.
 * This way the user is not required to 'always' use quotes when the value contains an dash.
 *
 * Remember this is intended for __matching__ not ***validating***, make the regex as simple as possible.
 * And __never__ match more then necessary!
 *
 * Validating the value is always performed after matching the value.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface ValueMatcherInterface
{
    /**
     * Returns the regex (without delimiters).
     *
     * The regex is used for matching an value in the list and detecting end position when using an range.
     * So it should __always__ use none-capturing (?:), ***especially*** when using or '|', (?:regex1|regex2).
     *
     * In an list the regex is used as: {match-quoted}|{regex}-{regex}|{comparison-regex}?{regex}|[^,]+
     * You should never match an (optional) comma and the end, since this will cause unexpected result.
     *
     * @return string
     */
    public function getRegex();

    /**
     * Returns whether the regex can be used in (a) JavaScript (Widget).
     * When using byte ranges not supported by JavaScript this should return false
     *
     * @return bool
     */
    public function supportsJs();
}