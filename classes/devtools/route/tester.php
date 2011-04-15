<?php defined('SYSPATH') or die ('No direct script access.');
/**
 * Kohana 3 Route tester class.  Use it by calling [Route_Tester::test]
 *
 * @package	   Bluehawk/Devtools
 * @author     Michael Peters
 */
class Devtools_Route_Tester {
	
	// The url for this test
	public $url;
	
	// The route this url matched
	public $route = FALSE;
	
	// The params the route returned
	public $params;
	
	// The optional expected params from the config
	public $expected_params = FALSE;

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
		
		$array = array();
		
		// Get the url and optional expected_params from the config
		foreach ($tests as $key => $value)
		{
			$current = new Route_Tester();
			
			if (is_array($value))
			{
				$current->url = $key;
				$current->expected_params = $value;
			}
			else
			{
				$current->url = $value;
			}
		
			// Test each route, and save the route and params if it matches
			foreach (Route::all() as $route)
			{
				if ($current->params = $route->matches($current->url))
				{
					$current->route = Route::name($route);
					$current->params = array_merge(array('route'=>$current->route),$current->params);
					break;
				}
			}
			
			$array[] = $current;
			
		}
		
		return $array;
		
	}
	
	public function get_params()
	{
		$array = array();
		
		// Add the result and expected keys to the array
		foreach ($this->params as $param => $value)
		{
			$array[$param]['result'] = $value;
		}
		
		foreach ($this->expected_params as $param => $value)
		{
			$array[$param]['expected'] = $value;
		}
		
		// Not the prettiest code in the word (wtf arrays), but oh well
		foreach ($array as $item => $options)
		{
			// Assume they don't match.
			$array[$item]['error'] = true;
			
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
				$array[$item]['error'] = false;
			}
		}
		
		return $array;
	}

}

