<?php
/*
   Simple PHP access logging class

   Copyright (C) 2011
   Andreas Schuler <andreas@schulerdev.de>
   http://codenaschen.de/

   This program is free software; you can redistribute it and/or modify it
   under the terms of the GNU General Public License version 2 as published
   by the Free Software Foundation.

   This program is distributed in the hope that it will be useful, but WITHOUT
   ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
   FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
   more details.

   You should have received a copy of the GNU General Public License along with
   this program; if not, write to the Free Software Foundation, Inc., 59 Temple
   Place, Suite 330, Boston, MA 02111-1307 USA
*/

class logaccess
{
  function log($logfile,$customid=1)
  {
    $db=logaccess::open_logdb($logfile);
    if(!$db)
      return;

    sqlite_query($db,"INSERT INTO access_logs (unixtime,ip,host,path,useragent,".
                    "referer,method,customid) VALUES (".time().
                    ",'".sqlite_escape_string($_SERVER['REMOTE_ADDR']).
                    "','".sqlite_escape_string($_SERVER["HTTP_HOST"]).
                    "','".sqlite_escape_string($_SERVER["REQUEST_URI"]).
                    "','".sqlite_escape_string($_SERVER["HTTP_USER_AGENT"]).
                    "','".sqlite_escape_string($_SERVER["HTTP_REFERER"]).
                    "','".sqlite_escape_string($_SERVER["REQUEST_METHOD"]).
                    "',".((int)$customid).");");

    sqlite_close($db);
  }

  function open_logdb($logfile)
  {
    if(!file_exists($logfile))
    {
      if(!($db=sqlite_open($logfile)))
        return 0;

      sqlite_query($db,"CREATE TABLE access_logs (id INTEGER PRIMARY KEY,unixtime INTEGER,".
                        " ip TEXT, host TEXT, path TEXT, useragent TEXT, referer TEXT,".
                        " method TEXT, customid INTEGER);");

      sqlite_close($db);
    }

    if(!($db=sqlite_open($logfile)))
      return 0;

    return $db;
  }


