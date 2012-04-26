<?php

function GetRSSHeader($self)
{
	return '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n".
				'<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" '.
        			'xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">'."\n".
				'  <channel>'."\n".
        '    <atom:link href="'.$self.'" rel="self" type="application/rss+xml" />'."\n".
				'    <title>codenaschen.de</title>'."\n".
				'    <link>http://codenaschen.de/tichyblog/</link>'."\n".
				'    <description>default rss feed text</description>'."\n".
				'    <language>en</language>'."\n".
				'    <copyright>krater (Andreas Schuler)</copyright>'."\n".
        '    <dc:creator>krater (Andreas Schuler)</dc:creator>'."\n".
				'    <pubDate>'.date("D, d M Y G:i:s O").'</pubDate>'."\n".
/*				'    <image>'."\n".
				'      <url>URL of image</url>'."\n".
				'      <title>Title of image</title>'."\n".
				'      <link>link of image</link>'."\n".
				'    </image>'."\n".*/
        '';
}

function GetRSSItem($title,$description,$text,$link,$id,$unixtime)
{
	return '    <item>'."\n".
				'      <title><![CDATA['.$title.']]></title>'."\n".
				'      <description><![CDATA['.$text.']]></description>'."\n".
//				'      <description><![CDATA['.$text.']]></description>'."\n".
				'      <link><![CDATA['.$link.']]></link>'."\n".
//				'      <author>Author</author>'."\n".
        '      <dc:creator>Author</dc:creator>'."\n".
				'      <guid><![CDATA['.$link.']]></guid>'."\n".
				'      <pubDate>'.date("D, d M Y G:i:s O",$unixtime).'</pubDate>'."\n".
				'    </item>'."\n";
}

function GetRSSFooter()
{
	return '  </channel>'."\n".
				'</rss>'."\n";
}

?>
