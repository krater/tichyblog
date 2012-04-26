<?php
include "template/".config::$template."/entry.php";

function OutputContent($db,$in,$checkonline=1)
{
  if($in->IsOverview())
  {
    echo '<div id="mainContent">';

    if($in->GetYear())
      OutputPath(IntToMonth($in->GetMonth()).' '.$in->GetYear(),"");
    else
      OutputPath("","");

    if($checkonline)
      $filter=" online=1";
    else
      $filter=" 1=1";

    $es=sqlite_query($db,"SELECT id,name,unixtime,title,description,picture FROM blogentry WHERE".$filter.$in->GetTimeFilter()." ORDER BY unixtime DESC");
    while($e=sqlite_fetch_array($es))
    {
      templ_outentry('index.php?action=blog&entry='.$e['id'].'_'.$e['name'],config::$path.'images/',$e['picture'],$e['title'],$e['description'],$e['unixtime']);
    }

    echo '</div>';
  }
  else
  {
    $ent=new entry();
    $ent->ReadFromDB($db,$in);

    OutputEntry($db,$ent,1);    
  }
}


function OutputPath($first,$firstlink,$second="",$secondlink="")
{
  if(!empty($second))
    echo '<p class="path"><a href="index.php">'.config::$url.'</a> &raquo; <a href="'.$firstlink.'">'.$first.'</a> &raquo; <a href="'.$secondlink.'">'.$second.'</a></p>'."\n";
  else
    echo '<p class="path"><a href="index.php">'.config::$url.'</a> &raquo; <a href="'.$firstlink.'">'.$first.'</a></p>'."\n";
}

function OutputEntry($db,$ent,$checkonline)
{
  $e=$ent->GetData($checkonline);

  if($ent->IsBlog())
  {
    echo '<div id="mainContent">';

    OutputPath(IntToMonth($e['month']).' '.$e['year'],"index.php?action=overview&month=".$e['month']."&year=".$e['year'],$e['menutitle'],"");

    echo '<h1>'.$e['title'].'</h1>'.
         //'<div id="description">'.$e['description'].'</div>'.
         '<!-- CONTENT STARTS HERE -->'.$e['text'].'<!-- CONTENT ENDS HERE -->'.
         '</div>';
  }
  else if($ent->IsStatic())
  {
    echo '<div id="mainContent">';

    OutputPath($e['menutitle'],"");

    echo '<h1>'.$e['title'].'</h1>'.
         '<!-- CONTENT STARTS HERE -->'.$e['text'].'<!-- CONTENT ENDS HERE -->'.
         '</div>';
  }
}
