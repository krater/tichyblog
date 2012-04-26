<?php

function OutputAdminContent($db,$in,$adm)
{
  $ent=new entry();
  $ent->ReadFromDB($db,$in);

  if($adm->GetAction()==7)    //is statistic ?
  {
    OutputStatistic($adm);
    return;
  }

  if($ent->IsUnknown())
    OutputContent($db,$in,0);
  else
  {
    switch($adm->GetAction())
    {
      case 0://normal display
        OutputEntry($db,$ent,0);
        break;
      case 1://delete
        if($ent->IsBlog())
          OutputDeleteQuestion("?action=blog&entry=".($ent->GetID())."&adminaction=confirm",$ent->GetName());
        else if($ent->IsStatic())
          OutputDeleteQuestion("?action=static&entry=".($ent->GetID())."&adminaction=confirm",$ent->GetName());
        break;
      case 2://edit
        OutputEditor($db,$ent);
        break;
      case 3;//confirm
        if($_SESSION['rand']==$in->GetRand())
        {
          $ent->DeleteFromDB($db);
          OutputDeleteConfirmation();
        }
        while(!($_SESSION['rand']=rand()));
        break;
      case 4://decline
        break;
      case 5://add
        $ent->UpdateFromPostVars();
        OutputEditor($db,$ent);
        break;
      case 6://save
        $ent->UpdateFromPostVars();
        OutputEditor($db,$ent);
        $ent->WriteToDB($db);
        break;
    }
  }
}

function OutputDeleteQuestion($link,$title)
{
  while(!($_SESSION['rand']=rand()));

  echo '<div id="mainContent"><h1>Delete</h1><p>Do you really want to delete "'.$title.'" ?'.
        '<form action="'.$link.'&rand='.$_SESSION['rand'].'" method="post"><input id="ok" type=submit value="Ok"><input type="hidden" name="rand" value="'.$_SESSION['rand'].'"></form> '.
        '<form action="javascript:history.back()"><input id="cancel" type=submit value="Cancel"></form>'.
        '</p></div>';
}

function OutputDeleteConfirmation()
{
  echo '<div id="mainContent"><h1>Delete</h1><p>Entry deleted.</p></div>';
}

function OutputEditor($db,$entry)
{
  if($entry->IsBlog())
    OutputBlogEditor($db,$entry);
  else if($entry->IsStatic())
    OutputStaticEditor($db,$entry);
}

function OutputBlogEditor($db,$ent)
{
  include "../template/".config::$template."/editor.html";

  if($ent->IsNew())
  {
    echo '<div id="mainContent"><h1>New Blog Entry</h1><hr>'.
          '<p><form method="post" enctype="multipart/form-data" action="?action=blog&adminaction=save"><table>'.
          '<tr><th>Date</th><td><input type="text" name="date" size="20" value="'.time().'"></td>'.
            '<td rowspan="6"></td>'.
          '</tr>'.
          '<tr><th>Name</th><td><input type="text" name="name" size="50"></td></tr>'.
          '<tr><th>Menu Title</th><td><input type="text" name="menutitle" size="50"></td></tr>'.
          '<tr><th>Title</th><td><input type="text" name="title" size="50"></td></tr>'.
          '<tr><th>Visible</th><td><input type="checkbox" name="online"></td></tr>'.
          '<tr><th>Comments Enabled</th><td><input type="checkbox" name="comen"></td></tr>'.
          '<tr><th>Short News</th><td><input type="checkbox" name="short"></td>'.
            '<td>Choose a image to upload: <input name="picture" type="file" size="35"></td>'.
          '</tr>'.
          '</table>'.

          '<p>Description<br>'.
          '<textarea name="description" style="width:100%"></textarea></p>'.

          '<p>Text<br>'.
          '<textarea rows="40" style="width:100%" class="mceedit" name="text"></textarea></p>'.

          '<br><input type="submit" value="Save"></form></p>'.
          '</div>';
  }
  else
  {
    $data=$ent->GetData(0);

    echo '<div id="mainContent"><h1>Edit '.$data['name'].'</h1><hr>'.
          '<p><form method="post"  enctype="multipart/form-data" action="?action=blog&entry='.$data['id'].'&adminaction=save"><table>'.
          '<tr><th>Date</th><td><input type="text" name="date" value="'.$data['unixtime'].'" size="20"></td>'.
            '<td rowspan="6" valign="center" align="center"><img src="'.config::$path.'images/'.$data['picture'].'" width="100"></td>'.
          '</tr>'.
          '<tr><th>Name</th><td><input type="text" name="name" value="'.$data['name'].'" size="50"></td></tr>'.
          '<tr><th>Menu Title</th><td><input type="text" name="menutitle" value="'.$data['menutitle'].'" size="50"></td></tr>'.
          '<tr><th>Title</th><td><input type="text" name="title" value="'.$data['title'].'" size="50"></td></tr>'.
          '<tr><th>Visible</th><td><input type="checkbox" name="online"'.(($data['online'])?" checked":"").'></td></tr>'.
          '<tr><th>Comments Enabled</th><td><input type="checkbox" name="comen"'.(($data['comen'])?" checked":"").'></td></tr>'.
          '<tr><th>Short News</th><td><input type="checkbox" name="short"'.(($data['short'])?" checked":"").'></td></td>'.
            '<td>Choose another image to upload: <input name="picture" type="file" size="35"></td>'.
          '</tr>'.
          '</table>'.

          '<p>Description<br>'.
          '<textarea name="description" style="width:100%">'.$data['description'].'</textarea></p>'.

          '<p>Text<br>'.
          '<textarea rows="40" style="width:100%" class="mceedit" name="text">'.$data['text'].
          '</textarea></p>'.

          '<br><input type="submit" value="Save"></form></p>'.
          '</div>';
  }
}

