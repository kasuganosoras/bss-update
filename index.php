<?php
define('STORAGE_ROOT', 'https://static-cdn.zerodream.net/bss/updates/');
define('OFFICAL_LINK', 'https://dev.azure.com/blessing-skin/51010f6d-9f99-40f1-a262-0a67f788df32/_apis/git/repositories/a9ff8df7-6dc3-4ff8-bb22-4871d3a43936/Items?path=%2Fupdate.json');

// Function: Get version list
function GetVersionList($type, $version) {
    $file = scandir('updates/');
    $list = [];
    foreach($file as $item) {
        // regex match: blessing-skin-server-5.0.0-beta.1.zip
        $regType = empty($type) ? '' : "-{$type}.([0-9]{1,2})";
        if(preg_match("/^blessing-skin-server-{$version}([0-9\.]+){$regType}.zip$/", $item)) {
            $list[] = $item;
        }
    }
    return $list;
}

// Function: Get all version list
function GetAllVersionList($version) {
    $file = scandir('updates/');
    $list = [];
    foreach($file as $item) {
        // regex match: blessing-skin-server-5.0.0-beta.1.zip
        if(preg_match("/^blessing-skin-server-{$version}(.*)$/", $item)) {
            $list[] = $item;
        }
    }
    return $list;
}

// Function: Check update
function CheckUpdate() {
    $data = file_get_contents(OFFICAL_LINK);
    $data = json_decode($data, true);
    if(isset($data['latest'], $data['url'])) {
        if(!file_exists('updates/blessing-skin-server-'.$data['latest'].'.zip')) {
            ConsoleLog("New update available: {$data['latest']}");
            $file = file_get_contents($data['url']);
            if(!empty($file) && strlen($file) > 1024 * 1024) {
                file_put_contents("updates/blessing-skin-server-{$data['latest']}.zip", $file);
                ConsoleLog("Update finished: blessing-skin-server-{$data['latest']}.zip");
            } else {
                ConsoleLog('Update failed: file is empty or too small.');
            }
        }
    } else {
        ConsoleLog('Update failed: data is empty.');
    }
    ConsoleLog("All files up to date.");
}

// Function: Console log
function ConsoleLog($msg) {
    echo sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $msg);
}

// Check if running in CLI
if(php_sapi_name() == 'cli') {
    // Loop check update every 5 minutes
    while(true) {
        CheckUpdate();
        sleep(300);
    }
} else {
    // Get version number
    $version = isset($_GET['v']) && is_string($_GET['v']) && preg_match("/^[0-9]{1}$/", $_GET['v']) && intval($_GET['v']) >= 4 && intval($_GET['v']) <= 6 ? intval($_GET['v']) : 6;
    // Get release type
    $type    = !isset($_GET['type']) || !is_string($_GET['type']) || ($_GET['type'] !== 'rc' && $_GET['type'] !== 'beta' && $_GET['type'] !== 'alpha') ? '' : $_GET['type'];
    // Get subversion, format 5.x.x
    $subver  = isset($_GET['sv']) && is_string($_GET['sv']) && preg_match("/^[0-9].[0-9].[0-9]$/", $_GET['sv']) ? $_GET['sv'] : "";
    // Get build number
    $build   = isset($_GET['b']) && is_string($_GET['b']) && preg_match("/^[0-9]{1,3}$/", $_GET['b']) ? intval($_GET['b']) : 1;
    // Get file list
    $list    = GetVersionList($type, $version);
    // PHP version
    $phpver  = $version > 5 ? '7.4.0' : '7.3.0';

    if(in_array(sprintf('blessing-skin-server-%s.zip', $subver), $list) && !empty($subver)) {
        $final = $subver;
    } elseif(in_array(sprintf('blessing-skin-server-%s-%s.%d.zip', $subver, $type, $build), $list)) {
        $final = sprintf('%s-%s.%d', $subver, $type, $build);
    } else {
        $list  = GetAllVersionList($version);
        $last  = end($list);
        $final = preg_match("/^blessing-skin-server-([A-Za-z0-9\.\-]+).zip$/", $last, $matches) ? $matches[1] : str_replace('.zip', '', str_replace('blessing-skin-server-', '', $last));
    }

    Header('Content-Type: application/json');
    echo json_encode([
        'spec' => 2,
        'php' => $phpver,
        'latest' => $final,
        'url' => STORAGE_ROOT . "blessing-skin-server-{$final}.zip"
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}