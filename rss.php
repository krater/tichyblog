<?php
include "admin/config.php";
include "db.php";
include "helper.php";
include "logaccess.php";
include "template/".config::$template."/feed.php";

header("Content-Type: application/xml; charset=ISO-8859-1");


logaccess::log(config::$logfile,2);

$in=new inputvars();
$in->parse();
$db=OpenDB();

echo GetRSSHeader(config::$path."rss.php");

$es=sqlite_query($db,"SELECT id,name,unixtime,title,description,text FROM blogentry WHERE online=1 ORDER BY unixtime DESC LIMIT 10");
while($e=sqlite_fetch_array($es))
{
  echo GetRSSItem($e['title'],$e['description'],$e['text'],config::$path.'?action=blog&entry='.$e['id'].'_'.$e['name'],$e['id'],$e['unixtime'],config::$path.'images/blubb.jpg');
}


echo GetRSSFooter();



?>

