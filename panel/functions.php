<?php
require_once __DIR__ . '/zipper.php';

// Veriyi filtreleme işlemini yapıyor
function mx_filter($val, $lower = true)
{
    if ($lower) {
        return addslashes(htmlspecialchars(mb_strtolower(trim($val), "UTF-8")));
    } else {
        return addslashes(htmlspecialchars(trim($val)));
    }
};


// Filtrelenen veriyi çözümlüyor (Ekrana çıktı verirken kullanılacak)
function getMxFilter($val)
{
    return stripslashes(htmlspecialchars(htmlspecialchars_decode(trim($val))));
};


// String'i Slug'a dönüştürüyor
function str_slug($text)
{
    $find = array("/Ğ/", "/Ü/", "/Ş/", "/İ/", "/Ö/", "/Ç/", "/ğ/", "/ü/", "/ş/", "/ı/", "/ö/", "/ç/");
    $degis = array("G", "U", "S", "I", "O", "C", "g", "u", "s", "i", "o", "c");
    $text = preg_replace("/[^0-9a-zA-ZÄzÜŞİÖÇğüşıöç]/", " ", $text);
    $text = preg_replace($find, $degis, $text);
    $text = preg_replace("/ +/", " ", $text);
    $text = preg_replace("/ /", "-", $text);
    $text = preg_replace("/\s/", "", $text);
    $text = strtolower($text);
    $text = preg_replace("/^-/", "", $text);
    $text = preg_replace("/-$/", "", $text);
    return $text;
};


// String'in ilk harfini büyük harfe çeviriyor
function ucfirst_tr($str)
{
    $m_length = mb_strlen($str, "UTF-8");
    $firstChar = mb_substr($str, 0, 1, "UTF-8");
    $remainder = mb_substr($str, 1, $m_length - 1, "UTF-8");
    $firstChar = mb_strtoupper($firstChar, "UTF-8");
    $remainder = mb_strtolower($remainder, "UTF-8");
    return $firstChar . $remainder;
};


// İçi dolu olsa bile klasörü silen fonksiyon
function removeFolder($folder)
{
    if (substr($folder, -1) != '/')
        $folder .= '/';
    if ($handle = opendir($folder)) {
        while ($obj = readdir($handle)) {
            if ($obj != '.' && $obj != '..') {
                if (is_dir($folder . $obj)) {
                    if (!removeFolder($folder . $obj))
                        return false;
                } elseif (is_file($folder . $obj)) {
                    if (!unlink($folder . $obj))
                        return false;
                }
            }
        }
        closedir($handle);
        if (!@rmdir($folder))
            return false;
        return true;
    }
    return false;
};


// Template bulan fonskiyon
function getFile($fileName, $domainReplace = null)
{
    if ($domainReplace !== null) {
        return __DIR__ . '/../sites/' . $domainReplace . '/' . $fileName;
    } else {
        return __DIR__ . '/../temp/' . $fileName;
    }
}


// Message Fonksiyonu
function msg($msg, $status = 'error')
{
    echo '<div class="message ' . $status . '">' . $msg . '</div>';
}


// Navigation link yapısı
function setLinks($arr)
{

    /* @params -> required => [
        "active" => "home", //(String: "Aktif classı olacak olan linki belirler")
        "site-name" => $arr["site-name"], // Linkin gözükecek olan adı
        "links" => $impludeLinksHome | $impludeLinks // Sektör linkleri
    ]
    */


    $indexClass = $arr["active"] === 'index' ? 'active' : null;
    $aboutClass = $arr["active"] === 'about' ? 'active' : null;
    $contactClass = $arr["active"] === 'contact' ? 'active' : null;
    $privacyClass = $arr["active"] === 'privacy-policy' ? 'active' : null;
    $cookieClass = $arr["active"] === 'cookie-policy' ? 'active' : null;
    if (!$arr["sector-files"]) {
        return '<a href="/" title="' . ($arr["site-name"]) . '" class="' . $indexClass . '">Anasayfa</a><a title="Hakkımızda" href="/about.html" class="' . $aboutClass . '">Hakkımızda</a><a title="İletişim" href="/contact.html" class="' . $contactClass . '">İletişim</a><a title="Gizlilik Politikası" href="/privacy-policy.html" class="' . $privacyClass . '">Gizlilik Politikası</a><a title="Çerez Politikası" href="/cookie-policy.html" class="' . $cookieClass . '">Çerez Politikası</a><br><br><br>' . $arr["links"] . "<br><br><br>";
    } else {
        return $arr["links"];
    }
}

