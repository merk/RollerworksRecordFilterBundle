<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Dumper;

use Rollerworks\Bundle\RecordFilterBundle\Formatter\FormatterInterface;
use Rollerworks\Bundle\RecordFilterBundle\Type\FilterTypeInterface;
use Rollerworks\Bundle\RecordFilterBundle\Value\FilterValuesBag;
use Rollerworks\Bundle\RecordFilterBundle\Value\Range;

/**
 * Dump the filtering preferences as formatted XML (Extensible Markup Language).
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class XML extends AbstractDumper
{
    /**
     * {@inheritdoc}
     */
    public function dumpFilters(FormatterInterface $formatter)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $filters = $dom->createElement('filters');
        $groups  = $dom->createElement('groups');

        foreach ($formatter->getFilters() as $fields) {
            $group = $dom->createElement('group');

            foreach ($fields as $field => $values) {
                $type = $formatter->getFieldSet()->get($field)->getType();

                $fieldNode = $dom->createElement('field');
                $fieldNode->setAttribute('name', $field);

                self::createField($type, $values, $fieldNode, $dom);

                if (count($fieldNode->childNodes) > 0) {
                    $group->appendChild($fieldNode);
                }
            }

            if (count($group->childNodes) > 0) {
                $groups->appendChild($group);
            }
        }

        if (count($groups->childNodes) < 1) {
            throw new \UnexpectedValueException('No groups or filters where returned by the formatter.');
        }

        $filters->appendChild($groups);
        $dom->appendChild($filters);

        return $dom->saveXML();
    }

    /**
     * Set to return the output to an read friendly format.
     *
     * Setting this will make the XML more readable but also increase the content size.
     *
     * FIXME Needs implementation
     */
    public function setFormatOutput()
    {
    }

    /**
     * @param null|FilterTypeInterface $type
     * @param FilterValuesBag          $filter
     * @param \DOMNode                 $fieldNode
     * @param \DOMDocument             $dom
     */
    private static function createField(FilterTypeInterface $type = null, FilterValuesBag $filter, \DOMNode $fieldNode, \DOMDocument $dom)
    {
        if ($filter->hasSingleValues()) {
            $singleValues = $dom->createElement('single-values');

            foreach ($filter->getSingleValues() as $value) {
                $valueNode = $dom->createElement('value');

                $valueNode->appendChild($dom->createTextNode(self::dumpValue($type, $value->getValue())));
                $singleValues->appendChild($valueNode);
            }

            $fieldNode->appendChild($singleValues);
        }

        if ($filter->hasExcludes()) {
            $excludedValues = $dom->createElement('excluded-values');

            foreach ($filter->getExcludes() as $value) {
                $valueNode = $dom->createElement('value');

                $valueNode->appendChild($dom->createTextNode(self::dumpValue($type, $value->getValue())));
                $excludedValues->appendChild($valueNode);
            }

            $fieldNode->appendChild($excludedValues);
        }

        if ($filter->hasRanges()) {
            $ranges = $dom->createElement('ranges');

            foreach ($filter->getRanges() as $range) {
                $ranges->appendChild(self::createRangeNode($type, $range, $dom));
            }

            $fieldNode->appendChild($ranges);
        }

        if ($filter->hasExcludedRanges()) {
            $ranges = $dom->createElement('excluded-ranges');

            foreach ($filter->getExcludedRanges() as $range) {
                $ranges->appendChild(self::createRangeNode($type, $range, $dom));
            }

            $fieldNode->appendChild($ranges);
        }

        if ($filter->hasCompares()) {
            $compares = $dom->createElement('compares');

            foreach ($filter->getCompares() as $compare) {
                $compareNode = $dom->createElement('compare');
                $compareNode->setAttribute('opr', $compare->getOperator());
                $compareNode->appendChild($dom->createTextNode(self::dumpValue($type, $compare->getValue())));

                $compares->appendChild($compareNode);
            }

            $fieldNode->appendChild($compares);
        }
    }

    /**
     * @param null|FilterTypeInterface $type
     * @param Range                    $range
     * @param \DOMDocument             $dom
     *
     * @return \DOMElement
     */
    private static function createRangeNode(FilterTypeInterface $type = null, Range $range, \DOMDocument $dom)
    {
        $rangeNode = $dom->createElement('range');

        $lowerValNode = $dom->createElement('lower');
        $lowerValNode->appendChild($dom->createTextNode(self::dumpValue($type, $range->getLower())));

        $higherValNode = $dom->createElement('higher');
        $higherValNode->appendChild($dom->createTextNode(self::dumpValue($type,  $range->getUpper())));

        $rangeNode->appendChild($lowerValNode);
        $rangeNode->appendChild($higherValNode);

        return $rangeNode;
    }
}
