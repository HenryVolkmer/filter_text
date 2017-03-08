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
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metapalettes']['text extends default'] = array
(
    '+config' => array('attr_id', 'urlparam', 'label', 'template', 'textsearch', 'placeholder'),
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metasubselectpalettes']['textsearch'] = array
(
    'any' => array('delimiter'),
    'all' => array('delimiter')
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['textsearch'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['textsearch'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('exact', 'beginswith', 'endswith', 'any', 'all'),
    'reference' => $GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references'],
    'eval'      => array('tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true)
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['delimiter'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delimiter'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50')
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['placeholder'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['placeholder'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('tl_class' => 'w50')
);