  // -------------------------------- Display functions --------------------------------
  function display_requests($logfile,$customid=0,$starttime=0,$endtime=9999999999)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter);

      $logcnt=sqlite_fetch_array($logcnts);

      $logs=sqlite_query($db,"SELECT path,count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter." GROUP BY path ORDER BY cnt DESC");

      while($log=sqlite_fetch_array($logs))
      {
        echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
              '<div class="barlabel">'.strip_tags($log['cnt']).': <a href="'.logaccess::remove_quotes($log['path']).'">'.strip_tags($log['path']).'</a></div>'."\n";
      }
    }

    sqlite_close($db);
  }

  function display_referers($logfile,$customid=0,$fullreferers=1,$starttime=0,$endtime=9999999999)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      if($fullreferers)
      {
        $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                  " AND unixtime<=".((int)$endtime).$filter);

        $logcnt=sqlite_fetch_array($logcnts);
      }

      $logs=sqlite_query($db,"SELECT referer,count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter." GROUP BY referer ORDER BY cnt DESC");

      if($fullreferers)
      {
        while($log=sqlite_fetch_array($logs))
        {
          echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
                '<div class="barlabel">'.strip_tags($log['cnt']).': <a href="'.logaccess::remove_quotes($log['referer']).'">'.strip_tags($log['referer']).'</a></div>'."\n";
        }
      }
      else
      {
        unset($domains);
        $logcnt=0;
        while($log=sqlite_fetch_array($logs))
        {
          if(strlen($log['referer'])>7)
          {
            $pos=strpos($log['referer'],'/',7);
            if($pos)
            {
              $domain=substr($log['referer'],0,$pos);
              $domains[$domain]+=$log['cnt'];
              $logcnt+=$log['cnt'];
            }
          }
        }

        foreach($domains as $domain => $cnt)
        {
          echo '<div class="bar" style="width:'.(100/$logcnt*$cnt).'%">&nbsp;</div>'."\n".
                '<div class="barlabel">'.strip_tags($cnt).': <a href="'.logaccess::remove_quotes($domain).'">'.strip_tags($domain).'</a></div>'."\n";
        }
      }
    }

    sqlite_close($db);
  }


  function display_useragents($logfile,$customid=0,$starttime=0,$endtime=9999999999)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter);

      $logcnt=sqlite_fetch_array($logcnts);

      $logs=sqlite_query($db,"SELECT useragent,count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter." GROUP BY useragent ORDER BY cnt DESC");

      while($log=sqlite_fetch_array($logs))
      {
        echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
              '<div class="barlabel">'.strip_tags($log['cnt']).': '.strip_tags($log['useragent']).'</div>'."\n";
      }
    }

    sqlite_close($db);
  }

  function display_systems($logfile,$customid=0,$starttime=0,$endtime=9999999999)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter);

      $logcnt=sqlite_fetch_array($logcnts);

      $logs=sqlite_query($db,"SELECT useragent LIKE '%Windows%' AS windows,".
                              "useragent LIKE '%Linux%' as linux,".
                              "useragent LIKE '%Mac%' as mac,".
                              "count(*) AS cnt FROM access_logs ".
                              "WHERE unixtime>=".((int)$starttime).
                              " AND unixtime<=".((int)$endtime).$filter." GROUP BY ".
                              "(useragent LIKE '%Windows%'),".
                              "(useragent LIKE '%Linux%'),".
                              "(useragent LIKE '%Mac%') ORDER BY cnt DESC");

      while($log=sqlite_fetch_array($logs))
      {
        if($log['windows']==1)
          $sys="Windows";
        else if($log['linux']==1)
          $sys="Linux";
        else if($log['mac']==1)
          $sys="Mac";
        else
          $sys="Other";

        echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
             '<div class="barlabel">'.strip_tags($log['cnt']).': '.$sys.'</div>'."\n";
      }
    }

    sqlite_close($db);
  }

  function display_hosts($logfile,$customid=0,$starttime=0,$endtime=9999999999)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter);

      $logcnt=sqlite_fetch_array($logcnts);

      $logs=sqlite_query($db,"SELECT ip,count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$starttime).
                                " AND unixtime<=".((int)$endtime).$filter." GROUP BY ip ORDER BY cnt DESC");

      while($log=sqlite_fetch_array($logs))
      {
        echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
              '<div class="barlabel">'.strip_tags($log['cnt']).': '.strip_tags($log['ip']).'</div>'."\n".
              '<div class="barlabel">'."\t\t\t\t\t".strip_tags(gethostbyaddr($log['ip'])).'</div>'."\n";
      }
    }

    sqlite_close($db);
  }


  function display_30days($logfile,$customid=0,$endtime)
  {
    $db=logaccess::open_logdb($logfile);

    if($db)
    {
      if($customid)
        $filter=" AND customid=".((int)$customid);
      else
        $filter="";

      echo "<div class=\"vbars\">\n";
      for($i=0;$i<30;$i++)
      {
        $startday=mktime(0,0,0,date("m"),date("d")+$i-30,date("Y"));
        $endday=mktime(0,0,0,date("m"),date("d")+$i-29,date("Y"));
        $logcnts=sqlite_query($db,"SELECT count(*) AS cnt FROM access_logs WHERE unixtime>=".((int)$startday).
                                " AND unixtime<=".((int)$endday).$filter);

        $logcnt=sqlite_fetch_array($logcnts);
        /*echo '<div class="bar" style="width:'.(100/$logcnt['cnt']*$log['cnt']).'%">&nbsp;</div>'."\n".
              '<div class="barlabel">'.$log['cnt'].': <a href="'.$log['path'].'">'.$log['path'].'</a></div>'."\n";    */

        echo '<div class="vcontainer"><div class="vbar" style="height:'.(100/50*$logcnt['cnt']).'%">'.$logcnt['cnt']."</div></div>\n";

      }

      echo "</div>\n";

      sqlite_close($db);
    }


  }

  private static function remove_quotes($str)
  {
    return str_replace('"','',$str);
  }

}







?>
