<?php

function globber($base, $zip, $siteName){
    foreach(glob($base.'/*') as $dirFile){
        if(is_dir($dirFile)){
            globber($dirFile,$zip,$siteName);
        }else{
            $fileBase = substr($dirFile,strpos($base, $siteName));
            $zip->addFile($dirFile, $fileBase);
        }
    }
}

function zipper($siteName)
{
    $allZips = glob("*.zip");
    foreach ($allZips as $singleZip) {
        unlink($singleZip);
    }
    $uid = uniqid();
    $zip = new ZipArchive();
    $base = __DIR__ . '/../sites/' . $siteName;
    //zip yaratıldıysa
    if ($zip->open($siteName . '-' . $uid . '.zip', ZipArchive::CREATE) === true) {
        globber($base,$zip,$siteName);

        $zip->close();

        return '<a class="message-box success" href="/panel/' . $siteName . '-' . $uid . '.zip">' . $siteName . '-' . $uid . ' zip hazır tıklayıp indirin!</a><a style="padding: 0.25rem;border-radius:0.5rem;" href="/panel">Tekrar form sayfasına dönmek için tıkla.</a>';
    }
    return 'zip oluşturulamadı';
}
