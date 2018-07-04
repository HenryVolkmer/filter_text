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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\IFilterRule;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\Text;
use MetaModels\IMetaModel;
use MetaModels\Test\Helper\Closure;
use PHPUnit\Framework\TestCase;

/**
 * This tests the text filter.
 */
class TextTest extends TestCase
{
    /**
     * Data provider for testAddsFilterRule.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function addsFilterRuleForSimpleSearchProvider()
    {
        return [
            'search without value' => [
                [
                    'urlparam' => 'filtername'
                ],
                [],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(StaticIdList::class, $filterRule);
                    $this->assertSame(null, $filterRule->getMatchingIds());
                })
            ],
            'search for empty' => [
                [
                    'urlparam' => 'filtername'
                ],
                [
                    'filtername' => ''
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(StaticIdList::class, $filterRule);
                    $this->assertSame(null, $filterRule->getMatchingIds());
                })
            ],
            'search for two spaces' => [
                [
                    'urlparam' => 'filtername',
                    'attr_id'  => '1',
                    'textsearch' => 'exact',
                ],
                [
                    'filtername' => '  '
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(SearchAttribute::class, $filterRule);
                    $refl = new \ReflectionProperty(SearchAttribute::class, 'strValue');
                    $refl->setAccessible(true);
                    $this->assertSame('  ', $refl->getValue($filterRule));
                })
            ],
            'begins with' => [
                [
                    'urlparam'   => 'filtername',
                    'attr_id'    => '1',
                    'textsearch' => 'beginswith',
                ],
                [
                    'filtername' => 'herb'
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(SearchAttribute::class, $filterRule);
                    $refl = new \ReflectionProperty(SearchAttribute::class, 'strValue');
                    $refl->setAccessible(true);
                    $this->assertSame('herb*', $refl->getValue($filterRule));
                })
            ],
            'ends with' => [
                [
                    'urlparam'   => 'filtername',
                    'attr_id'    => '1',
                    'textsearch' => 'endswith',
                ],
                [
                    'filtername' => 'herb'
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(SearchAttribute::class, $filterRule);
                    $refl = new \ReflectionProperty(SearchAttribute::class, 'strValue');
                    $refl->setAccessible(true);
                    $this->assertSame('*herb', $refl->getValue($filterRule));
                })
            ],
            'exact match' => [
                [
                    'urlparam'   => 'filtername',
                    'attr_id'    => '1',
                    'textsearch' => 'exact',
                ],
                [
                    'filtername' => 'herb'
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(SearchAttribute::class, $filterRule);
                    $refl = new \ReflectionProperty(SearchAttribute::class, 'strValue');
                    $refl->setAccessible(true);
                    $this->assertSame('herb', $refl->getValue($filterRule));
                })
            ],
            'unknown type should trigger "contains"' => [
                [
                    'urlparam'   => 'filtername',
                    'attr_id'    => '1',
                    'textsearch' => 'unknown type',
                ],
                [
                    'filtername' => 'herb'
                ],
                Closure::fromCallable(function (IFilterRule $filterRule) {
                    $this->assertInstanceOf(SearchAttribute::class, $filterRule);
                    $refl = new \ReflectionProperty(SearchAttribute::class, 'strValue');
                    $refl->setAccessible(true);
                    $this->assertSame('*herb*', $refl->getValue($filterRule));
                })
            ],
        ];
    }

    /**
     * Test the various environments.
     *
     * @param array    $configuration The text filter configuration.
     * @param array    $filterUrl     The filter URL.
     * @param \Closure $validator     The validator function.
     *
     * @return void
     *
     * @dataProvider addsFilterRuleForSimpleSearchProvider()
     */
    public function testAddsFilterRuleForSimpleSearch($configuration, $filterUrl, \Closure $validator)
    {
        $metaModel = $this->getMockBuilder(IMetaModel::class)->getMock();
        if (isset($configuration['attr_id'])) {
            $metaModel
                ->expects($this->once())
                ->method('getAttributeById')
                ->with($configuration['attr_id'])
                ->willReturn($this->getMockBuilder(IAttribute::class)->getMock());
        } else {
            $metaModel
                ->expects($this->never())
                ->method('getAttributeById');
        }

        $collection = $this->getMockBuilder(ICollection::class)->getMock();
        $collection->method('getMetaModel')->willReturn($metaModel);

        $text = new Text($collection, $configuration);

        $filter = $this
            ->getMockBuilder(IFilter::class)
            ->getMock();

        $filter
            ->expects($this->once())
            ->method('addFilterRule')
            ->willReturnCallback($validator->bindTo($this));

        $text->prepareRules($filter, $filterUrl);
    }
}