// function create_file($arr, $fileName, $getTemplate = null, $createFolder = null)
function create_file($arrCreate)
{

    // Varsayılan ayarlar
    $arrDistrict = !isset($arrCreate["district"]) ? null : $arrCreate["district"];
    $arrDistrictCityId = !isset($arrCreate["district-city-id"]) ? null : $arrCreate["district-city-id"];
    $arrCitySektors = !isset($arrCreate["city"]) ? false : $arrCreate["city"];
    $arrCreateSectorFiles = !isset($arrCreate["sector-files"]) ? false : $arrCreate["sector-files"];
    $arrCreateTemplate = !isset($arrCreate["template"]) ? null : $arrCreate["template"];
    $arrCreateBaseText = !isset($arrCreate["base-text"]) ? null : $arrCreate["base-text"];
    $fileName = !isset($arrCreate["file-name"]) ? null : $arrCreate["file-name"];


    // Sektör dosyası oluştur aktifse BaseText klasörünü kontrol et eğer yoksa oluştur
    if ($arrCreateSectorFiles) {
        if (!file_exists(getFile($arrCreateBaseText, $arrCreate["arr"]["domain-replace"]))) {
            mkdir(getFile($arrCreateBaseText, $arrCreate["arr"]["domain-replace"]), 0777, true);
        }
    }


    // Dosyayı kontrol edelim eğer yoksa oluşturalım
    if (!file_exists(getFile($arrCreate["file-name-slug"] . '.html', $arrCreate["arr"]["domain-replace"]))) {

        // Index Template'i alalım
        if ($arrCreateTemplate === null) {
            $template = file_get_contents(getFile($arrCreate["file-name-slug"] . '.html'));
        } else {
            $template = file_get_contents(getFile($arrCreateTemplate . '.html'));
        }

        // Sektör linklerimizi oluşturalım
        $sectorLinks = [];


        // Sektör dosyası oluşacaksa sektör bilgisini al
        if ($arrCreateSectorFiles) {
            $sectorSlug = '';
            $sectorTitle = $arrCreate["arr"]["sector-title"];
            $sectorDesc = $arrCreate["arr"]["sector-desc"];

            $breadcrumbSchema = '{
                "@type": "ListItem",
                "position": 1,
                "name": "{siteName}",
                "item": "https://{domain}"
              },{
                "@type": "ListItem",
                "position": 2,
                "name": "{base}",
                "item": "https://{domain}/{baseSlug}"
              },{
                "@type": "ListItem",
                "position": 3,
                "name": "{sectorName}"
                "item": "https://{domain}/{baseSlug}/{sectorSlug}"
            }';

            $breadcrumb = '<span><a href="/">{siteName}</a></span>
            <span><a class="active">{sectorName}</a></span>';

            $randKM = rand(0, 150) / 100;
        }


        foreach ($arrCreate["arr"]["sectors"] as $sector) {
            if ($sector === $fileName) {
                $sectorActive = "active";
                if ($arrCreate["arr"]["location-pages"] === 'yes') {
                    $cityLinks = "{cityLinks}";
                } else {
                    $cityLinks = "";
                }
            } else {
                $sectorActive = "";
                $cityLinks = "";
            }
            $sectorLinks[] = '<a title="' . $sector . '" href="/' . $arrCreate["arr"]["base-text"] . '/' . str_slug($sector) . '.html" class="' . $sectorActive . '">' . $sector . '</a>' . $cityLinks;
        }

        // Navigation için Linkleri alalım
        if ($arrCreateSectorFiles) {
            $implodeLinksHome = implode(' ', $sectorLinks);
            $implodeLinks = setLinks([
                "active" => $arrCreate["file-name-slug"],
                "site-name" => $arrCreate["arr"]["site-name"],
                "sector-files" => true,
                "links" =>  $implodeLinksHome
            ]);
        } else {
            $implodeLinksHome = implode(' ', $sectorLinks);
            $implodeLinks = setLinks([
                "active" => $arrCreate["file-name-slug"],
                "site-name" => $arrCreate["arr"]["site-name"],
                "sector-files" => false,
                "links" =>  $implodeLinksHome
            ]);
        }


        // Replace olacak dizimize linklerimizi de ekliyoruz
        $arrCreate["arr"]["replace"][] = [
            "variable" => "{navLinks}",
            "item" => $implodeLinks
        ];
        $arrCreate["arr"]["replace"][] = [
            "variable" => "{navLinksHome}",
            "item" => $implodeLinksHome
        ];

        if ($arrCreateSectorFiles) {
            if ($arrCreate["arr"]["location-pages"] === 'yes') {

                $getCities = file_get_contents('../json/cities.json');
                $dataCities = json_decode($getCities, true);
                $getDistricts = file_get_contents('../json/districts.json');
                $dataDistrics = json_decode($getDistricts, true);
                $cityLinks = [];
                $cityName = "";
                $districtName = "";

                if ($arrDistrict !== null) {
                    $districts = array_filter($dataDistrics, function ($item) use ($arrDistrictCityId) {
                        return $item["city_id"] == $arrDistrictCityId;
                    });
                    foreach ($districts as $district) {
                        $cityLinks[] = '<li><a href="/' . $arrDistrict . '/' . $arrCreate["arr"]["base-text"] . '/' . str_slug($fileName) . '.html">' . $district["name"] . '</a></li>';
                        $cityName = explode('-', $district["slug"])[0];
                    }
                } else {
                    if ($arrCitySektors) {
                        foreach ($dataCities as $citys) {
                            $districts = array_filter($dataDistrics, function ($item) use ($citys) {
                                return $item["city_id"] == $citys["id"];
                            });
                            foreach ($districts as $district) {
                                $cityLinks[] = '<li><a href="/' . $arrDistrict . '/' . $arrCreate["arr"]["base-text"] . '/' . str_slug($fileName) . '.html">' . $district["name"] . '</a></li>';
                                $cityName = explode('-', $district["slug"])[0];
                            }
                        }
                    } else {
                        foreach ($dataCities as $citys) {
                            $cityLinks[] = '<li><a href="/' . $citys["slug"] . '/' . $arrCreate["arr"]["base-text"] . '/' . str_slug($fileName) . '.html">' . $citys["name"] . '</a></li>';
                        }
                    }
                }
            }

            // Replace olacak dizimize sektör değişkenlerini ekliyoruz
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{_SCRIPT_BREADCRUMB_}",
                "item" => $breadcrumbSchema
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{breadcrumb}",
                "item" => $breadcrumb
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{sectorSlug}",
                "item" => $arrCreate["file-name-slug"]
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{siteName}",
                "item" => $arrCreate["arr"]["site-name"]
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{base}",
                "item" => ucfirst_tr($arrCreate["arr"]["base-text"])
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{baseSlug}",
                "item" => str_slug($arrCreate["arr"]["base-text"])
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{domain}",
                "item" => $arrCreate["arr"]["domain"]
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{KM}",
                "item" => $randKM
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{sectorActive}",
                "item" => $sectorSlug
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{sectorTitle}",
                "item" => $sectorTitle
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{sectorDesc}",
                "item" => $sectorDesc
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{sectorName}",
                "item" => ucfirst_tr($fileName)
            ];
            if ($arrCreate["arr"]["location-pages"] === 'yes') {
                if ($arrCitySektors) {
                    $setCityLink = '<ul><li><a>' . $cityName . '</a></li><ul>' . implode(' ', $cityLinks) . '</ul></ul>';
                } else if ($arrDistrict !== null) {
                    $setCityLink = '<ul><li><a>' . $cityName . '</a></li><ul><li><a>' . $cityName . '</a></li><ul>' . implode(' ', $cityLinks) . '</ul></ul></ul>';
                } else {
                    $setCityLink = '<ul>' . implode(' ', $cityLinks) . '</ul>';
                }
            } else {
                $setCityLink = "";
            }
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{cityLinks}",
                "item" => $setCityLink
            ];
        }


        // Değişkenleri Replace edelim
        foreach ($arrCreate["arr"]["replace"] as $variable) {
            $template = str_replace($variable["variable"], $variable["item"], $template);
        }


        // Dosyayı oluşturalım
        if ($arrCreateSectorFiles) {
            if ($arrDistrict !== null) {
                $filePath = $arrDistrict . '/' . $arrCreateBaseText . '/' . $arrCreate["file-name-slug"];
                if (!file_exists(getFile($arrDistrict . '/' . $arrCreateBaseText, $arrCreate["arr"]["domain-replace"]))) {
                    mkdir(getFile($arrDistrict . '/' . $arrCreateBaseText, $arrCreate["arr"]["domain-replace"]), 0777, true);
                }
            } else {
                $filePath = $arrCreateBaseText . '/' . $arrCreate["file-name-slug"];
            }
        } else {
            $filePath = $arrCreate["file-name-slug"];
        }
        $file = file_put_contents(getFile($filePath . '.html', $arrCreate["arr"]["domain-replace"]), $template);
        if (!$file) {
            msg($arrCreate["file-name-slug"] . '.html dosyası oluşurken bir hata oluştu!');
        }
    }
}


