<?php
/**
 * @file
 * The module manager class
 */
class wtModuleManager
{
	var $mDB;
	var $mActiveModules=array();
	var $mModules=array();
	var $mInvokedModule=NULL;
	var $mInvokedModuleAction=NULL;
		
	function wtModuleManager()
	{
		$this->mDB=&wtDBGetConnection("Core");
	}
	
	/** 
   * Checks if the module belongs to the core. Can be used before read().
   *    
	 * @param $id
	 *   The ID of the module	 
	 * @return
	 *   TRUE if the module belongs to the core, otherwise FALSE.
	 */        	 	
	function isCoreModule($id)
	{
	   global $WT;
	   return is_dir($WT->CoreDir."/modules/".$id);
  }
  
  /**
   * Checks if the module is installed. This function can be used before read().
   *    
	 * @param $id
	 *   The ID of the module	 
	 * @param $version
	 *   Optional. A version number may be passed that the module needs to fit (equal or higher).
	 * @param $installed
	 *   If TRUE, only modules having the installed flag set will be checked.          	 
	 * @return
	 *   TRUE if the module is installed and the version equals $version (if passed),
	 *   FALSE if the module is not installed,
	 *   a positive value if the module is installed in a higher version than expected,
	 *   a negative value if the module is installed in a lower version than expected .        	 
	 */ 
  function checkModule($id, $version=NULL, $installed=false)
  {
    $this->mDB->q("SELECT `version` FROM `@P@modules` WHERE `mid`='%s'".($installed?" AND `installed`=1":""), $id);
    $mod=$this->mDB->fetch();
    if(!$mod)        
      return FALSE;
    if($version===NULL)
      return TRUE;
    if($version==$mod['version'])
      return TRUE;
    if($version<$mod['version'])
      return 1;
    if($version>$mod['version'])
      return -1;
  }
		
	function read($onlyActive=true)
	{	
    if($onlyActive)
	   $this->mDB->q("SELECT * FROM `@P@modules` WHERE `active`='1' ORDER BY `loadweight` ASC");
	  else
	   $this->mDB->q("SELECT * FROM `@P@modules`");
	  
	  $result=0;
	  
	  global $WT;
		  	  
	  while($mod=$this->mDB->fetch())
	  {
	     if(!$this->isCoreModule($mod['mid']))
	     {
	       $dir=wtDir($WT->ModuleDir.$mod['mid']."/");
	       $url=wtDir($WT->ModuleUrl.$mod['mid']."/");
	     }
	     else
	     {
	       $dir=wtDir($WT->CoreDir."modules/".$mod['mid']."/");
	       $url=wtDir($WT->CoreUrl."modules/".$mod['mid']."/");
	     }
	       
	      include($dir."Module.php");
	    
	     eval("\$this->mModules['".$mod['mid']."']=new ".$mod['mid'].";");  
	     
       if($mod['active'])
       {
        $result++;
        $this->mModules[$mod['mid']]->Dir=$dir;
        $this->mModules[$mod['mid']]->Url=$url;
        $this->mModules[$mod['mid']]->Id=$mod['mid'];
        $this->mActiveModules[$mod['mid']]=&$this->mModules[$mod['mid']];
       }
       
       $this->mModules[$mod['mid']]->Installed=$mod['installed'];   	   
    }
    
    return $result;
	}
		
	function getModuleHandle($mid)
	{
	   if(!isset($this->mModules[$mid]))
	     return null;
	   else
	     return $this->mModules[$mid];
  }
  
  function postInit()
  {
    foreach($this->mActiveModules as $m)
    {
      if(!$m->Installed) // Install new modules and set the install flag
      {
        $this->mDB->q("UPDATE `@P@modules` SET `installed`=1 WHERE `mid`='%s'", $m->Id);
        if(file_exists($m->Dir."ModuleInstall.php"))
        {
          $m->_install($m->Dir."ModuleInstall.php");          
        }
        $m->Installed=true;
        wtCallHook("Core/OnModuleInstall", &$m);
      }
    }
    
    foreach($this->mActiveModules as $m)
    {
      $m->onPostInit();
    }
  }
  
  function preInit()
  {
    foreach($this->mActiveModules as $m)
    {
     $m->onPreInit();
    }
  }
  
  function call($mid)
  {
    if(isset($this->mModules[$mid]))
      $this->mModules[$mid]->onCall();    
  }
  
  function invoke($mid, $action)
  {
    $this->mInvokedModule=&$this->getModuleHandle($mid);
    $this->mInvokedModuleAction=$action; 
  }
  
  function callInvoked()
  {
    if($this->mInvokedModule!=NULL)
    {
      return $this->mInvokedModule->onCall($this->mInvokedModuleAction);
    }
    return false;
  }

}

?>