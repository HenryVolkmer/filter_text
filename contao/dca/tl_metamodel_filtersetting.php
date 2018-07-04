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
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <mail@netzmacht.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metapalettes']['text extends default'] = array
(
    '+config'   => array('attr_id'),
    '+fefilter' => array('urlparam', 'label', 'template', 'textsearch', 'placeholder')
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metasubselectpalettes']['textsearch'] = array
(
    'any'    => array('delimiter'),
    'all'    => array('delimiter'),
    'regexp' => array('pattern')
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['textsearch'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['textsearch'],
    'exclude'   => true,
    'inputType' => 'select',
    'options'   => array('exact', 'beginswith', 'endswith', 'any', 'all', 'regexp'),
    'reference' => $GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['references'],
    'sql'       => 'varchar(32) NOT NULL default \'\'',
    'eval'      => array('tl_class' => 'w50', 'includeBlankOption' => true, 'submitOnChange' => true)
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['delimiter'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delimiter'],
    'exclude'   => true,
    'inputType' => 'text',
    'sql'       => 'varchar(255) NOT NULL default \'\'',
    'eval'      => array('tl_class' => 'w50')
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['pattern'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['pattern'],
    'exclude'       => true,
    'inputType'     => 'text',
    'default'       => '%s',
    'load_callback' => function ($varValue, $dc) {
        return !empty($varValue) ? $varValue : '%s';
    },
    'sql'           => 'varchar(255) NOT NULL default \'\'',
    'eval'          => array('tl_class' => 'w50', 'mandatory' => true, 'preserveTags' => true)
);

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['placeholder'] = array
(
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['placeholder'],
    'exclude'   => true,
    'inputType' => 'text',
    'sql'       => 'varchar(255) NOT NULL default \'\'',
    'eval'      => array('tl_class' => 'w50')
);
