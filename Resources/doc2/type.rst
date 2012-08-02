Type
====

Filtering types for working with values,
each type implements its own way of handling a value including
validation/sanitizing and possible optimizing.

Out of the box the basic types like Date/Time and numbers supported,
but building your own types is also possible and very simple.

All *built-in* types are local aware and require either
the International extension or Symfony Intl Stubs
(The Symfony stub may not support all locales).

Secondly, all *built-in* types support comparison
and optimizing when possible.

All the build-in type are registered in the Service container,
see Resources/config/record_filter.xml for there name.

Its possible but not recommended to overwrite the build-in types by using the same alias.

Configuration
-------------

Types implementing ConfigurableTypeInterface
can be configured with extra options using the setOptions() method of the type.

When building the FieldSet.

.. code-block:: html+php

    /* ... */

    use Rollerworks\Bundle\RecordFilterBundle\Type\Date;

    $fieldSet->set(new FilterField('name', new Date(array('max' => '2015-10-14'))));

Changing an existing FieldSet.

.. code-block:: html+php

    /* ... */

    $fieldSet->get('field_name')->getType()->setOptions(array('max' => '2015-10-14'));

Text
----

Handles text values as-is, this type can be seen as 'abstract' for more strict handling.

DateTime
--------

DateTime related types can be used for working with either date/time or a combination of both.

The following options can be set.

+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| Option            | Description                                                                                            | Accepted values      |
+===================+========================================================================================================+======================+
| min               | Minimum value. must be lower then max (default is NULL)                                                | DateTime object,NULL |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| max               | Maximum value. must be higher then min (default is NULL)                                               | DateTime object,NULL |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| time_optional     | If the time is optional (DateTime type only)                                                           | Boolean              |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+

Number
------

Handles numbers, can be localized.

Note: When working with big numbers (beyond maximum php value),
either bcmath or GMP must be installed and the configuration **must** use an string.

The following options can be set.

+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| Option            | Description                                                                                            | Accepted values      |
+===================+========================================================================================================+======================+
| min               | Minimum value. must be lower then max (default is NULL)                                                | string,integer,NULL  |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| max               | Maximum value. must be higher then min (default is NULL)                                               | string,integer,NULL  |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+

Decimal
-------

Handles Decimal values, can be localized.

Note: When working with big numbers (beyond maximum php value),
either bcmath or GMP must be installed and the configuration **must** use an string.

The following options can be set.

+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| Option            | Description                                                                                            | Accepted values      |
+===================+========================================================================================================+======================+
| min               | Minimum value. must be lower then max (default is NULL)                                                | string,float,NULL    |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+
| max               | Maximum value. must be higher then min (default is NULL)                                               | string,float,NULL    |
+-------------------+--------------------------------------------------------------------------------------------------------+----------------------+

Making your own
---------------

Often you will find that the build-in types are not enough,
luckily making your own type is very ease.

Extending
~~~~~~~~~

To safe your self some work, extending an existing one is an good option.

For example: you want to be able to handle client numbers that are prefixed like C30320.

Using the Number type and overwriting the validateValue() and sanitizeString() is enough.

.. code-block:: html+php

    use Rollerworks\Bundle\RecordFilterBundle\Type\Number;
    use Rollerworks\Bundle\RecordFilterBundle\MessageBag;

    class CustomerType extends Number
    {
        public function sanitizeString($value)
        {
            $value = ltrim($value, 'Cc');

            return parent::sanitizeString($value);
        }

        public function validateValue($value, &$message = null, MessageBag $messageBag = null)
        {
            $value = ltrim($value, 'Cc');

            return parent::validateValue($value, $message, $messageBag);
        }
    }

*Please remember that not all types may use strings,
DateTime types use an extended \DateTime class for passing information between methods.*

From Scratch
~~~~~~~~~~~~

Creating your own type takes 2 simple steps.

1. Creating the Class
2. Registering it in the Container

For this little tutorial we are going to create an type that can handle an status flag.

    The status can be localized and converted back to an label,
    and as a little bonus the Value can matched for usage with FilterQuery input.

