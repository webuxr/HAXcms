<?php
  include_once '../system/lib/bootstrapHAX.php';
  include_once $HAXCMS->configDirectory . '/config.php';
  // test if this is a valid user login
  if ($HAXCMS->validateJWT()) {
    header('Content-Type: application/json');
    // load the site from name
    $site = $HAXCMS->loadSite($HAXCMS->safePost['siteName']);
    // update these parts of the manifest to match POST
    $site->manifest->title = filter_var($_POST['manifest']->title, FILTER_SANITIZE_STRING);
    $site->manifest->description = filter_var($_POST['manifest']->description, FILTER_SANITIZE_STRING);
    if (isset($_POST['manifest']->metadata->icon)) {
      $site->manifest->metadata->icon = filter_var($_POST['manifest']->metadata->icon, FILTER_SANITIZE_STRING);
    }
    if (isset($_POST['manifest']->metadata->domain)) {
      $domain = filter_var($_POST['manifest']->metadata->domain, FILTER_SANITIZE_STRING);
      // support updating the domain CNAME value
      if ($site->manifest->metadata->domain != $domain) {
        $site->manifest->metadata->domain = $domain;
        @file_put_contents($site->directory . '/' . $site->manifest->metadata->siteName . '/CNAME', $domain);
      }
    }
    // look for a match so we can set the correct data
    foreach ($HAXCMS->getThemes() as $key => $theme) {
      if (filter_var($_POST['manifest']->metadata->theme, FILTER_SANITIZE_STRING) == $key) {
        $site->manifest->metadata->theme = $theme;
      }
    }
    $site->manifest->metadata->image = filter_var($_POST['manifest']->metadata->image, FILTER_SANITIZE_STRING);
    $site->manifest->metadata->hexCode = filter_var($_POST['manifest']->metadata->hexCode, FILTER_SANITIZE_STRING);
    $site->manifest->metadata->cssVariable = filter_var($_POST['manifest']->metadata->cssVariable, FILTER_SANITIZE_STRING);
    $site->manifest->metadata->updated = time();
    $site->manifest->save();
    // now work on HAXCMS layer to match the saved / sanitized data
    $item = $site->manifest;
    // remove items list as we only need the item itself not the nesting
    unset($item->items);
    $HAXCMS->outlineSchema->updateItem($item, TRUE);
    $site->gitCommit('Manifest updated');
    // check git remote if it came across as a possible setting
    if (isset($_POST['manifest']->metadata->git)) {
      if ((filter_var($_POST['manifest']->metadata->git->url, FILTER_SANITIZE_STRING)) &&
      (!isset($site->manifest->metadata->git->url) || $site->manifest->metadata->git->url != filter_var($_POST['manifest']->metadata->git->url, FILTER_SANITIZE_STRING))) {
        $site->gitSetRemote(filter_var($_POST['manifest']->metadata->git->url, FILTER_SANITIZE_STRING));
      }
    }
    header('Status: 200');
    print json_encode($site->manifest);
    exit;
  }
?>