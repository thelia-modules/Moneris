<?php
/**
* This class has been generated by TheliaStudio
* For more information, see https://github.com/thelia-modules/TheliaStudio
*/

namespace Moneris\Form\Type\Base;

use Thelia\Core\Form\Type\Field\AbstractIdType;
use Moneris\Model\MonerisErrorsQuery;

/**
 * Class MonerisErrors
 * @package Moneris\Form\Base
 * @author TheliaStudio
 */
class MonerisErrorsIdType extends AbstractIdType
{
    const TYPE_NAME = "moneris_errors_id";

    protected function getQuery()
    {
        return new MonerisErrorsQuery();
    }

    public function getName()
    {
        return static::TYPE_NAME;
    }
}