.. code-block:: html+php

    namespace Acme\Invoice\RecordFilter\Type;

    use Symfony\Component\Translation\TranslatorInterface;
    use Rollerworks\Bundle\RecordFilterBundle\Type\FilterTypeInterface;
    use Rollerworks\Bundle\RecordFilterBundle\MessageBag;
    use Rollerworks\Bundle\RecordFilterBundle\Type\ValueMatcherInterface;

    class InvoiceStatusType implements FilterTypeInterface, ValueMatcherInterface
    {
        private $statusToString = array();
        private $stringToStatus = array();
        private $match;

        public function setTranslator(TranslatorInterface $translator)
        {
            foreach (array('concept', 'unpaid', 'paid') as $status) {
                // Get the label using the translator
                $label = $translator->trans($status, array(), 'invoice');

                $this->stringToStatus[$label] = $status;
                $this->statusToString[$status] = $label;
            }
        }

        public function sanitizeString($value)
        {
            // Normally its better to use mb_strtolower()
            $value = strtolower($value);

            if (isset($this->stringToStatus[$value])) {
                $this->stringToStatus[$value];
            }

            return $value;
        }

        public function formatOutput($value)
        {
            return isset($this->statusToString[$value]) ? $this->statusToString[$value] : $value;
        }

        public function dumpValue($value)
        {
            return $value;
        }

        /**
         * Not used.
         */
        public function isHigher($input, $nextValue)
        {
            return false;
        }

        /**
         * Not used.
         */
        public function isLower($input, $nextValue)
        {
            return true;
        }

        public function isEqual($input, $nextValue)
        {
            return ($input === $nextValue);
        }

        public function validateValue($value, &$message = null, MessageBag $messageBag = null)
        {
            $message = 'This is not an legal invoice status.';

            $value = strtolower($value);

            if (!isset($this->stringToStatus[$value])) {
                return false;
            }

            return true;
        }

        public function getMatcherRegex()
        {
            // This method gets called multiple times so cache the outcome
            if (null === $this->match) {
                $labels = $this->stringToStatus;

                // Escape the label to prevent mistaken regex-match
                array_map(function ($label) { return preg_quote($label, '#'); }, $labels);

                // Match must be an none-capturing group
                $this->match = sprintf('(?:%s)', implode('|', $labels));
            }

            return $this->match;
        }
    }

Now we need to register our type in the service container.

.. configuration-block::

    .. code-block:: yaml

        services:
            acme_invoice.record_filter.status_type:
                class: Acme\Invoice\RecordFilter\Type\InvoiceStatusType
                calls:
                    - [ setTranslator, [ @translator ] ]
                tags:
                    -  { name: rollerworks_record_filter.filter_type, alias: acme_invoice_type }

    .. code-block:: xml

        <service id="acme_invoice.record_filter.status_type" class="Acme\Invoice\RecordFilter\Type\InvoiceStatusType">
            <!-- Our Type needs the Translator -->
            <call method="setContainer">
                <argument type="service" id="translator"/>
            </call>

            <tag name="rollerworks_record_filter.filter_type" alias="acme_invoice_type" />
        </service>

    .. code-block:: php

        $container->setDefinition(
            'acme_invoice.record_filter.status_type',
            new Definition('Acme\Invoice\RecordFilter\Type\InvoiceStatusType'),
            array(new Reference('translator'))
        )
        ->addMethodCall('setTranslator', array(new Reference('translator')))
        ->addTag('rollerworks_record_filter.filter_type', array('alias' => 'acme_invoice_type'));

Advanced types
--------------

An type can be *extended* with extra functionality for
more advanced optimization and handling.

Look at the build-in types if you need help implementing them.

ValueMatcherInterface
~~~~~~~~~~~~~~~~~~~~~

Implement the ``Rollerworks\Bundle\RecordFilterBundle\Type\ValueMatcherInterface``
to provide an regex-based matcher for the value.

This is used for the Input component, so its not required to 'always'
use quotes when the value contains a dash or comma.

ConfigurableTypeInterface
~~~~~~~~~~~~~~~~~~~~~~~~~

Implement the ``Rollerworks\Bundle\RecordFilterBundle\Type\ConfigurableTypeInterface``
when the type support dynamic configuration for an example an maximum value or such.

    Note: The constructor should accept setting options, for ease of use.

This uses the Symfony OptionsResolver component.

OptimizableInterface
~~~~~~~~~~~~~~~~~~~~

Implement the ``Rollerworks\Bundle\RecordFilterBundle\Formatter\OptimizableInterface``
if the values can be further optimized.
Optimizing includes removing redundant values and changing the filtering strategy.

An example can be, where you have an 'Status' type which only accepts 'active', 'not-active' and 'remove'.
If ***all*** the possible values are chosen, the values are redundant and the filter should be removed.

ValuesToRangeInterface
~~~~~~~~~~~~~~~~~~~~~~

Implement the ``Rollerworks\Bundle\RecordFilterBundle\Formatter\ValuesToRangeInterface``
to converted an connected-list of values to ranges.

Connected values are values where the current value increased by one equals the next value.

1,2,3,4,5,8,10 is converted to 1-5,8,10
