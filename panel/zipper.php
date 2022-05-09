<?php

function zipper($siteName)
{
    $allZips = glob("*.zip");
    foreach($allZips as $singleZip){
        unlink($singleZip);
    }
	$uid = uniqid();
    $zip = new ZipArchive();
    $base = __DIR__ . '/../sites/';
    if ($zip->open($siteName .'-'.$uid .'.zip', ZipArchive::CREATE) === true) {
        if ($handle = opendir($base . '/' . $siteName)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (!is_dir($base . '/' . $siteName . '/' . $entry)) {
                        $zip->addFile($base . '/' . $siteName . '/' . $entry, $siteName . '/' . $entry);
                    } else {
                        $handle2 = opendir($base . '/' . $siteName . '/' . $entry);
                        while (false !== ($entry2 = readdir($handle2))) {
                            if ($entry2 != "." && $entry2 != "..") {
                                if (!is_dir($base . '/' . $siteName . '/' . $entry . '/' . $entry2)) {
                                    $zip->addFile($base . '/' . $siteName . '/' . $entry . '/' . $entry2, $siteName . '/' . $entry . '/' . $entry2);
                                }
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }

        $zip->close();

        return '<a class="message-box" href="/panel/' . $siteName .'-'.$uid . '.zip">' . $siteName .'-'.$uid . ' zip hazır tıklayıp indirin!</a>'.'<a style="padding: 0.25rem;border-radius:0.5rem;" href="/panel">Tekrar form sayfasına dönmek için tıkla.</a>';
    }
    return 'zip oluşturulamadı';
}