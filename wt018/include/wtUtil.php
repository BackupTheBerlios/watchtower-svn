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

function wtParseRequirement($reqStr)
{
	$regex="/^((.+):)?(.+)(>=|==|<=|>|<|!=)(.+)/";
	preg_match($regex, $reqStr, $result);
	if(!$result)
		return NULL;

	if(!$result[3] || !$result[4] || !$result[5])
    	return NULL;	
	
	return array(
		'type'=>$result[2],
		'first'=>$result[3],
		'comparison'=>$result[4],
		'second'=>$result[5]
		);
}


/**
 *	Checks wheter a requirement is available or not. Requirements are used for module dependencies, for example.
 *  A typical requirement string should look like this:
 *
 *  Module:SomeModule>=0.1
 *
 *	This would check if a module named SomeModule is installed in version 0.1 or later.
 *
 *
 * @param $str
 *		The requirement string.
 *	@return
 *		True if the requirement has been met or false if not.
 */
function wtCheckRequirement($reqStr)
{
	 $req=wtParseRequirement($reqStr);
	 if(!$req)
	 	return false;
    
    global $WT;
    switch($req['type'])
    {
    	case "Module":
    	    $db=wtDBGetConnection("Core");
    	    $r=$WT->ModuleManager->checkModule($req['first'], $req['second']);    	    
          if($req['type']=="==" || $req['type']=="<=" || $req['type']==">=" && $r!==TRUE)
            return false;
          if($req['type']=="!=" && $r===TRUE)
            return false;
          if($req['type']==">" && ($r===TRUE || $r<=0))
            return false;  
          if($req['type']=="<" && ($r===TRUE || $r>=0))
            return false; 
          return true;                                   	 
        	break;
		  case "":
        	if($req['first']=="WTCore")
            	return(@eval("return \$WT->Version".$req['comparison']."\$req['second'];"));
        	break;
    }
    return false;
}

/**
 * Converts a dirty path into a clean path
 * 
 * @param $dir
 *    The dirty directory path
 * @param $trailing_slashes
 *    If NULL, trailing slashes will be used as in the dirty directory path,
 *    if TRUE, trailing slahes will be forced,
 *    if FALSE, trailing slashes will be cut.  
 * @return 
 *    A clean compatible directory path   
 */ 
function wtDir($dir, $trailing_slashes=NULL, $leading_slashes=NULL)
{ 
  $d="/";
  if($dir=="")
  {
    if($trailing_slashes!==FALSE && $leading_slashes!==FALSE)
      return $d;
    else
      return "";
  }

  $buf="";
  $len=strlen($dir);
  $parts=array();
  
  for($i=0;$i<$len;$i++)
  {
    if($dir[$i]=="\\" || $dir[$i]=="/")
    {
      if($buf!="")
      {
        if($buf==".." && count($parts)>0)
          array_pop($parts);
        else
          $parts[]=$buf;
      }
      $buf="";
    }
    else
      $buf.=$dir[$i];
  }
  
  $out="";
  if($leading_slashes!==FALSE && ($dir[0]=="\\" || $dir[0]=="/"))
  {
    $out=$d;
  }   
     
  if(count($parts)>0)
    $out.=implode($d, $parts);
    
  if($dir[$len-1]=="\\" || $dir[$len-1]=="/")
  {
    if($trailing_slashes!==FALSE)
      $out.=$d;
  }
  else if($buf)
  {
    if($out)
      $out.=$d;
    $out.=$buf;
    if($trailing_slashes===TRUE)
      $out.=$d;
  }
     
  global $WT;
  if(isset($WT->CoreDir))
  {
    $len=strlen($WT->CoreDir);
    if($len>0)
    {
      if(substr($out, 0, $len)==$WT->CoreDir)
      {
        $out=substr($out, $len);
      }
    }
  }  
  
  if($leading_slashes===TRUE && $out[0]!="/")
  {
    $out="/".$out;
  }
  else if($leading_slashes==FALSE && $out[0]=="/")
  {
    $out=substr($out, 1);
  }
    
  return $out;
}

?>