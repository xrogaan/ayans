<?php
/** vim: set ts=4 expandtab:
 * ErrorNot Notifier http://github.com/errornot/ErrorNot
 * Copyright (C) 2010  Ludovic Bellière
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * @package ErrorNotException
 */
class ErrorNotException extends exception
{
    /**
     * Optionnal data
     * Will be used to send more infomation to ErrorNot
     *
     * @var array
     */
    protected $data;

    public function __construct($message=null, $code=0, $data=null, Exception $previous=null)
    {
        $this->data = (array) $data;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return boolean|array
     */
    public function getData()
    {
        if (!empty($data)) {
            return $data;
        } else {
            return false;
        }
    }
}
