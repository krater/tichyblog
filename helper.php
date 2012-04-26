<?php

function IntToMonth($i)
{
  $month=array("","January","February","March","April","May","June","July","August","September","October","November","December");
  return $month[$i];
}

class inputvars
{
  var $actions=array("overview"=>0,"blog"=>1,"static"=>2);

  function parse()
  {
    if(!empty($_GET['action']))
      $this->action=$this->actions[$_GET['action']];
    else
      $this->action=0;

    $this->entry=((int)$_GET['entry']);
    $this->month=((int)$_GET['month']);
    $this->year=((int)$_GET['year']);
    $this->rand=((int)$_GET['rand']);
  }

  function IsActive($action,$entry,$month=0,$year=0)
  {
    if($action==0)
    {
      if(($this->year==$year)&&($this->month==$month))
        return 1;
      else
        return 0;
    }

    if(($this->action==$action)&&($this->entry==$entry))
      return 1;
    else
      return 0;
  }

  function IsHome()
  {
    if(($this->action==0)&&($this->entry==0)&&($this->year==0))
      return 1;
    else
      return 0;
  }


  function GetTimeFilter()
  {
    if($this->year)
    {
      if($this->month)
        return " AND year=".$this->year." AND month=".$this->month;
      else
        return " AND year=".$this->year;
    }
    else
      return "";
  }

  function IsBlog()     {return ($this->action==1)?1:0;}
  function IsStatic()   {return ($this->action==2)?1:0;}
  function IsOverview() {return ($this->action==0)?1:0;}

  function GetYear()    {return $this->year;}
  function GetMonth()   {return $this->month;}
  function GetEntry()   {return $this->entry;}

  function GetRand()    {return $this->rand;}

  var $action=0;
  var $entry=0;
  var $month=0;
  var $year=0;

  var $rand;
}




define("e_unknownentry","0");
define("e_blogentry","1");
define("e_staticentry","2");

class entry
{
  /*****************************************************************************
  ****                          Common Functions                            ****
  *****************************************************************************/

  function __construct($type=e_unknownentry)
  {
    $this->type=$type;
    $this->newentry=1;
  }

  function ReadFromDB($db,$in)
  {
    $this->newentry=1;

    if($in->IsBlog())
      $this->ReadFromBlogDB($db,$in);
    else if($in->IsStatic())
      $this->ReadFromStaticDB($db,$in);

    return 0;
  }

  function UpdateFromPostVars()
  {
    if($this->IsBlog())
      $this->UpdateBlogFromPostVars();
    else if($this->IsStatic())
      $this->UpdateStaticFromPostVars();

    $this->name=filterstring($_POST['name']);
    $this->online=($_POST['online']=="on")?1:0;
    $this->title=filterstring($_POST['title']);
    $this->menutitle=filterstring($_POST['menutitle']);
    $this->com_enabled=($_POST['comen']=="on")?1:0;
    $this->text=filterstring($_POST['text']);
  }

  function WriteToDB($db)
  {
    if($this->IsBlog())
      $this->WriteBlogEntryToDB($db);
    else if($this->IsStatic())
      $this->WriteStaticEntryToDB($db);

    return 0;
  }

  function DeleteFromDB($db)
  {
    if($this->IsBlog())
      sqlite_query($db,"DELETE FROM blogentry WHERE id=".((int)$this->id));
    else if($this->IsStatic())
      sqlite_query($db,"DELETE FROM staticentry WHERE id=".((int)$this->id));
  }


  function GetData($checkonline=1)
  {
    if(($checkonline==1)&&($this->online==0))
      return 0;

    if($this->IsBlog())
    {
      $data=$this->GetBlogData();
    }
    else if($this->IsStatic())
    {
      $data=$this->GetStaticData();
    }

    $data['id']=$this->id;
    $data['name']=$this->name;
    $data['online']=$this->online;
    $data['title']=$this->title;
    $data['menutitle']=$this->menutitle;
    $data['comen']=$this->com_enabled;
    $data['text']=$this->text;

    return $data;
  }


  function IsNew()      {return ($this->newentry==1)?1:0;}
  function GetID()      {return $this->id;}
  function GetName()    {return $this->name;}
  function GetTitle()    {return $this->title;}

