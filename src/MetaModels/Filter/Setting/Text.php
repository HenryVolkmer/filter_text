<?php

/**
 * This file is part of MetaModels/filter_text.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage FilterText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IMetaModel;

/**
 * Filter "text field" for FE-filtering, based on filters by the MetaModels team.
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
        if (empty($arrFilterUrl[$this->getParamName()])) {
            $objFilter->addFilterRule(new StaticIdList(null));
            return;
        }

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
            case 'regexp':
                $this->doRegexpSearch($objFilter, $arrFilterUrl);
        }
    }

    /**
     * Make a simple search with a like.
     *
     * @param string   $strTextSearch The mode for the search.
     *
     * @param IFilter  $objFilter     The filter to append the rules to.
     *
     * @param string[] $arrFilterUrl  The parameters to evaluate.
     *
     * @return void
     */
    private function doSimpleSearch($strTextSearch, $objFilter, $arrFilterUrl)
    {
        $objMetaModel  = $this->getMetaModel();
        $objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
        $arrLanguages  = $this->getAvailableLanguages($objMetaModel);
        $strParamName  = $this->getParamName();
        $strParamValue = $arrFilterUrl[$strParamName];

        // React on wildcard, overriding the search type.
        if (strpos($strParamValue, '*') !== false) {
            $strTextSearch = 'exact';
        }

        // Type of search.
        switch ($strTextSearch) {
            case 'beginswith':
                $strWhat = $strParamValue . '*';
                break;
            case 'endswith':
                $strWhat = '*' . $strParamValue;
                break;
            case 'exact':
                $strWhat = $strParamValue;
                break;
            default:
                $strWhat = '*' . $strParamValue . '*';
                break;
        }

        if ($objAttribute && $strParamName && $strParamValue !== null) {
            $objFilter->addFilterRule(new SearchAttribute($objAttribute, $strWhat, $arrLanguages));

            return;
        }

        $objFilter->addFilterRule(new StaticIdList(null));
    }

    /**
     * Do a complex search with each word. Search for all words or for any word.
     *
     * @param string   $strTextSearch The mode any or all.
     *
     * @param IFilter  $objFilter     The filter to append the rules to.
     *
     * @param string[] $arrFilterUrl  The parameters to evaluate.
     *
     * @return void
     */
    private function doComplexSearch($strTextSearch, $objFilter, $arrFilterUrl)
    {
        $objMetaModel  = $this->getMetaModel();
        $objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
        $arrLanguages  = $this->getAvailableLanguages($objMetaModel);
        $strParamName  = $this->getParamName();
        $strParamValue = $arrFilterUrl[$strParamName];
        $parentFilter  = null;
        $words         = [];

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

            default:
                // Do nothing. Because the parent function saved us. The value have to be any or all.
                break;
        }

        if ($objAttribute && $strParamName && $strParamValue !== null && $parentFilter) {
            foreach ($words as $word) {
                $subFilter = $objMetaModel->getEmptyFilter();
                $subFilter->addFilterRule(new SearchAttribute($objAttribute, '%' . $word . '%', $arrLanguages));
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
     * Make a simple search with a regexp.
     *
     * @param IFilter  $objFilter     The filter to append the rules to.
     *
     * @param string[] $arrFilterUrl  The parameters to evaluate.
     *
     * @return void
     */
    private function doRegexpSearch($objFilter, $arrFilterUrl)
    {
        $objMetaModel  = $this->getMetaModel();
        $objAttribute  = $objMetaModel->getAttributeById($this->get('attr_id'));
        $strParamName  = $this->getParamName();
        $strParamValue = $arrFilterUrl[$strParamName];
        $strPattern    = $this->get('pattern');

        if ($objAttribute && $strParamName && $strParamValue !== null) {
            if (empty($strPattern) || substr_count($strPattern, '%s') != 1) {
                $strPattern = '%s';
            }

            $strRegex = sprintf($strPattern, $strParamValue);

            $strQuery = sprintf(
                'SELECT id FROM %s WHERE %s REGEXP \'%s\'',
                $objMetaModel->getTableName(),
                $objAttribute->getColName(),
                $strRegex
            );

            $objFilter->addFilterRule(new SimpleQuery($strQuery));

            return;
        }

        $objFilter->addFilterRule(new StaticIdList(null));
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
            return [];
        }

        if (!($attribute = $this->getFilteredAttribute())) {
            return [];
        }

        $arrReturn = [];
        $this->addFilterParam($this->getParamName());

        // Text search.
        $arrCount  = [];
        $arrWidget = [
            'label'     => [
                $this->getLabel(),
                'GET: ' . $this->getParamName()
            ],
            'inputType' => 'text',
            'count'     => $arrCount,
            'showCount' => $objFrontendFilterOptions->isShowCountValues(),
            'eval'      => [
                'colname'     => $attribute->getColname(),
                'urlparam'    => $this->getParamName(),
                'template'    => $this->get('template'),
                'placeholder' => $this->get('placeholder'),
            ]
        ];

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
        return [];
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

    /**
     * Get available langauges.
     *
     * @param IMetaModel $objMetaModel The metamodel.
     *
     * @return array|null|\string[]
     */
    private function getAvailableLanguages(IMetaModel $objMetaModel)
    {
        return ($objMetaModel->isTranslated() && $this->get('all_langs'))
            ? $objMetaModel->getAvailableLanguages()
            : [$objMetaModel->getActiveLanguage()];
    }
}
