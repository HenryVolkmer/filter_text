<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package      MetaModels
 * @subpackage   FilterText
 * @author       Christian de la Haye <service@delahaye.de>
 * @author       Andreas Isaak <info@andreas-isaak.de>
 * @author       Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author       David Molineus <mail@netzmacht.de>
 * @author       David Maack <david.maack@arcor.de>
 * @author       Stefan Heimes <stefan_heimes@hotmail.com>
 * @author       Christopher Boelter <christopher@boelter.eu>
 * @copyright    The MetaModels team.
 * @license      LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\FrontendIntegration\FrontendFilterOptions;

/**
 * Filter "text field" for FE-filtering, based on filters by the MetaModels team.
 *
 * @package       MetaModels
 * @subpackage    FrontendFilter
 * @author        Christian de la Haye <service@delahaye.de>
 * @author        Stefan Heimes <stefan_heimes@hotmail.com>
 */
class Text extends SimpleLookup
{
    /**
     * Overrides the parent implementation to always return true, as this setting is always optional.
     *
     * @return bool true if all matches shall be returned, false otherwise.
     */
    public function allowEmpty()
    {
        return true;
    }

    /**
     * Overrides the parent implementation to always return true, as this setting is always available for FE filtering.
     *
     * @return bool true as this setting is always available.
     */
    public function enableFEFilterWidget()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $strTextSearch = $this->get('textsearch');
        switch ($strTextSearch) {
            case 'beginswith':
            case 'endswith':
            case 'exact':
            default:
                $this->doSimpleSearch($strTextSearch, $objFilter, $arrFilterUrl);
                break;

            case 'any':
            case 'all':
                $this->doComplexSearch($strTextSearch, $objFilter, $arrFilterUrl);
                break;
        }
    }

    /**
     * @param string   $strTextSearch
     *
     * @param IFilter  $objFilter    The filter to append the rules to.
     *
     * @param string[] $arrFilterUrl The parameters to evaluate.
     *
     */
    private function doSimpleSearch($strTextSearch, $objFilter, $arrFilterUrl)
    {
        $objMetaModel  = $this->getMetaModel();
        $objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
        $strParamName  = $this->getParamName();
        $strParamValue = $arrFilterUrl[$strParamName];

        // React on wildcard, overriding the search type.
        if (strpos($strParamValue, '*') !== false) {
            $strTextSearch = 'exact';
        }

        // Type of search.
        switch ($strTextSearch) {
            case 'beginswith':
                $strWhat = $strParamValue . '%';
                break;
            case 'endswith':
                $strWhat = '%' . $strParamValue;
                break;
            case 'exact':
                $strWhat = $strParamValue;
                break;
            default:
                $strWhat = '%' . $strParamValue . '%';
                break;
        }

        if ($objAttribute && $strParamName && $strParamValue) {
            $objFilter->addFilterRule(new SearchAttribute($objAttribute, $strWhat));

            return;
        }

        $objFilter->addFilterRule(new StaticIdList(null));
    }

    /**
     * @param string   $strTextSearch
     *
     * @param IFilter  $objFilter    The filter to append the rules to.
     *
     * @param string[] $arrFilterUrl The parameters to evaluate.
     *
     */
    private function doComplexSearch($strTextSearch, $objFilter, $arrFilterUrl)
    {
        $objMetaModel  = $this->getMetaModel();
        $objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
        $strParamName  = $this->getParamName();
        $strParamValue = $arrFilterUrl[$strParamName];
        $parentFilter  = null;
        $words         = array();

        // Type of search.
        switch ($strTextSearch) {
            case 'any':
                $words        = $this->getWords($strParamValue);
                $parentFilter = new ConditionOr();
                break;
            case 'all':
                $words        = $this->getWords($strParamValue);
                $parentFilter = new ConditionAnd();
                break;
        }

        if ($objAttribute && $strParamName && $strParamValue && $parentFilter) {
            foreach ($words as $word) {
                $subFilter = $objMetaModel->getEmptyFilter();
                $subFilter->addFilterRule(new SearchAttribute($objAttribute, '%' . $word . '%'));
                $parentFilter->addChild($subFilter);
            }

            $objFilter->addFilterRule($parentFilter);

            return;
        }

        $objFilter->addFilterRule(new StaticIdList(null));
    }

    /**
     * Use the delimiter from the setting and make a list of words.
     *
     * @param string $string The list of words as a single string.
     *
     * @return array The list of word split on the delimiter.
     */
    private function getWords($string)
    {
        $delimiter = $this->get('delimiter');
        if (empty($delimiter)) {
            $delimiter = ' ';
        }

        return trimsplit($delimiter, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->enableFEFilterWidget()) {
            return array();
        }

        if (!($attribute = $this->getFilteredAttribute())) {
            return array();
        }

        $arrReturn = array();
        $this->addFilterParam($this->getParamName());

        // Address search.
        $arrCount  = array();
        $arrWidget = array(
            'label'     => array(
                $this->getLabel(),
                'GET: ' . $this->getParamName()
            ),
            'inputType' => 'text',
            'count'     => $arrCount,
            'showCount' => $objFrontendFilterOptions->isShowCountValues(),
            'eval'      => array(
                'colname'  => $attribute->getColname(),
                'urlparam' => $this->getParamName(),
                'template' => $this->get('template'),
            )
        );

        // Add filter.
        $arrReturn[$this->getParamName()] =
            $this->prepareFrontendFilterWidget($arrWidget, $arrFilterUrl, $arrJumpTo, $objFrontendFilterOptions);

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        return array();
    }

    /**
     * Add Param to global filter params array.
     *
     * @param string $strParam Name of filter param.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function addFilterParam($strParam)
    {
        $GLOBALS['MM_FILTER_PARAMS'][] = $strParam;
    }
}