  function IsUnknown()  {return (($this->type!=e_blogentry)&&($this->type!=e_staticentry))?1:0;}


  /*****************************************************************************
  ****                           Blog Functions                             ****
  *****************************************************************************/

  function ReadFromBlogDB($db,$in)
  {
    $this->newentry=1;
    $this->type=e_blogentry;

    $ds=sqlite_query($db,"SELECT name,unixtime,month,year,online,title,menutitle,picture,comen,text,description,short FROM blogentry WHERE id=".((int)$in->GetEntry()));
    $d=sqlite_fetch_array($ds);
    if($d)
    {
      $this->id=$in->GetEntry();
      $this->name=$d['name'];
      $this->unixtime=$d['unixtime'];
      $this->month=$d['month'];
      $this->year=$d['year'];
      $this->online=$d['online'];
      $this->title=$d['title'];
      $this->menutitle=$d['menutitle'];
      $this->picture=$d['picture'];
      $this->com_enabled=$d['comen'];
      $this->text=$d['text'];
      $this->description=$d['description'];
      $this->isshort=$d['short'];

      $this->newentry=0;

      return 1;
    }
    else
      return 0;
  }

  private function UpdateBlogFromPostVars()
  {
    $this->SetTime((int)$_POST['date']);
    $this->description=filterstring($_POST['description']);
    $this->isshort=($_POST['short']=="on")?1:0;

    if($_FILES['picture']['error']===UPLOAD_ERR_OK)
    {
      if(in_array(end(explode(".",strtolower($_FILES['picture']['name']))),config::$picuploads))
      {
        $name=basename($_FILES['picture']['name']);

        $i=0;
        while(file_exists(config::$serverpath.'images/'.$name))
          $name=($i++).basename($_FILES['picture']['name']);

        if(move_uploaded_file($_FILES['picture']['tmp_name'],config::$serverpath.'images/'.$name))
        {
          $size=getimagesize(config::$serverpath.'images/'.$name);
          if($size[0]>100)
          {
            $x=100;
            $y=$size[1]/$size[0]*100;
            $src=imagecreatefromjpeg(config::$serverpath.'images/'.$name);
            $dst=imagecreatetruecolor($x,$y);
            imagecopyresampled($dst,$src,0,0,0,0,$x,$y,$size[0],$size[1]);
            imagepng($dst,config::$serverpath.'images/'.$name,0);           //maybee we have another extension than png, but this should work
            imagedestroy($src);
            imagedestroy($dst);
          }

          $this->picture=$name;
        }
        else
          $this->picture="";
      }
    }


  }

  function WriteBlogEntryToDB($db)
  {
    if($this->IsNew())
    {
      $this->SetTime(time());

      sqlite_query($db,"INSERT INTO blogentry (name,unixtime,month,year,online,title,menutitle,picture,comen,text,description,short) VALUES".
                        "('".sqlite_escape_string($this->name)."',".((int)$this->unixtime).",".((int)$this->month).",".((int)$this->year).",".
                        ((int)$this->online).",'".sqlite_escape_string($this->title)."','".sqlite_escape_string($this->menutitle)."','".
                        sqlite_escape_string($this->picture)."',".((int)$this->com_enabled).",'".sqlite_escape_string($this->text)."','".
                        sqlite_escape_string($this->description)."',".((int)$this->isshort).");");

      $this->id=sqlite_last_insert_rowid($db);
      $this->newentry=0;
    }
    else
    {
      sqlite_query($db,"UPDATE blogentry SET name='".sqlite_escape_string($this->name)."',unixtime=".((int)$this->unixtime).",month=".((int)$this->month).
                        ",year=".((int)$this->year).",online=".((int)$this->online).",title='".sqlite_escape_string($this->title)."',menutitle='".
                        sqlite_escape_string($this->menutitle)."',picture='".sqlite_escape_string($this->picture)."',comen=".((int)$this->com_enabled).
                        ",text='".sqlite_escape_string($this->text)."',description='".sqlite_escape_string($this->description)."',short=".((int)$this->isshort).
                          " WHERE id=".((int)$this->id));
    }
  }

