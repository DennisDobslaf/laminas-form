<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use DateTime;
use IntlDateFormatter;
use Laminas\Form\Element\DateSelect as DateSelectElement;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormMonthSelect as FormMonthSelectHelper;

class FormDateSelect extends FormMonthSelectHelper
{
    /**
     * Render a date element that is composed of three selects
     *
     * @param  ElementInterface $element
     * @throws \Laminas\Form\Exception\InvalidArgumentException
     * @throws \Laminas\Form\Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof DateSelectElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Laminas\Form\Element\DateSelect',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $selectHelper = $this->getSelectElementHelper();
        $pattern      = $this->parsePattern();

        $daysOptions   = $this->getDaysOptions($pattern['day']);
        $monthsOptions = $this->getMonthsOptions($pattern['month']);
        $yearOptions   = $this->getYearsOptions($element->getMinYear(), $element->getMaxYear());

        $dayElement   = $element->getDayElement()->setValueOptions($daysOptions);
        $monthElement = $element->getMonthElement()->setValueOptions($monthsOptions);
        $yearElement  = $element->getYearElement()->setValueOptions($yearOptions);

        if ($element->shouldCreateEmptyOption()) {
            $dayElement->setEmptyOption('');
            $yearElement->setEmptyOption('');
            $monthElement->setEmptyOption('');
        }

        $markup = array();
        $markup[$pattern['day']]   = $selectHelper->render($dayElement);
        $markup[$pattern['month']] = $selectHelper->render($monthElement);
        $markup[$pattern['year']]  = $selectHelper->render($yearElement);

        $markup = sprintf(
            '%s %s %s %s %s',
            $markup[array_shift($pattern)],
            array_shift($pattern), // Delimiter
            $markup[array_shift($pattern)],
            array_shift($pattern), // Delimiter
            $markup[array_shift($pattern)]
        );

        return $markup;
    }

    /**
     * Create a key => value options for days
     *
     * @param string  $pattern Pattern to use for days
     * @return array
     */
    protected function getDaysOptions($pattern)
    {
        $keyFormatter   = new IntlDateFormatter($this->getLocale(), null, null, null, null, 'dd');
        $valueFormatter = new IntlDateFormatter($this->getLocale(), null, null, null, null, $pattern);
        $date           = new DateTime('1970-01-01');

        $result = array();
        for ($day = 1; $day <= 31; $day++) {
            $key   = $keyFormatter->format($date);
            $value = $valueFormatter->format($date);
            $result[$key] = $value;

            $date->modify('+1 day');
        }

        return $result;
    }
}
