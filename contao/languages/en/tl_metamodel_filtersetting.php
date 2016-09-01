<?php

/**
 * This file is part of MetaModels/filter_text.
 *
 * (c) 2012-2016 The MetaModels team.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['text']        = 'Text filter';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['textsearch'][0]            = 'Search type';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['textsearch'][1]            = 'Finding text parts.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['placeholder'][0]           = 'Placeholder';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['placeholder'][1]           =
    'Show this text as long as the field is empty (requires HTML5).';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delimiter'][0]             = 'Delimeter';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delimiter'][1]             =
    'Set delimeter for search type "any" or "all" - default delimeter is space.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references']['exact']      = 'exact search';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references']['beginswith'] = 'begins with search term';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references']['endswith']   = 'ends with search term';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references']['any']        = 'search any word (OR)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references']['all']        = 'search all words (AND)';
