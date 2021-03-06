<?php
/**
 * @brief Database abstraction layer base class
 * 
 * This class is intended to act as an abstraction layer for various database drivers. 
 * It's a pure virtualization class and not recommended for direct use. To get a proper 
 * database class instance, it's better to utilize Watchtowers @ref dbmanagement "database management"
 * and use wtDBGetConnection().
 */

class wtDB
{
	   var $Query=""; // The last query
	   var $Name;
     var $Prefix=NULL;

    var $mDB=NULL; // The database translator
    var $mResult=NULL;


    /**
	 * Returns the connection status
	 *
	 * @return
	 *		TRUE if connected, otherwise FALSE
	 */
    function connected()
    {
    	return $this->mConnected;
    }

    /**
	 * Returns the number of rows affected by a SELECT operation
	 *
	 * @param
	 *		The query or NULL to use the last query
	 * @return
    *		An integer carrying the number of rows affected by the operation.
	 */
    function count($q=NULL)
    {
    	if($q!==NULL)
    	{
    		$q=func_get_args();
    		call_user_func_array(array(&$this, "q"), $q);
    	}
    	if($this->mResult)
    		return $this->mDB->count($this->mResult);
    	else
    		return FALSE;
    }

	/**
	 * Connect to the database.
	 *
	 * @param
	 *		The connection URL. (e.g. mysql://user:pass@whateverhost.com:2400/dbname#prefix)
	 * @return
	 */
	function connect($db_url)
	{
		global $WT;
		$this->mConnected=false;

		// Parse the Url.
		$url=wtUrlParse($db_url);

      $this->mDB=NULL;
		switch($url['protocol'])
        {
        	case "mysql": // MySQL protocol
            	if(!$url['port'])
                	$url['port']="3306";

            	 require_once($WT->CoreDir."include/wtDBTranslatorMySQL.php");
                $this->mDB=new wtDBTranslatorMySQL();
            	break;

            default: // Unknown database protocol
            	return false;
        }

      $this->mDB->mDB=&$this;
      $this->Prefix=$url['anchor'];
      $this->Name=substr($url['path'], 1);
   	$this->mConnected=$this->mDB->connect($url['host'].":".$url['port'], $url['user'], $url['password'], $this->Name);

   	wtCallHook("Core/OnDBConnect", $url, $this->mConnected);

   	return $this->mConnected;
	 }

    /**
	 * Queries the database
	 *
	 * @param
	 *		The query.
	 * @return
     *		The result of the query.
	 */
    function q()
    {
    	if($this->mDB==NULL)
        	return false;

      $q=func_get_args();
      $cq=count($q);
		for($i=1;$i<$cq;$i++)
		{
			$q[$i]=mysql_real_escape_string($q[$i]);
		}

      $q=call_user_func_array("sprintf",$q);
      $q=str_replace("@P@", $this->Prefix, $q);

    	return $this->mDB->query($q);
    }
    
    function lastId($table)
    {
      if($this->mDB==NULL)
        	return false;
      return $this->mDB->lastId($table);
    }

    /**
	 * Fetches the result of a database query
	 *
	 * @return
     *		A result row or NULL.
	 */
    function fetch()
    {
        if($this->mDB==NULL)
        	return false;
		return $this->mDB->fetch($this->mResult);
    }

    /**
	 * Fetches the all rows resulting of a database query
	 *
	 * @return
     *		An array containing the resulting rows.
	 */
    function fetchAll()
    {
    	$r=array();
        while($rr=$this->fetch())
        	$r[]=$rr;
        return $r;
    }

    /**
	 * Returns an error description if exists.
	 *
	 * @return
     *		The error string
	 */
    function error()
    {
    	if($this->mDB==NULL)
        	return false;
        return $this->mDB->error();
    }
}

?>