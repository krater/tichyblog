<?php
include "admin/config.php";
include "db.php";
include "helper.php";
include "menu.php";
include "content.php";
include "logaccess.php";

logaccess::log(config::$logfile,1);

$in=new inputvars();
$in->parse();
$db=OpenDB();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="alternate" type="application/rss+xml" title="Rss-Feed" href=<?php echo '"'.config::$path;?>/rss.php">
<?php
include "template/".config::$template."/head.php";
?>
</head>

<body class="tichyblog">
<div id="container">
  <div id="header">
<?php
include "template/".config::$template."/head2.php";
?>
  </div>
<?php
OutputPublicMenu($db,$in);
OutputContent($db,$in);
CloseDB($db);
?>
  <br class="clearfloat" />
  <div id="footer">
    <p><?php echo config::$footer;?></p>
  </div>
</div>
</body>
</html>
