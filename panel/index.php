<?php
set_time_limit(0);
// Gerekli dosyaları require edelim
require_once __DIR__ . '/sectors.php';
require_once __DIR__ . '/icon-items.php';
require_once __DIR__ . '/author-names.php';
require_once __DIR__ . '/words-for-meta.php';
require_once __DIR__ . '/functions.php';
?>

<!DOCTYPE html>
<html lang="tr-TR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Oluşturma Paneli</title>
    <link rel="stylesheet" href="/css/panel.min.css">
</head>

<body>

    <header>
        <nav style="justify-content: center;">
            <div class="logo">Site Oluşturma Paneli</div>
        </nav>
    </header>
    <div class="header">

        <?php

        // Post edildiği zaman yapılan işlemler
        if (isset($_POST["post:site-create"])) {


            // Sites isimli klasör kontrolü => eğer klasör varsa silip tekrardan oluşturuyor eğer yoksa oluşturuyor.
            if (file_exists(__DIR__ . '/../sites')) {
                $removeDirSites = removeFolder(__DIR__ . '/../sites');
                if ($removeDirSites) {
                    mkdir(__DIR__ . '/../sites', 0777, true);
                }
            } else {
                mkdir(__DIR__ . '/../sites', 0777, true);
            }


            // Gelen post değerlerini alalım
            $siteName = mx_filter(@$_POST["site-name"], false);
            $domain = mx_filter(@$_POST["domain"]);
            $phone = mx_filter(@$_POST["phone"]);
            $color = @$_POST["color"];
            $menuPosition = @$_POST["menu-position"];
            $ctaText = ucfirst_tr(mx_filter(@$_POST["cta-text"], false));
            //$homeTitle = mx_filter(@$_POST["home-title"], false);
            //$homeDesc = mx_filter(@$_POST["home-desc"], false);
            //$sectorTitle = mx_filter(@$_POST["sector-title"], false);
            //$sectorDesc = mx_filter(@$_POST["sector-desc"], false);
            $locationPages = @$_POST["location-pages"];
            $favicon = @$_FILES["favicon"];
            $address = mx_filter(@$_POST["address"], false);
            $companyName = mx_filter(@$_POST["company-name"], false);
            $referanceInputNum = @$_POST["referance-input-num"];
            $analyticsCode = @$_POST["analytics-code"];
            $conversionTrackingCode = @$_POST["conversion-tracking-code"];
            $conversionTriggerCode = @$_POST["conversion-trigger-code"];


            // Boşluk kontrolü yapalım (sadece zorunlu olanlar)
            if (empty($siteName) && empty($domain) && empty($phone) && empty($color) && empty($menuPosition) && empty($ctaText) && empty($homeTitle) && empty($homeDesc) && empty($sectorTitle) && empty($sectorDesc) && empty($locationPages) && !isset($favicon) && empty($address)) {

                // Eğer boş input varsa uyarı mesajı gösterelim
                msg('Lütfen boş bırakmayın!');
                echo '<a style="padding: 0.25rem;border-radius:0.5rem;" href="/panel">Tekrar form sayfasına dönmek için tıkla.</a>';
            } else {
                // Eğer boş input yoksa devam edelim

                if (isset($_FILES["site-favicon"]) && $_FILES["site-favicon"]["size"]  > (20 * 1024)) {
                    msg('Maksimum favicon yükleme boyutu 20 KB\'dir.');
                    die();
                } else if (isset($_FILES['site-favicon']) && (pathinfo($_FILES["site-favicon"]["name"], PATHINFO_EXTENSION) !== 'png')) {
                    msg('Sadece png formatındaki dosyalar yüklenebilir.');
                    die();
                }

                // Domaindeki . olan yerleri _ ile değiştirelim
                $domainReplace = str_replace('.', '_', $domain);


                // Yeni oluşacak sitenin klasörünü kontrol edelim eğer yoksa oluşturalım
                if (!file_exists(__DIR__ . '/../sites/' . $domainReplace)) {
                    mkdir(__DIR__ . '/../sites/' . $domainReplace, 0777, true);
                }


                // Posttan aldığımız telefonu formatlayalım
                $formattedPhone = '0 (' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2);


                // Tema paletini ayarlayalım
                if ($color === 'blue') {
                    $rootColors = '<style>:root{--gradient-dark:#012946;--gradient-primary:#00529b;--primary:#7aafff;--primary-hover:#2a75e7;}</style>';
                } else if ($color === 'orange') {
                    $rootColors = '<style>:root{--gradient-dark:#5a2b00;--gradient-primary:#a16000;--primary:#f5a35c;--primary-hover:#c56502;}</style>';
                } else if ($color === 'brown') {
                    $rootColors = '<style>:root{--gradient-dark:#301306;--gradient-primary:#7c4228;--primary:#bf671c;--primary-hover:#762b08;}</style>';
                } else if ($color === 'purple') {
                    $rootColors = '<style>:root{--gradient-dark:#23083c;--gradient-primary:#992f95;--primary:#c496f7;--primary-hover:#8b54c9;}</style>';
                }else{
                    $rootColors = '';
                }


                // Referans linklerimizi oluşturalım
                $refLink = "";
                for ($i = 1; $i <= $referanceInputNum; $i++) {
                    $refInputName = ucfirst_tr(mx_filter(@$_POST["referance-name-" . $i]));
                    $refInputLink = $_POST["referance-link-" . $i];
                    if ($refInputName !== '' && $refInputLink !== '') {
                        $refLink .= '<a title="' . $refInputName . '" href="' . $refInputLink . '">' . $refInputName . '</a>';
                    }
                }

                // Menü pozisyonu
                $menuPosition = @$_POST["menu-position"];
                if ($menuPosition === 'right') {
                    $rootMenuPosition = '<style>nav{flex-direction:row-reverse;}nav ul{flex-direction:row-reverse;}#sideBarBtn{margin-right:0;}.left-bar{left:initial;right:0;}.left-bar #closeBtn{right:initial;left:0;}</style>';
                } else {
                    $rootMenuPosition = '<style>nav{flex-direction:row;}nav ul{flex-direction:row;}.left-bar{left:0;right:initial;}.left-bar #closeBtn{right:0;left:initial;}</style>';
                }


                // Siteyi oluşturalım
                create_site([

                    // Değişkenleri gönderiyoruz
                    "site-name" => $siteName,
                    "domain" => $domain,
                    "domain-replace" => $domainReplace,
                    "phone" => '0' . $phone,
                    "formatted-phone" => $formattedPhone,
                    "color" => $color,
                    "root-colors" => $rootColors,
                    "menu-position" => $menuPosition,
                    "cta-text" => $ctaText,
                    "words-for-meta" => $arrWords,
                    /*"home-title" => $homeTitle,
                    "home-desc" => $homeDesc,
                    "sector-title" => $sectorTitle,
                    "sector-desc" => $sectorDesc,*/
                    "location-pages" => $locationPages,
                    "favicon" => $favicon,
                    "address" => $address,
                    "company-name" => $companyName,
                    "sectors" => $arrSector,
                    "icon-items" => $arrIconItems,
                    "author-names" => $arrAuthorNames,
                    "referances" => $refLink,
                    "analytics-code" => $analyticsCode,
                    "conversion-tracking-code" => $conversionTrackingCode,
                    "conversion-trigger-code" => $conversionTriggerCode,

                    // Replace olacak değişkenleri gönderelim
                    "replace" => [
                        [
                            "variable" => "{rootColor}",
                            "item" => $rootColors
                        ], [
                            "variable" => "{rootMenuPosition}",
                            "item" => $rootMenuPosition
                        ], [
                            "variable" => "{conversionTrackingCode}",
                            "item" => $conversionTrackingCode
                        ], [
                            "variable" => "{conversionTriggerCode}",
                            "item" => $conversionTriggerCode
                        ], [
                            "variable" => "{analyticsCode}",
                            "item" => $analyticsCode
                        ], [
                            "variable" => "{refLinks}",
                            "item" => $refLink
                        ], [
                            "variable" => "{ctaText}",
                            "item" => $ctaText
                        ], [
                            "variable" => "{siteName}",
                            "item" => $siteName
                        ], [
                            "variable" => "{siteAddress}",
                            "item" => $address
                        ], [
                            "variable" => "{phone}",
                            "item" => $phone
                        ], [
                            "variable" => "{formattedPhone}",
                            "item" => $formattedPhone
                        ], [
                            "variable" => "{domain}",
                            "item" => $domain
                        ], [
                            "variable" => "{sectorActive}",
                            "item" => ""
                        ]
                    ]

                ]);
            }
        } else {
        ?>

            <form action="" method="post" autocomplete="off" enctype="multipart/form-data">
                <div class="form-message"><sup>*</sup> ile belirtilen alanlar zorunludur.</div>
                <div class="input">
                    <label for="site-name">Site adı<sup>*</sup></label>
                    <input type="text" id="site-name" name="site-name" placeholder="Site adı" required />
                </div>
                <div class="input">
                    <label for="domain">Domain<sup>*</sup> <small>(www) olmadan</small></label>
                    <input type="text" id="domain" name="domain" placeholder="Domain" required />
                </div>
                <div class="input">
                    <label for="phone">Telefon numarası<sup>*</sup> <small>Başında 0 olmadan</small></label>
                    <input type="tel" pattern="[0-9]{10}" id="phone" name="phone" placeholder="Telefon numarası" required />
                </div>
                <div class="input">
                    <label for="color">Site rengi<sup>*</sup></label>
                    <select id="color" name="color">
                        <option value="blue">Mavi</option>
                        <option value="orange">Turuncu</option>
                        <option value="brown">Kahverengi</option>
                        <option value="green">Yeşil</option>
                        <option value="purple">Mor</option>
                    </select>
                </div>
                <div class="input">
                    <label for="menu-position">Menü pozisyonu<sup>*</sup></label>
                    <select id="menu-position" name="menu-position">
                        <option value="left">Sol</option>
                        <option value="right">Sağ</option>
                    </select>
                </div>
                <div class="input">
                    <label for="cta-text">Bayimiz ol yazısı<sup>*</sup></label>
                    <input type="text" id="cta-text" name="cta-text" placeholder="Bayimiz ol yazısı" required />
                </div>
                <!-- <div class="input">
                    <label for="home-title">Anasayfa başlık yazısı<sup>*</sup></label>
                    <label for="home-title"><small>Başlığı yazarken kullanmak zorunda olduğunuz değişken <a title="Değişkeni eklemek için tıklayın" onclick="addVariable('home-title', '{siteName}')" style="background-color: #fff;font-size:0.625rem;border-radius:0.25rem;"><strong>{siteName}</strong></a></small></label>
                    <label for="home-title"><small>Kalan karakter hakkınız: <strong id="home-title-char"></strong></small></label>
                    <input type="text" id="home-title" name="home-title" controle-char="true" placeholder="Anasayfa başlık yazısı" required />
                </div>
                <div class="input">
                    <label for="home-desc">Anasayfa açıklama yazısı<sup>*</sup></label>
                    <label for="home-desc"><small>Açıklamayı yazarken kullanmak zorunda olduğunuz değişken <a title="Değişkeni eklemek için tıklayın" onclick="addVariable('home-desc', '{siteName}')" style="background-color: #fff;font-size:0.625rem;border-radius:0.25rem;"><strong>{siteName}</strong></a></small></label>
                    <label for="home-desc"><small>Kalan karakter hakkınız: <strong id="home-desc-char"></strong></small></label>
                    <input type="text" id="home-desc" name="home-desc" controle-char="true" placeholder="Anasayfa açıklama yazısı" required />
                </div>
                <div class="input">
                    <label for="sector-title">Sektör başlık yazısı<sup>*</sup></label>
                    <label for="sector-title"><small>Başlığı yazarken kullanmak zorunda olduğunuz değişken <a title="Değişkeni eklemek için tıklayın" onclick="addVariable('sector-title', '{sectorName}')" style="background-color: #fff;font-size:0.625rem;border-radius:0.25rem;"><strong>{sectorName}</strong></a></small></label>
                    <label for="sector-title"><small>Kalan karakter hakkınız: <strong id="sector-title-char"></strong></small></label>
                    <input type="text" id="sector-title" name="sector-title" controle-char="true" placeholder="Sektör başlık yazısı" required />
                </div>
                <div class="input">
                    <label for="sector-desc">Sektör açıklama yazısı<sup>*</sup></label>
                    <label for="sector-desc"><small>Açıklamayı yazarken kullanmak zorunda olduğunuz değişken <a title="Değişkeni eklemek için tıklayın" onclick="addVariable('sector-desc', '{sectorName}')" style="background-color: #fff;font-size:0.625rem;border-radius:0.25rem;"><strong>{sectorName}</strong></a></small></label>
                    <label for="sector-desc"><small>Kalan karakter hakkınız: <strong id="sector-desc-char"></strong></small></label>
                    <input type="text" id="sector-desc" name="sector-desc" controle-char="true" placeholder="Sektör açıklama yazısı" required />
                </div> -->
                <div class="input">
                    <label for="location-pages">Lokasyon sayfaları oluşsun mu?<sup>*</sup></label>
                    <select id="location-pages" name="location-pages">
                        <option value="no">Hayır</option>
                        <option value="yes">Evet</option>
                    </select>
                </div>
                <div class="input">
                    <label for="site-favicon">Site favicon<sup>*</sup></label>
                    <label for="site-favicon"><small>Sadece <strong>png</strong> türünde dosya yüklenebilir.</small></label>
                    <label for="site-favicon"><small>Maksimum dosya yükleme boyutu <strong>20KB</strong></small></label>
                    <input type="file" accept="image/png" name="site-favicon" id="site-favicon" required />
                </div>
                <div class="input">
                    <label for="company-name">Firma adı <sup>*</sup></label>
                    <input type="text" id="company-name" name="company-name" placeholder="Firma adı" required />
                </div>
                <div class="input">
                    <label for="address">Firma adresi <small>(konumu)</small><sup>*</sup></label>
                    <input type="text" id="address" name="address" placeholder="Firma adresi" required />
                </div>
                <div class="input">
                    <label for="referance-name-1">Referanslar</label>
                    <div id="referances">
                        <input style="margin-bottom: 0.5rem;" type="text" id="referance-name-1" name="referance-name-1" placeholder="1. Referans adı" />
                        <input style="margin-bottom: 2rem;" type="text" id="referance-link-1" name="referance-link-1" placeholder="1. Referans linki" />
                    </div>
                    <input type="hidden" style="display: none;" name="referance-input-num" value="1">
                    <a onclick="addReferances()" class="referance-button">Daha fazla referans ekle</a>
                </div>
                <div class="input">
                    <label for="analytics-code">Analytics kodu</label>
                    <textarea id="analytics-code" name="analytics-code" placeholder="Analytics Kodu"></textarea>
                </div>
                <div class="input">
                    <label for="conversion-tracking-code">Dönüşüm izleme kodu</label>
                    <textarea id="conversion-tracking-code" name="conversion-tracking-code" placeholder="Dönüşüm izleme Kodu"></textarea>
                </div>
                <div class="input">
                    <label for="conversion-trigger-code">Dönüşüm tetikleyici kodu</label>
                    <textarea id="conversion-trigger-code" name="conversion-trigger-code" placeholder="Dönüşüm tetikleyici kodu"></textarea>
                </div>
                <button type="submit" name="post:site-create">Site oluştur</button>
            </form>
        <?php } ?>
    </div>
    <script src="/js/panel.min.js"></script>
</body>

</html>