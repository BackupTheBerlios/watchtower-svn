<?php
/**
 * @file
 * Utility functions
 */

/**
  * Parses an URL.
  *
  * The URL may consist of: protocol, user,password, host, port, path, query and anchor.
  * Example: http://user:pass@yourhost.org:8800/path/to/index.php?querystring=here#anchor
  * Result: Array
  *			(
  *  				[protocol] => http
  *  				[user] => user
  *  				[password] => pass
  *  				[host] => yourhost.org
  *  				[port] => 8800
  *  				[path] => /path/to/index.php
  * 				[query] => querystring=here
  *  				[anchor] => anchor
  *			)
  *
  * @param $url
  *	The url to parse.
  * @param
  *	Optional. If a certain component is missing in the URL, a default value can be defined here.
  *	The input format equals the output of this function (an associative array with URL component keys).
  * @return
  *	An associative array containing the result.
  */
function wtUrlParse($url, $defaults=NULL)
{
	$regex="/^((\w+):\/\/)?((([\w|0-9|-|_]+):([\w|0-9|-|_]*))@)?([.|\w]*)(:([0-9]+))?(.*)/";
	preg_match($regex, $url, $result);

	$regex="/^([^#]*)(#(.*))?/";
	preg_match($regex, $result[10], $path);
	if(isset($path[2]))
	{
		$anchor=$path[3];
		$path=$path[1];
	}
	else
	{
		$path=$result[10];
		$anchor=NULL;
	}

	$regex="/^([^\?]*)(\?(.*))?/";
	preg_match($regex, $path, $path_r);
	if(isset($path_r[2]))
	{
		$query=$path_r[3];
		$path=$path_r[1];
	}
	else
	{
		$query=NULL;
	}
	$result=array(
		'protocol'=>strtolower($result[2]),
		'user'=>$result[5],
		'password'=>$result[6],
		'host'=>strtolower($result[7]),
		'port'=>$result[9],
		'path'=>$path,
		'query'=>$query,
		'anchor'=>$anchor
		);
		
	

	if($defaults!==NULL)
	{
		foreach($result as $key=>$value)
		{
			if(!$value && isset($defaults[$key]))
				$result[$key]=$defaults[$key];
		}
	}


	return $result;
}

/**
 *	Generate a hash. The algorithm defined in the configuration data will be used. (Normally SHA1)
 * 
 * @param $str
 *		A string to generate the hash from.
 *	@return
 *		The generated hash string.
 */ 
function wtHash($str)
{
	// TODO: At the moment only SHA1 is implemented here.
	return sha1($str);
}

?>