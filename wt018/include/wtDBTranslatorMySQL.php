<?php
/**
 * @file
 * MySQL database translator (most likely a dummy class)
 */

class wtDBTranslatorMySQL
{
	var $mCKey=NULL;
    var $mDB;
    
    /**
	 * Returns the number of rows affected by a SELECT operation
	 *
	 * @param
	 *		The query result
	 * @return
    *		An integer carrying the number of rows affected by the operation. 
	 */
    function count($res)
    {
    	return mysql_num_rows($res);    	
    }

    /**
	 * Queries the database
	 *
	 * @param
	 *		The untransformed query.
	 * @return
     *		The result of the transformed query.
	 */
	function query($q)
    {
    	// This is just a dummy function.
        if(!$this->mCKey)
        	return false;

        $this->mDB->mResult=mysql_query($q, $this->mCKey);
        $this->mDB->Query=$q;
    	return ($this->mDB->mResult?true:false);
    }
    
    function lastId($table)
    {
      $result=mysql_query("SELECT LAST_INSERT_ID() as `id` FROM `@P@".$table."`");
      $row=mysql_fetch_assoc($result);
      if($row)
        return $row['id'];
      return NULL;
    }

    /**
	 * Fetches the result of a database query
	 *
	 * @param
	 *		The query result
	 * @return
     *		A result row or NULL.
	 */
    function fetch($res)
    {
        if(!$this->mCKey)
        	return false;
        if(!$res)
        		return false;
    	return mysql_fetch_assoc($res);
    }

    /**
	 * Returns an error description if exists.
	 *
	 * @return
     *		The error string
	 */
    function error()
    {
    	if(!$this->mCKey)
        	return false;
    	return mysql_error($this->mCKey);
    }

    /**
	 * Connects to the database
	 *
     * @param host
     *		The hostname
     * @param user
     *		The username
     * @param password
     *		The password
     * @param dbname
     *		The database name
     *
	 * @return
     *		True on success, otherwise false
	 */
    function connect($host, $user, $password, $dbname)
    {
		$this->mCKey=mysql_connect($host, $user, $password, true);
        if(!$this->mCKey)
         	return false;
    	return (mysql_select_db($dbname, $this->mCKey)?true:false);

    }
}

?>