  function SetBlogData($name,$unixtime,$online,$title,$menutitle,$picture,$com_enabled,$text,$description,$isshort)
  {
    $this->name=$name;
    $this->SetTime($unixtime);
    $this->online=$online;
    $this->title=$title;
    $this->menutitle=$menutitle;;
    $this->picture=$picture;
    $this->com_enabled=$com_enabled;
    $this->text=$text;
    $this->description=$description;
    $this->isshort=$isshort;
    $this->newentry=1;
  }

  private function GetBlogData()
  {
    $data['unixtime']=$this->unixtime;
    $data['picture']=$this->picture;
    $data['description']=$this->description;
    $data['short']=$this->isshort;
    $data['year']=$this->year;
    $data['month']=$this->month;

    return $data;
  }

  function SetTime($unixtime)
  {
    $this->unixtime=$unixtime;
    $this->month=date('m',$unixtime);
    $this->year=date('Y',$unixtime);
  }


  function IsBlog()     {return ($this->type==e_blogentry)?1:0;}




  /*****************************************************************************
  ****                          Static Functions                            ****
  *****************************************************************************/

  function ReadFromStaticDB($db,$in)
  {
    $this->newentry=1;
    $this->type=e_staticentry;

    $ds=sqlite_query($db,"SELECT name,online,title,menutitle,comen,text,pos FROM staticentry WHERE id=".((int)$in->GetEntry()));
    $d=sqlite_fetch_array($ds);
    if($d)
    {
      $this->id=$in->GetEntry();
      $this->name=$d['name'];
      $this->online=$d['online'];
      $this->title=$d['title'];
      $this->menutitle=$d['menutitle'];
      $this->com_enabled=$d['comen'];
      $this->text=$d['text'];
      $this->pos=$d['pos'];
      $this->newentry=0;

      return 1;
    }
    else
      return 0;
  }

  private function UpdateStaticFromPostVars()
  {
    $this->pos=((int)$_POST['pos']);
  }

  function WriteStaticEntryToDB($db)
  {
    if($this->IsNew())
    {
      sqlite_query($db,"INSERT INTO staticentry (name,online,title,menutitle,comen,text,pos) VALUES".
                        "('".sqlite_escape_string($this->name)."',".((int)$this->online).",'".sqlite_escape_string($this->title)."','".
                        sqlite_escape_string($this->menutitle)."',".((int)$this->com_enabled).",'".sqlite_escape_string($this->text)."',".
                        ((int)$this->pos).");");

      $this->id=sqlite_last_insert_rowid($db);
      $this->newentry=0;
    }
    else
    {
      sqlite_query($db,"UPDATE staticentry SET name='".sqlite_escape_string($this->name)."',online=".((int)$this->online).",title='".
                        sqlite_escape_string($this->title)."',menutitle='".sqlite_escape_string($this->menutitle).
                        "',comen=".((int)$this->com_enabled).",text='".sqlite_escape_string($this->text)."',pos=".
                        ((int)$this->pos)." WHERE id=".((int)$this->id));
    }
  }

  function SetStaticData($name,$online,$title,$menutitle,$com_enabled,$text,$pos)
  {
    $this->name=$name;
    $this->online=$online;
    $this->title=$title;
    $this->menutitle=$menutitle;
    $this->com_enabled=$com_enabled;
    $this->text=$text;
    $this->pos=$pos;

    $this->newentry=1;
  }

  private function GetStaticData()
  {
    $data['pos']=$this->pos;

    return $data;
  }


  function IsStatic()   {return ($this->type==e_staticentry)?1:0;}



  /*****************************************************************************
  ****                             Variables                                ****
  *****************************************************************************/

  //common
  private $type; //blogentry or staticentry

  private $id;
  private $name;
  private $online;
  private $title;
  private $menutitle;
  private $com_enabled;
  private $text;
  private $newentry;

  //blog
  private $unixtime;
  private $month;
  private $year;
  private $pic;
  private $description;
  private $isshort;

  //static
  private $pos;
}

function filterstring($text)
{
  $text=str_replace("\\'","'",$text);
  $text=str_replace("\\\"","\"",$text);
  $text=str_replace("\\\\","\\",$text);          //"

  return $text;
}

?>
