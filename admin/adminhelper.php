<?php

class adminvars
{
  var $adminactions=array(""=>0,"delete"=>1,"edit"=>2,"confirm"=>3,"add"=>5,"save"=>6,"statistic"=>7);

  function parse()
  {
    if(!empty($_GET['adminaction']))
      $this->adminaction=$this->adminactions[$_GET['adminaction']];
    else
      $this->adminaction=0;

    if(!empty($_GET['starttime']))
    	$this->starttime=((int)$_GET['starttime']);
    else
    	$this->starttime=0;

    if(!empty($_GET['endtime']))
    	$this->endtime=((int)$_GET['endtime']);
    else
    	$this->endtime=0xffffffff;
  }

  function IsAdminAction()  {return ($this->adminaction>0);}
  function GetAction()      {return $this->adminaction;}
  function GetStart()				{return $this->starttime;}
  function GetEnd()					{return $this->endtime;}

  private $adminaction;
  private $starttime,$endtime;
}

?>