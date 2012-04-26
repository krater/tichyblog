<?php
include "config.php";
include "../db.php";
include "../helper.php";
include "../menu.php";
include "../content.php";
include "../logaccess.php";   
include "admincontent.php";
include "adminhelper.php";


logaccess::log(config::$logfile,3);

session_start();
$in=new inputvars();
$in->parse();
$adm=new adminvars();
$adm->parse();
$db=OpenDB();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
include "../template/".config::$template."/head.php";
?>
</head>

<body class="tichyblog">
<div id="container">
  <div id="header">
    <h1><?php echo config::$header;?></h1>
  </div>
<?php
OutputAdminMenu($db,$in);   

if((fileperms(config::$serverpath."images/")&0777)!=0777)
  echo '<div id="errContent"><p>Please edit access rights for images folder to 777</p></div>';

if((fileperms(config::$serverpath."admin/db/")&0777)!=0777)
  echo '<div id="errContent"><p>Please edit access rights for admin/db/ folder to 777</p></div>';

if((fileperms(config::$serverpath."admin/db/tichyblog.db")&0777)!=0666)
  echo '<div id="errContent"><p>Please edit access rights for tichyblog.db folder to 666</p></div>';

OutputAdminContent($db,$in,$adm);
CloseDB($db);
?>
  <br class="clearfloat" />
  <div id="footer">
    <p><?php echo config::$footer;?></p>
  </div>
</div>
</body>
</html>
