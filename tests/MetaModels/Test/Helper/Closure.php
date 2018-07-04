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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/filter_text/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Helper;

/**
 * This class is a polyfill for use the Closure class < php 7.1
 *
 * @see \Closure
 */
class Closure
{
    /**
     * This is the polyfill method for use in < php 7.1
     *
     * @see \Closure::fromCallable()
     *
     * @param callable $callable
     *
     * @return \Closure
     */
    public static function fromCallable(callable $callable)
    {
        // In case we've got it native, let's use that native one!
        if (\method_exists(\Closure::class, 'fromCallable')) {
            return \Closure::fromCallable($callable);
        }

        return $callable;
    }
}
