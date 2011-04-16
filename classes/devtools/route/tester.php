<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Kohana 3 Route tester class.  Use it by calling [Route_Tester::test]
 *
 * @package	   Bluehawk/Devtools
 * @author     Michael Peters
 */
class Devtools_Route_Tester {
	
	/**
	 * @var string  url for test
	 */
	protected $_url;
	
	/**
	 * @var string  the route that this url matched
	 */
	protected $_route;
	
	/**
	 * @var array   the returned params from route 
	 */
	protected $_params = array();
	
	/**
	 * @var array   the optional expected params from the config file
	 */
	protected $_expected_params;

	/**
	 * Test a URL or an array of URLs to see which routes they match.
	 *
	 * If no param is passed it will test the current url:
	 *
	 *     // Test the current url
	 *     echo Route_Tester::test();
	 * 
	 * To test on a single url:
	 *
	 *     echo Route_Tester::test('some/url/to/test');
	 *
	 * To test several urls:
	 *
	 *     echo Route_Tester::test(array(
	 *         'some/url/to/test',
	 *         'another/url',
	 *         'guide/api/Class',
	 *         'guide/media/image.png',
	 *     ));
	 *
	 * You may also pass the route and parameters you expect, by passing each
	 * url as a key with an array of expected values.
	 *
	 *     $urls = array(
	 *         'guide/media/image.png' = array(
	 *             'route' => 'docs/media',
	 *             'controller' => 'userguide',
	 *             'action' => 'media',
	 *             'file' => 'image.png',
	 *         ),
	 *
	 *         'blog/5/some-title` = array(
	 *             'route' => 'blog',
	 *             'controller' => 'blog',
	 *             'action' => 'article',
	 *             'id' => '5',
	 *             'title' => 'some-title',
	 *         ),
	 *     );
	 *     echo Route_Tester::test($urls);
	 *
	 * It's useful to store your array of urls to be tested in a config file,
	 * for example in `application/config/my-route-tests.php` return an array
	 * similar to the previous examples then call:
	 *
	 *     echo Route_Tester::test(Kohana::config('your-route-tests'));
	 *
	 * @author    Michael Peters
	 * @license   http://creativecommons.org/licenses/by-sa/3.0/
	 */
	public static function test($urls = NULL)
	{
		// If no url provide, use the current url
		if ($urls === NULL)
		{
			$urls = Request::current()->uri();
		}
		return View::factory('devtools/route-test',array(
			// Get all the tests
			'tests' => Route_Tester::create_tests($urls),
		));
	}
	
	/**
	 * Get an array of Route_Tester objects from the config settings
	 *
	 * @param  $tests  A URL to test, or an array of URLs to test.
	 * @returns array  An array of Route_Tester objects
	 */
	public static function create_tests($tests)
	{
		if (is_string($tests))
		{
			$tests = array($tests);
		}
		
		$tests_instances = array();
		
		// Get the url and optional expected_params from the config
		foreach ($tests as $key => $value)
		{
			$current = new Route_Tester();
			
			if (is_array($value))
				$current->url($key)->expected_params($value);
			else
				$current->url($value);
		
			// Test each route, and save the route and params if it matches
			foreach (Route::all() as $route)
			{
				if ($current->params( $route->matches($current->url()) ))
				{
					$current->route(Route::name($route))
						->params( array('route' => $current->route()) );
					break;
				}
			}
			
			$tests_instances[] = $current;
			
		}
		
		return $tests_instances;
		
	}
	
	public function get_params()
	{
		$array = array();
		
		// Add the result and expected keys to the array
		foreach ($this->params() as $param => $value)
		{
			$array[$param]['result'] = $value;
		}
		
		foreach ($this->expected_params() as $param => $value)
		{
			$array[$param]['expected'] = $value;
		}
		
		// Not the prettiest code in the word (wtf arrays), but oh well
		foreach ($array as $item => $options)
		{
			// Assume they don't match.
			$array[$item]['error'] = TRUE;
			
			if ( ! isset($options['expected']))
			{
				$array[$item]['expected'] = '[none]';
			}
			else if ( ! isset($options['result']))
			{
				$array[$item]['result'] = '[none]';
			}
			else if ($options['result'] == $options['expected'])
			{
				$array[$item]['error'] = FALSE;
			}
		}
		
		return $array;
	}
	
	/**
	 * Get or set the url parameter for Route_Tester::create_tests()
	 * 
	 * @param  string  url to test
	 * @return mixed
	 */
	public function url($url = NULL)
	{
		if($url === NULL)
			return $this->_url;
			
		$this->_url = $url;
		return $this;
	}
	
	/**
	 * Get or set the route name parameter for Route_Tester::create_tests()
	 * 
	 * @param  string  the route this url matched
	 * @return mixed
	 */
	public function route($name = NULL)
	{
		if($name === NULL)
			return $this->_route;
			
		$this->_route = $name;
		return $this;
	}
	
	/**
	 * Get or set the returned params from route used in Route_Tester::create_tests()
	 * 
	 * @param  array  setted params will be auto-concated with exiting ones
	 * @return mixed
	 */
	public function params($params = NULL)
	{
		if( ! $params)
			return $this->_params;
			
		$this->_params = array_merge($this->_params, $params);
		return $this;
	}
	
	/**
	 * Get or set the returned params from route used in Route_Tester::create_tests()
	 * 
	 * @param  array  setted params will be auto-merged with exiting ones
	 * @return mixed
	 */
	public function expected_params($params = NULL)
	{
		if( ! $params)
			return $this->_expected_params;
			
		$this->_expected_params = $params;
		return $this;
	}
}