function create_site($arr)
{

    /* @params -> required => [

    // Değişkenleri gönderiyoruz
    "site-name" => $siteName,
    "domain" => $domain,
    "domain-replace" => $domainReplace,
    "phone" => $phone,
    "formatted-phone" => $formattedPhone,
    "color" => $color,
    "root-colors" => $rootColors,
    "menu-position" => $menuPosition,
    "cta-text" => $ctaText,
    "base-text" => $baseText,
    "home-title" => $homeTitle,
    "home-desc" => $homeDesc,
    "sector-title" => $sectorTitle,
    "sector-desc" => $sectorDesc,
    "location-pages" => $locationPages,
    "favicon" => $favicon,
    "address" => $address,
    "sectors" => $arrSector,
    "referances" => $refLink,
    "analytics-code" => $analyticsCode,
    "conversion-tracking-code" => $conversionTrackingCode,
    "conversion-trigger-code" => $conversionTriggerCode,

    // Replace olacak değişkenleri gönderelim
    "replace" => [

        // İstediğim kadar değişken gönderebilirim. Örnek kullanım:
        [
            "variable" => "{siteName}",
            "item" => $item // Örnek: ($item = $analyticsCode)
        ]

    ]


    ];*/


    // Dosyaları oluşturalım
    create_file([
        "arr" => $arr,
        "file-name-slug" => 'index'
    ]);

    create_file([
        "arr" => $arr,
        "file-name-slug" => 'about'
    ]);

    create_file([
        "arr" => $arr,
        "file-name-slug" => 'contact'
    ]);

    create_file([
        "arr" => $arr,
        "file-name-slug" => 'privacy-policy'
    ]);

    create_file([
        "arr" => $arr,
        "file-name-slug" => 'cookie-policy'
    ]);

    // Sektörleri oluşturalım
    foreach ($arr["sectors"] as $sector) {
        create_file([
            "sector-files" => true,
            "template" => "service",
            "base-text" => $arr["base-text"],
            "arr" => $arr,
            "file-name" => $sector,
            "file-name-slug" => str_slug($sector)
        ]);
    }

    if ($arr["location-pages"] === 'yes') {

        // Şehir ve İlçe Json dosyalarını alalım
        $getCities = file_get_contents('../json/cities.json');
        $dataCities = json_decode($getCities, true);
        $getDistricts = file_get_contents('../json/districts.json');
        $dataDistrics = json_decode($getDistricts, true);

        // Şehirleri döngüden geçirelim
        foreach ($dataCities as $city) {

            // Şehire ait ilçeleri bulalım
            $districts = array_filter($dataDistrics, function ($item) use ($city) {
                return $item["city_id"] == $city["id"];
            });

            // Şehrin klasörü yoksa oluşturalım
            if (!file_exists(getFile($city["slug"], $arr["domain-replace"]))) {
                mkdir(getFile($city["slug"], $arr["domain-replace"]), 0777, true);
            }

            // Dosyaları oluşturalım
            create_file([
                "arr" => $arr,
                "template" => "index",
                "file-name-slug" => $city["slug"] . '/index'
            ]);

            create_file([
                "arr" => $arr,
                "template" => "about",
                "file-name-slug" => $city["slug"] . '/about'
            ]);

            create_file([
                "arr" => $arr,
                "template" => "contact",
                "file-name-slug" => $city["slug"] . '/contact'
            ]);

            create_file([
                "arr" => $arr,
                "template" => "privacy-policy",
                "file-name-slug" => $city["slug"] . '/privacy-policy'
            ]);

            create_file([
                "arr" => $arr,
                "template" => "cookie-policy",
                "file-name-slug" => $city["slug"] . '/cookie-policy'
            ]);

            // Sektörleri oluşturalım
            foreach ($arr["sectors"] as $sector) {
                create_file([
                    "city" => true,
                    "sector-files" => true,
                    "template" => "service",
                    "base-text" => $city["slug"] . '/' . $arr["base-text"],
                    "arr" => $arr,
                    "file-name" => $sector,
                    "file-name-slug" => str_slug($sector)
                ]);
            }

            foreach ($districts as $district) {
                // İlçelerin klasörü yoksa oluşturalım
                $districtSlug = explode("-", $district["slug"]);
                if (!file_exists(getFile($city["slug"] . '/' . $districtSlug[1], $arr["domain-replace"]))) {
                    mkdir(getFile($city["slug"] . '/' . $districtSlug[1], $arr["domain-replace"]), 0777, true);
                }
                // Sektörleri oluşturalım
                foreach ($arr["sectors"] as $sector) {
                    create_file([
                        "district" => $city["slug"] . '/' . $districtSlug[1],
                        "district-city-id" => $district["city_id"],
                        "sector-files" => true,
                        "template" => "service",
                        "base-text" => $arr["base-text"],
                        "arr" => $arr,
                        "file-name" => $sector,
                        "file-name-slug" => str_slug($sector)
                    ]);
                }
            }
        }
    }

    // Faviconu kayıt edelim
    if (isset($_FILES["site-favicon"])) {
        $path = __DIR__ . '/../sites/' . $arr["domain-replace"] . '/check-icon.png';
        if (!file_exists($path)) {
            $result = move_uploaded_file($_FILES["site-favicon"]["tmp_name"], $path);
            if (!$result) {
                echo '<div class="message error">Favicon oluşturulurken bir hata oluştu.</div>';
            }
        }
    }

    echo zipper($arr["domain-replace"]);
};
