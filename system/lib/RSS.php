<?php
// simple RSS / Atom feed generator from a JSON outline schema object
class FeedMe {
  /**
   * Generate the RSS 2.0 header
   */
  public function getRSSFeed($site) {
    return '<?xml version="1.0" encoding="utf-8"?>
<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
  <channel>
    <title>' . $site->manifest->title . '</title>
    <link>' . $site->manifest->metadata->domain . '/rss.xml</link>
    <description>' . $site->manifest->description . '</description>
    <copyright>Copyright (C) ' . date('Y') . ' ' . $site->manifest->metadata->domain . '</copyright>
    <language>' . $site->language . '</language>
    <lastBuildDate>' . date('r', $site->manifest->metadata->updated) . '</lastBuildDate>
    <atom:link href="' . $site->manifest->metadata->domain . '/rss.xml" rel="self" type="application/rss+xml"/>'
    . $this->rssItems($site) . '
  </channel>
</rss>';
  }
  /**
   * Generate RSS items.
   */
  public function rssItems($site) {
    $output = '';
    foreach ($site->manifest->items as $key => $item) {
      $tags = '';
      if (isset($item->metadata->tags)) {
        $tags = implode(',', $item->metadata->tags);
      }
      $output .= '
    <item>
      <title>' . $item->title . '</title>
      <link>' . $site->manifest->metadata->domain . '/' . str_replace('pages/', '', str_replace('/index.html', '', $item->location)) . '</link>
      <description>
          <![CDATA[ ' . $item->description . ' ]]>
      </description>
      <category>' . $tags . '</category>
      <guid>' . $site->manifest->metadata->domain . '/' . str_replace('pages/', '', str_replace('/index.html', '', $item->location)) . '</guid>
      <pubDate>' . date("r", strtotime($item->metadata->created)) . '</pubDate>
    </item>';
    }
    return $output;
  }
  /**
   * Generate the atom feed
   */
  public function getAtomFeed($site) {
    return '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
  <title>' . $site->manifest->title . '</title>
  <link href="' . $site->manifest->metadata->domain . '/atom.xml" rel="self" />
  <subtitle>' . $site->manifest->description . '</subtitle>
  <updated>' . date(\DateTime::ATOM, $site->manifest->metadata->updated) . '</updated>
  <author>
      <name>' . $site->manifest->author . '</name>
  </author>
  <id>' . $site->manifest->metadata->domain . '/feed</id>'
  . $this->atomItems($site) . '
</feed>';
  }
  /**
   * Generate Atom items.
   */
  public function atomItems($site) {
    $output = '';
    foreach ($site->manifest->items as $key => $item) {
      $tags = '';
      if (isset($item->metadata->tags)) {
        foreach ($item->metadata->tags as $tag) {
          $tags .= '<category term="' . $tag . '" label="' . $tag . '" />';
        }
      }
      $output .= '
  <entry>
    <title>' . $item->title . '</title>
    <id>' . $site->manifest->metadata->domain . '/' . str_replace('pages/', '', str_replace('/index.html', '', $item->location)) . '</id>
    <updated>' . date(\DateTime::ATOM, $item->metadata->updated) . '</updated>
    <published>' . date(\DateTime::ATOM, $item->metadata->created) . '</published>
    <summary>' . $item->description . '</summary>
    <link href="' . $site->manifest->metadata->domain . '/' . str_replace('pages/', '', str_replace('/index.html', '', $item->location)) . '"/>
    ' . $tags . '
    <content type="html">
      <![CDATA[ ' . $item->description . ' ]]>
    </content>
  </entry>';
    }
    return $output;
  }
}