<?php
function siteMapCreate($arr)
{

    // Varsayılan olarak template yolu
    $siteMapXml = !isset($arr["sitemap-xml"]) ? false : $arr["sitemap-xml"];
    $xmlLinks = !isset($arr["xml-links"]) ? false : $arr["xml-links"];
    $domain = !isset($arr["domain"]) ? null : $arr["domain"];

    // SiteMap Template'i alalım
    if(!$xmlLinks){
        $siteMapTemplate = file_get_contents(__DIR__ . '/../temp/sitemap.xml');
    }else{
        $siteMapTemplate = file_get_contents(__DIR__ . '/../temp/sitemap-finish.xml');
    }

    // SiteMap Linklerini Implode edelim
    $siteMapLinksImplode = [];
    foreach ($arr["links"] as $siteMapLink) {
        if (!$siteMapXml) {
            $siteMapLinksImplode[] = '<url>
            <loc>' . $siteMapLink["loc"] . '</loc>
            <lastmod>' . date("c") . '</lastmod>
            <changefreq>' . $siteMapLink["changefreq"] . '</changefreq>
            <priority>' . $siteMapLink["changefreq"] . '</priority>
            </url>';
        } else {
            $siteMapLinksImplode[] = '<sitemap>
            <loc>http://' . $domain . '/sitemaps/' . $siteMapLink["loc"] . '</loc>
            <lastmod>' . date("c") . '</lastmod>
            </sitemap>';
        }
    }
    $siteMapLinksImplodeResult = implode('', $siteMapLinksImplode);

    // SiteMap Template'i Replace edelim
    $siteMapTemplate = str_replace('{siteMapUrl}', $siteMapLinksImplodeResult, $siteMapTemplate);

    // SiteMap dosyamızı yazalım
    if (!$xmlLinks) {
        $siteMapCreate = file_put_contents(__DIR__ . '/../sites/' . $arr["domain-replace"] . '/sitemaps/' . $arr["file-name"] . '-sitemap.xml', $siteMapTemplate);
    } else {
        $siteMapCreate = file_put_contents(__DIR__ . '/../sites/' . $arr["domain-replace"] . '/sitemap.xml', $siteMapTemplate);
    }

    if (!$siteMapCreate) {
        msg($arr["file-name"] . '-sitemap.xml dosyası oluşurken bir hata oluştu!');
    }
}
