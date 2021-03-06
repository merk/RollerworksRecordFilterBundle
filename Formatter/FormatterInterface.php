<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Formatter;

use Rollerworks\Bundle\RecordFilterBundle\FieldSet;

/**
 * RecordFiltering formatting interface.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @api
 */
interface FormatterInterface
{
    /**
     * Returns the formatted filters.
     *
     * This will return an array contain all the groups and there fields (per group).
     *
     * Like:
     * [group-n] => array(
     *   'field-name' => {\Rollerworks\Bundle\RecordFilterBundle\Value\FilterValuesBag object}
     * )
     *
     * @return array
     *
     * @api
     */
    public function getFilters();

    /**
     * Get the fieldSet of the last performed formatting.
     *
     * @return FieldSet
     *
     * @api
     */
    public function getFieldSet();
}
