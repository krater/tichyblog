<?php

function OutputPublicMenu($db,$in)
{
  echo '<div id="menu">'.
        //'<h3>Menu Content</h3>'.
        '<ul class="menuitem">';

  OutputBlogMenu($db,$in,0);
  OutputStaticMenu($db,$in,0);

  echo "</ul></div>\n";
}

function OutputAdminMenu($db,$in)
{
  echo '<div id="menu">'.
        //'<h3>Menu Content</h3>'.
        '<ul class="menuitem">'.
        '<li class="addblogentry"><a class="online" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=add">Add Blog Entry</a></li>'.
        '<li class="addstaticentry"><a class="online" href="index.php?action=static&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=add">Add Static Entry</a></li>'.
        '<li class="statistics"><a class="online" href="index.php?adminaction=statistic">Show Statistics</a></li>';
  OutputBlogMenu($db,$in,1);
  OutputStaticMenu($db,$in,1);

  echo "</ul></div>\n";
}


function OutputStaticMenu($db,$in,$adminmode=0)
{
  if($adminmode)
    $filter="";
  else
    $filter=" WHERE online=1";

  $statics=sqlite_query($db,"SELECT id,name,menutitle,online FROM staticentry".$filter." ORDER BY pos ASC");
  while($entry=sqlite_fetch_array($statics))
  {
    if($in->IsActive(2,$entry['id']))
      $cla="active";
    else
      $cla="";

    if($entry['online']==1)
      $clo="online";
    else
      $clo="offline";

    if($adminmode)
    {
      $admin='<a class="'.$clo.'" id="delbtn" href="index.php?action=static&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=delete">D</a> '.
              '<a class="'.$clo.'" id="editbtn" href="index.php?action=static&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=edit">E</a> ';
    }

    echo '<li class="'.$cla.'">'.$admin.'<a class="'.$clo.'" href="index.php?action=static&entry='.$entry['id'].'_'.urlEncode($entry['name']).'">'.$entry['menutitle']."</a></li>\n";
  }
}

function OutputBlogMenu($db,$in,$adminmode=0)
{
  if($adminmode)
    $filter="";
  else
    $filter=" online=1 AND";

  if($adminmode)
  {
    if($in->IsHome())
      echo '<li class="active"><a id="homebtn" class="online" href="'.config::$path.'admin/">Home</a><a id="rssbtn" href="'.config::$path.'rss.php" target="_blank"><img id="rsspic" src="../template/'.config::$template.'/rss.png"></a></li>'."\n";
    else
      echo '<li><a id="homebtn" class="online" href="'.config::$path.'admin/">Home</a><a id="rssbtn" href="'.config::$path.'rss.php" target="_blank"><img id="rsspic" src="../template/'.config::$template.'/rss.png"></a></li>'."\n";
  }
  else
  {
    if($in->IsHome())
      echo '<li class="active"><a id="homebtn" class="online" href="'.config::$path.'">Home</a><a id="rssbtn" href="rss.php" target="_blank"><img id="rsspic" src="template/'.config::$template.'/rss.png"></a></li>'."\n";
    else
      echo '<li><a id="homebtn" class="online" href="'.config::$path.'">Home</a><a id="rssbtn" href="rss.php" target="_blank"><img id="rsspic" src="template/'.config::$template.'/rss.png"></a></li>'."\n";
  }

  $years=sqlite_query($db,"SELECT year FROM blogentry WHERE".$filter." 1=1 GROUP BY year ORDER BY year DESC");
  $year=sqlite_fetch_array($years);

  //output last year splitted in months
  $times=sqlite_query($db,"SELECT month,year,count(*) FROM blogentry WHERE".$filter." 1=1 AND year=".$year[0]." GROUP BY month,year ORDER BY unixtime DESC");
  while($time=sqlite_fetch_array($times))
  {
    if($in->IsActive(0,0,$time['month'],$time['year']))
      echo '<li class="active"><a class="online" href="index.php?action=overview&month='.$time['month'].'&year='.$time['year'].'">'.IntToMonth($time['month']).' '.$time['year'].'</a></li>'."\n";
    else
      echo '<li><a class="online" href="index.php?action=overview&month='.$time['month'].'&year='.$time['year'].'">'.IntToMonth($time['month']).' '.$time['year'].'</a></li>'."\n";

    $entries=sqlite_query($db,"SELECT id,name,menutitle,online FROM blogentry WHERE".$filter." month=".((int)$time['month'])." AND year=".((int)$time['year'])." ORDER BY unixtime DESC");
    while($entry=sqlite_fetch_array($entries))
    {
      if($in->IsActive(1,$entry['id']))
        $cli="active";
      else
        $cli="";

      if($entry['online']==0)
        $clo="offline";
      else
        $clo="online";

      if($adminmode)
      {
        $admin='<a class="'.$clo.'" id="delbtn" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=delete">D</a> '.
                '<a class="'.$clo.'" id="editbtn" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=edit">E</a> ';
      }

      echo '<li class="submenuitem '.$cli.'">'.$admin.'<a class="'.$clo.'" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'">'.$entry['menutitle']."</a></li>\n";
     }
  }

  //output other years
  $times=sqlite_query($db,"SELECT year,count(*) FROM blogentry WHERE".$filter." 1=1 AND year!=".$year[0]." GROUP BY year ORDER BY unixtime DESC");
  while($time=sqlite_fetch_array($times))
  {
    if($in->IsActive(0,0,0,$time['year']))
      echo '<li class="active"><a class="online" href="index.php?action=overview&year='.$time['year'].'">'.$time['year'].'</a></li>'."\n";
    else
      echo '<li><a class="online" href="index.php?action=overview&year='.$time['year'].'">'.$time['year'].'</a></li>'."\n";

    $entries=sqlite_query($db,"SELECT id,name,menutitle,online FROM blogentry WHERE".$filter." year=".((int)$time['year'])." ORDER BY unixtime DESC");
    while($entry=sqlite_fetch_array($entries))
    {
      if($in->IsActive(1,$entry['id']))
        $cli="active";
      else
        $cli="";

      if($entry['online']==0)
        $clo="offline";
      else
        $clo="online";

      if($adminmode)
      {
        $admin='<a class="'.$clo.'" id="delbtn" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=delete">D</a> '.
                '<a class="'.$clo.'" id="editbtn" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'&adminaction=edit">E</a> ';
      }

      echo '<li class="submenuitem '.$cli.'">'.$admin.'<a class="'.$clo.'" href="index.php?action=blog&entry='.$entry['id'].'_'.urlEncode($entry['name']).'">'.$entry['menutitle']."</a></li>\n";
     }

  }

}




?>
