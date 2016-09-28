<h1>Config Dump</h1>

<?php

foreach ($configs as $path => $name)
{
	echo "<h3>$path</h3>";
	
	try
	{
		echo Kohana_Debug::vars(Kohana::config($name));
	}
	catch (Exception $e)
	{
		echo "Something went terribly wrong. This is usually caused by
		      undefined constants because of missing dependancies. Error
			  message: " . $e->getMessage();
	}
}