function OutputStaticEditor($db,$ent)
{
  include "../template/".config::$template."/editor.html";

  if($ent->IsNew())
  {
    echo '<div id="mainContent"><h1>New Blog Entry</h1><hr>'.
          '<p><form method="post" action="?action=static&adminaction=save"><table>'.
          '<tr><th>Position</th><td><input type="text" name="pos" size="2"></td></tr>'.
          '<tr><th>Name</th><td><input type="text" name="name" value="'.$data['name'].'" size="50"></td></tr>'.
          '<tr><th>Menu Title</th><td><input type="text" name="menutitle" value="'.$data['menutitle'].'" size="50"></td></tr>'.
          '<tr><th>Title</th><td><input type="text" name="title" value="'.$data['title'].'" size="50"></td></tr>'.
          '<tr><th>Visible</th><td><input type="checkbox" name="online"></td></tr>'.
          '<tr><th>Comments Enabled</th><td><input type="checkbox" name="comen"></td></tr>'.
          '</table>'.

          '<p>Text<br>'.
          '<textarea rows="40" style="width:100%" class="mceedit" name="text">'.$data['text'].
          '</textarea></p>'.

          '<br><input type="submit" value="Save"></form></p>'.
          '</div>';
  }
  else
  {
    $data=$ent->GetData(0);

    echo '<div id="mainContent"><h1>Edit '.$data['name'].'</h1><hr>'.
          '<p><form method="post" action="?action=static&entry='.$data['id'].'&adminaction=save"><table>'.
          '<tr><th>Position</th><td><input type="text" name="pos" value="'.$data['pos'].'" size="2"></td></tr>'.
          '<tr><th>Name</th><td><input type="text" name="name" value="'.$data['name'].'" size="50"></td></tr>'.
          '<tr><th>Menu Title</th><td><input type="text" name="menutitle" value="'.$data['menutitle'].'" size="50"></td></tr>'.
          '<tr><th>Title</th><td><input type="text" name="title" value="'.$data['title'].'" size="50"></td></tr>'.
          '<tr><th>Visible</th><td><input type="checkbox" name="online"'.(($data['online'])?" checked":"").'></td></tr>'.
          '<tr><th>Comments Enabled</th><td><input type="checkbox" name="comen"'.(($data['comen'])?" checked":"").'></td></tr>'.
          '</table>'.

          '<p>Text<br>'.
          '<textarea rows="40" style="width:100%" class="mceedit" name="text">'.$data['text'].
          '</textarea></p>'.

          '<br><input type="submit" value="Save"></form></p>'.
          '</div>';
  }
}

function OutputStatistic($adm)
{
  if($adm->GetStart()!=0)
  {
    $today=$adm->GetStart();
    $tomorrow=$adm->GetEnd();
  }
  else
  {
    $today=time();
    $tomorrow=$today-($today%86400)-(1*86400);
  }

  echo '<div id="mainContent"><h1>Statistics</h1>';

  echo "<h2>Filter</h2>\n".
        '<form method="POST" action="index.php?adminaction=statistic&starttime='.(int)($adm->GetStart()).'&endtime='.(int)($adm->GetEnd()).'"><input type="checkbox" name="fullref" '.(($_POST['fullref']=="on")?" checked":"").'>Show full Referer<input type="submit" value="Filter"></form>';
                                              
  $fullref=0;
  if($_POST['fullref']=="on")
    $fullref=1;


  echo '<div id="logview">';
  echo "<h2>Requests</h2>\n";
  logaccess::display_requests(config::$logfile,1,$tomorrow);
  echo "<h2>Referers</h2>\n";
  logaccess::display_referers(config::$logfile,1,$fullref,$tomorrow);
  echo "<h2>User-Agents</h2>\n";
  logaccess::display_useragents(config::$logfile,1,$tomorrow);
  echo "<h2>User Systems</h2>\n";
  logaccess::display_systems(config::$logfile,1,$tomorrow);
  echo "<h2>Hosts</h2>\n";
  logaccess::display_hosts(config::$logfile,1,$tomorrow);

//  echo "<h2>Blubb</h2>\n";
//  logaccess::display_30days(config::$logfile,1,time());

  echo "</div>\n";
  echo "</div>\n";
}


?>
