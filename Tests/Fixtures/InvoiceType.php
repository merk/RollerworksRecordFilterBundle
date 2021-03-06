<?php

/**
 * This file is part of the RollerworksRecordFilterBundle.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\RecordFilterBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Rollerworks\Bundle\RecordFilterBundle\MessageBag;
use Rollerworks\Bundle\RecordFilterBundle\Type\FilterTypeInterface;
use Rollerworks\Bundle\RecordFilterBundle\Type\ValueMatcherInterface;

/**
 * InvoiceType.
 */
class InvoiceType implements FilterTypeInterface, ValueMatcherInterface, ContainerAwareInterface
{
    protected $fool;

    /**
     * @param string $foo
     * @param string $bar
     */
    public function __construct($foo = 'bar', $bar = null)
    {
        $this->fool = $foo;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizeString($input)
    {
        return $input;
    }

    /**
     * {@inheritdoc}
     */
    public function formatOutput($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function dumpValue($input)
    {
        return $input;
    }

    /**
     * {@inheritdoc}
     */
    public function isHigher($input, $nextValue)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLower($input, $nextValue)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqual($input, $nextValue)
    {
        return ($input === $nextValue);
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($input, &$message = null, MessageBag $messageBag = null)
    {
        $message = 'This is not an valid invoice';

        return (preg_match('/^F?\d{4}-\d+$/i', $this->sanitizeString($input)) ? true : false );
    }

    /**
     * {@inheritdoc}
     */
    public function getMatcherRegex()
    {
        return '(?:F\d{4}-\d+)';
    }
}
