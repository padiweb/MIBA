<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
<<<<<<< HEAD
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
=======
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Application Controller Class
 *
<<<<<<< HEAD
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
=======
 * Modified for PHP 8.2+ compatibility:
 * - Removed all =& (assign by reference) from constructor
 * - Uses __get()/__isset() to proxy property access via $_ci_props[]
 * - No dynamic properties created, so no E_DEPRECATED warnings
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/general/controllers.html
 */
class CI_Controller {

	/**
	 * Reference to the CI singleton
	 *
	 * @var	object
	 */
	private static $instance;

	/**
<<<<<<< HEAD
=======
	 * Storage for CI library instances assigned dynamically.
	 * Avoids PHP 8.2 "Creation of dynamic property" deprecation.
	 *
	 * @var array
	 */
	protected $_ci_props = [];

	// Explicit declarations for the most common CI properties
	// so IDE autocomplete still works normally.
	public $load;
	public $benchmark;
	public $hooks;
	public $config;
	public $utf8;
	public $uri;
	public $router;
	public $output;
	public $security;
	public $input;
	public $lang;
	public $db;
	public $session;
	public $form_validation;
	public $pagination;
	public $upload;
	public $email;
	public $cache;

	// --------------------------------------------------------------------

	/**
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		self::$instance =& $this;

		// Assign all the class objects that were instantiated by the
		// bootstrap file (CodeIgniter.php) to local class variables
		// so that CI can run as one big super object.
<<<<<<< HEAD
		foreach (is_loaded() as $var => $class)
		{
			$this->$var =& load_class($class);
		}

		$this->load =& load_class('Loader', 'core');
=======
		// NOTE: We use = instead of =& here to avoid
		// "Cannot assign by reference to overloaded object" on PHP 8.2+.
		// CI objects are already singletons so no reference needed.
		foreach (is_loaded() as $var => $class)
		{
			$this->$var = load_class($class);
		}

		$this->load = load_class('Loader', 'core');
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
		$this->load->initialize();
		log_message('info', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
<<<<<<< HEAD
=======
	 * PHP 8.2+ magic: intercept writes to undeclared properties.
	 * Routes them into $_ci_props[] instead.
	 */
	public function __set(string $name, mixed $value): void
	{
		$this->_ci_props[$name] = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * PHP 8.2+ magic: read back properties from $_ci_props[].
	 */
	public function &__get(string $name): mixed
	{
		if (array_key_exists($name, $this->_ci_props))
		{
			return $this->_ci_props[$name];
		}

		$null = NULL;
		return $null;
	}

	// --------------------------------------------------------------------

	/**
	 * PHP 8.2+ magic: isset() check for dynamic properties.
	 */
	public function __isset(string $name): bool
	{
		return isset($this->_ci_props[$name]);
	}

	// --------------------------------------------------------------------

	/**
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
	 * Get the CI singleton
	 *
	 * @static
	 * @return	object
	 */
	public static function &get_instance()
	{
		return self::$instance;
	}

<<<<<<< HEAD
}
=======
}
>>>>>>> 7892da24966aaa2c8b68947b83186b7d69af2156
