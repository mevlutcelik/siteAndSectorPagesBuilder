<?php
require_once __DIR__ . '/zipper.php';
require_once __DIR__ . '/site-map-creator.php';

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
// function ucfirst_tr($str)
// {
//     $m_length = mb_strlen($str, "UTF-8");
//     $firstChar = mb_substr($str, 0, 1, "UTF-8");
//     $remainder = mb_substr($str, 1, $m_length - 1, "UTF-8");
//     $firstChar = mb_strtoupper($firstChar, "UTF-8");
//     $remainder = mb_strtolower($remainder, "UTF-8");
//     return $firstChar . $remainder;
// };
function ucfirst_tr($str)
{
    return mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
}


// İçi dolu olsa bile klasörü silen fonksiyon
function removeFolder($folder)
{
    if (substr($folder, -1) != '/')
        $folder .= '/';
    if ($handle = opendir($folder)) {
        while ($obj = readdir($handle)) {
            if ($obj != '.' && $obj != '..') {
                if (is_dir($folder . $obj)) {
                    if (removeFolder($folder . $obj))
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


    $cityName = !isset($arr["city-name"]) ? null : $arr["city-name"];

    $indexClass = $arr["active"] === 'index' ? 'active' : null;
    $aboutClass = $arr["active"] === 'about' ? 'active' : null;
    $contactClass = $arr["active"] === 'contact' ? 'active' : null;
    $privacyClass = $arr["active"] === 'privacy-policy' ? 'active' : null;
    $cookieClass = $arr["active"] === 'cookie-policy' ? 'active' : null;
    if (!$arr["sector-files"]) {
        return '<a href="/" title="' . ($arr["site-name"]) . '" class="' . $indexClass . '">Anasayfa</a><a title="Hakkımızda" href="/about.html" class="' . $aboutClass . '">Hakkımızda</a><a title="İletişim" href="/contact.html" class="' . $contactClass . '">İletişim</a><a title="Gizlilik Politikası" href="/privacy-policy.html" class="' . $privacyClass . '">Gizlilik Politikası</a><a title="Çerez Politikası" href="/cookie-policy.html" class="' . $cookieClass . '">Çerez Politikası</a><br><br><br>' . $arr["links"] . "<br><br><br>";
    } else {
        if ($cityName !== null) {
            return $arr["links"];
        } else {
            return $arr["links"];
        }
    }
}

// function create_file($arr, $fileName, $getTemplate = null, $createFolder = null)
function create_file($arrCreate)
{
    // Varsayılan ayarlar
    $arrDistrict = !isset($arrCreate["district"]) ? null : $arrCreate["district"];
    $arrDistrictName = !isset($arrCreate["district-name"]) ? null : $arrCreate["district-name"];
    $arrDistrictCityId = !isset($arrCreate["district-city-id"]) ? null : $arrCreate["district-city-id"];
    $arrCitySektors = !isset($arrCreate["city"]) ? null : $arrCreate["city"];
    $arrCityName = !isset($arrCreate["city-name"]) ? null : $arrCreate["city-name"];
    $isCitySlug = !isset($arrCreate["is-city-slug"]) ? null : $arrCreate["is-city-slug"];
    $arrCitySlug = !isset($arrCreate["city-slug"]) ? null : $arrCreate["city-slug"];
    $arrCreateSectorFiles = !isset($arrCreate["sector-files"]) ? false : $arrCreate["sector-files"];
    $arrCreateTemplate = !isset($arrCreate["template"]) ? null : $arrCreate["template"];
    $fileName = !isset($arrCreate["file-name"]) ? null : $arrCreate["file-name"];


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

            if ($arrDistrict !== null) {


                $breadcrumbSchema = '{
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{siteName}",
                    "item": "https://{domain}"
                  },{
                    "@type": "ListItem",
                    "position": 2,
                    "name": "{cityName}"
                    "item": "https://{domain}/{citySlug}"
                },{
                    "@type": "ListItem",
                    "position": 3,
                    "name": "{districtsName}"
                    "item": "https://{domain}/{districts}"
                },{
                    "@type": "ListItem",
                    "position": 4,
                    "name": "{sectorName}"
                    "item": "https://{domain}/{districts}/{sectorSlug}"
                }';


                $breadcrumb = '<div class="breadcrumb-text districts"><a href="/"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 6.453l9 8.375v9.172h-6v-6h-6v6h-6v-9.172l9-8.375zm12 5.695l-12-11.148-12 11.133 1.361 1.465 10.639-9.868 10.639 9.883 1.361-1.465z"/></svg></a></div>
                <div class="breadcrumb-text districts"><a href="/{citySlug}">{cityName}</a></div>
                <div class="breadcrumb-text districts"><a href="/{districts}">{districtsName}</a></div>
                <div class="breadcrumb-text districts"><a class="active">{sectorName}</a></div>';

                $siteMapContent = '{site}';
            } else if ($arrCityName !== null) {

                $breadcrumbSchema = '{
                        "@type": "ListItem",
                        "position": 1,
                        "name": "{siteName}",
                        "item": "https://{domain}"
                      },{
                        "@type": "ListItem",
                        "position": 2,
                        "name": "{cityName}"
                        "item": "https://{domain}/{citySlug}"
                    },{
                        "@type": "ListItem",
                        "position": 3,
                        "name": "{sectorName}"
                        "item": "https://{domain}/{citySlug}/{sectorSlug}"
                    }';

                $breadcrumb = '<div class="breadcrumb-text city"><a href="/">Anasayfa</a></div>
                    <div class="breadcrumb-text city"><a href="/{citySlug}">{cityName}</a></div>
                    <div class="breadcrumb-text city"><a class="active">{sectorName}</a></div>';
            } else {

                $breadcrumbSchema = '{
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{siteName}",
                    "item": "https://{domain}"
                  },{
                    "@type": "ListItem",
                    "position": 2,
                    "name": "{sectorName}"
                    "item": "https://{domain}/{sectorSlug}"
                }';


                $breadcrumb = '<div class="breadcrumb-text"><a href="/">Anasayfa</a></div>
                <div class="breadcrumb-text"><a class="active">{sectorName}</a></div>';
            }

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
            if ($arrCityName !== null) {
                if ($isCitySlug !== null) {
                    $sectorLinks[] = '<a title="' . $arrCityName . ' ' . $sector . '" href="/' . $isCitySlug . '/' . str_slug($arrCityName) . '/' . str_slug($sector) . '.html" class="' . $sectorActive . '">' . $sector . '</a>' . $cityLinks;
                } else {
                    $sectorLinks[] = '<a title="' . $arrCityName . ' ' . $sector . '" href="/' . str_slug($arrCityName) . '/' . str_slug($sector) . '.html" class="' . $sectorActive . '">' . $sector . '</a>' . $cityLinks;
                }
            } else {
                $sectorLinks[] = '<a title="' . $sector . '" href="/' . str_slug($sector) . '.html" class="' . $sectorActive . '">' . $sector . '</a>' . $cityLinks;
            }
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
                "city-name" => $arrCityName,
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

                if ($arrDistrict !== null) {
                    $districts = array_filter($dataDistrics, function ($item) use ($arrDistrictCityId) {
                        return $item["city_id"] == $arrDistrictCityId;
                    });
                    foreach ($districts as $district) {
                        $cityLinks[] = '<li><a href="/' . explode('-', $district["slug"])[0] . '/' . explode('-', $district["slug"])[1] . '/' . str_slug($fileName) . '.html">' . ucfirst_tr($district["name"]) . '</a></li>';
                        $cityName = explode(' ', $district["full_name"])[0];
                    }
                } else {
                    if ($arrCitySektors) {
                        $districts = array_filter($dataDistrics, function ($item) use ($arrDistrictCityId) {
                            return $item["city_id"] == $arrDistrictCityId;
                        });
                        foreach ($districts as $district) {
                            $cityLinks[] = '<li><a href="/' . $arrCitySlug . '/' . explode('-', $district["slug"])[1] . '/' . str_slug($fileName) . '.html">' . ucfirst_tr($district["name"]) . '</a></li>';
                            $cityName = explode(' ', $district["full_name"])[0];
                        }
                    } else {
                        foreach ($dataCities as $citys) {
                            $cityLinks[] = '<li><a href="/' . $citys["slug"] . '/' . str_slug($fileName) . '.html">' . ucfirst_tr($citys["name"]) . '</a></li>';
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
                "item" => $fileName
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{cityName}",
                "item" => $arrCityName
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{citySlug}",
                "item" => str_slug($arrCityName)
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{districts}",
                "item" => $arrDistrict
            ];
            $arrCreate["arr"]["replace"][] = [
                "variable" => "{districtsName}",
                "item" => $arrDistrictName
            ];

            $iconItems = [];
            shuffle($arrCreate["arr"]["icon-items"]);
            foreach ($arrCreate["arr"]["icon-items"] as $iconItem) {
                $iconItems[] = $iconItem;
            }

            $arrCreate["arr"]["replace"][] = [
                "variable" => "{iconItems}",
                "item" => implode(' ', $iconItems)
            ];

            $randomAuthorName = array_rand($arrCreate["arr"]["author-names"], 1);

            $arrCreate["arr"]["replace"][] = [
                "variable" => "{randomAuthorName}",
                "item" => $arrCreate["arr"]["author-names"][$randomAuthorName]
            ];


            if ($arrCreate["arr"]["location-pages"] === 'yes') {
                if ($arrCitySektors) {
                    $setCityLink = '<ul><li><a title="' . $cityName . '" href="/' . str_slug($arrCityName) . '">' . $cityName . '</a></li><ul>' . implode(' ', $cityLinks) . '</ul></ul>';
                } else if ($arrDistrict !== null) {
                    $setCityLink = '<ul><li><a title="' . $cityName . '" href="/' . str_slug($arrCityName) . '">' . $cityName . '</a></li><ul>' . implode(' ', $cityLinks) . '</ul></ul>';
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

        if ($arrCityName !== null) {
            if ($arrDistrict !== null) {
                //$locationName = $arrCityName . ' ' . $arrDistrictName . ' '; Eğer ilçe sayfasında başlıkta il ve ilçe yazsın istiyorsak
                $locationName = $arrDistrictName . ' ';
            } else {
                $locationName = $arrCityName . ' ';
            }
        } else {
            $locationName = "";
        }

        $arrCreate["arr"]["replace"][] = [
            "variable" => "{locationName}",
            "item" => $locationName
        ];

        $arrCreate["arr"]["replace"][] = [
            "variable" => "{companyName}",
            "item" => $arrCreate["arr"]["company-name"]
        ];

        $arrCreate["arr"]["replace"][] = [
            "variable" => "{faviconPath}",
            "item" => '/favicon.png'
        ];

        // Değişkenleri Replace edelim
        foreach ($arrCreate["arr"]["replace"] as $variable) {
            $template = str_replace($variable["variable"], $variable["item"], $template);
        }


        // Dosyayı oluşturalım
        if ($arrCreateSectorFiles) {
            if ($arrDistrict !== null) {
                $filePath = $arrCreate["file-name-slug"];
                if (!file_exists(getFile($arrDistrict, $arrCreate["arr"]["domain-replace"]))) {
                    mkdir(getFile($arrDistrict, $arrCreate["arr"]["domain-replace"]), 0777, true);
                }
            } else {
                $filePath = $arrCreate["file-name-slug"];
            }
        } else {
            $filePath = $arrCreate["file-name-slug"];
        }

        if($arrDistrictName !== 'Merkez'){
            $file = file_put_contents(getFile($filePath . '.html', $arrCreate["arr"]["domain-replace"]), $template);
            if (!$file) {
                msg($arrCreate["file-name-slug"] . '.html dosyası oluşurken bir hata oluştu!');
            }
        }


        if (!file_exists(__DIR__ . '/../sites/' . $arrCreate["arr"]["domain-replace"] . '/sitemaps')) {
            mkdir(__DIR__ . '/../sites/' . $arrCreate["arr"]["domain-replace"] . '/sitemaps', 0777, true);
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

    // SiteMap linklerini belirleyelim
    $siteMapLinks = [];
    $siteMapSectorLinks = [];

    // Şehir ve İlçe Json dosyalarını alalım
    $getCities = file_get_contents('../json/cities.json');
    $dataCities = json_decode($getCities, true);
    $getDistricts = file_get_contents('../json/districts.json');
    $dataDistrics = json_decode($getDistricts, true);

    // Sektörleri oluşturalım
    foreach ($arr["sectors"] as $sector) {
        create_file([
            "sector-files" => true,
            "template" => "service",
            "arr" => $arr,
            "file-name" => $sector,
            "file-name-slug" => str_slug($sector)
        ]);
        $siteMapSectorLinks[] = str_slug($sector);
    }

    if ($arr["location-pages"] === 'yes') {

        // Şehirleri döngüden geçirelim
        foreach ($dataCities as $city) {

            $siteMapCityLinks = [];
            $siteMapSpecialCityLinks = [];

            // Şehire ait ilçeleri bulalım
            $districts = array_filter($dataDistrics, function ($item) use ($city) {
                return $item["city_id"] == $city["id"];
            });


            // Şehrin linkini ekleyelim
            $siteMapCityLinks[] = $city["slug"];
            $siteMapSpecialCityLinks[] = $city["slug"];


            // Şehirin sektör linkini ekliyoruz
            $siteMapCityLinks[] = $city["slug"];
            foreach ($siteMapSectorLinks as $sectorLinks) {
                $siteMapSpecialCityLinks[] = $city["slug"] . '/' . $sectorLinks;
            }

            // Şehirin ileçeleri linkini ekliyoruz
            foreach ($districts as $district) {
                $siteMapSpecialCityLinks[] = $city["slug"] . '/' . explode('-', $district["slug"])[1];
            }

            // Şehirin ilçelerinin sektörleri linkini ekliyoruz
            foreach ($siteMapSectorLinks as $sectorLinks) {
                foreach ($districts as $district) {
                    $siteMapSpecialCityLinks[] = $city["slug"] . '/' . explode('-', $district["slug"])[1] . '/' . $sectorLinks;
                }
            }

            $siteMapLinks = [];
            foreach ($siteMapSpecialCityLinks as $specialLink) {
                $siteMapLinks[] = [
                    "loc" => "https://" . $arr["domain"] . "/" . $specialLink . "/index.html",
                    "changefreq" => "monthly",
                    "priority" => "0.8"
                ];
            }

            siteMapCreate([
                "links" => $siteMapLinks,
                "domain-replace" => $arr["domain-replace"],
                "file-name" => str_slug($city["name"]) . ""
            ]);


            // Şehrin klasörü yoksa oluşturalım
            if (!file_exists(getFile($city["slug"], $arr["domain-replace"]))) {
                mkdir(getFile($city["slug"], $arr["domain-replace"]), 0777, true);
            }

            // Dosyaları oluşturalım
            create_file([
                "arr" => $arr,
                "template" => "index",
                "city-name" => $city["name"],
                "file-name-slug" => $city["slug"] . '/index'
            ]);


            // Sektörleri oluşturalım
            foreach ($arr["sectors"] as $sector) {
                create_file([
                    "city" => true,
                    "district-city-id" => $city["id"],
                    "city-name" => $city["name"],
                    "city-slug" => $city["slug"],
                    "sector-files" => true,
                    "template" => "service",
                    "arr" => $arr,
                    "file-name" => $sector,
                    "file-name-slug" => $city["slug"] . '/' . str_slug($sector)
                ]);
            }

            foreach ($districts as $district) {
                // İlçelerin klasörü yoksa oluşturalım
                $districtSlug = explode("-", $district["slug"]);
                if (!file_exists(getFile($city["slug"] . '/' . $districtSlug[1], $arr["domain-replace"]))) {
                    mkdir(getFile($city["slug"] . '/' . $districtSlug[1], $arr["domain-replace"]), 0777, true);
                }

                create_file([
                    "arr" => $arr,
                    "template" => "index",
                    "city-name" => $district["name"],
                    "is-city-slug" => $city["slug"],
                    "file-name-slug" => $city["slug"] . '/' . $districtSlug[1] . '/index'
                ]);


                // Sektörleri oluşturalım
                foreach ($arr["sectors"] as $sector) {
                    create_file([
                        "city-name" => $city["name"],
                        "district" => $city["slug"] . '/' . $districtSlug[1],
                        "district-name" => $district["name"],
                        "district-city-id" => $district["city_id"],
                        "sector-files" => true,
                        "template" => "service",
                        "arr" => $arr,
                        "file-name" => $sector,
                        "file-name-slug" => $city["slug"] . '/' . $districtSlug[1] . '/' . str_slug($sector)
                    ]);
                }
            }
        }
    }

    // Faviconu kayıt edelim
    if (isset($_FILES["site-favicon"])) {
        $path = __DIR__ . '/../sites/' . $arr["domain-replace"] . '/favicon.png';
        if (!file_exists($path)) {
            $result = move_uploaded_file($_FILES["site-favicon"]["tmp_name"], $path);
            if (!$result) {
                echo '<div class="message error">Favicon oluşturulurken bir hata oluştu.</div>';
            }
        }
    }

    // Index için linklerini belirleyelim
    $siteMapLinks = [
        [
            "loc" => "https://" . $arr["domain"],
            "changefreq" => "daily",
            "priority" => "1.0"
        ], [
            "loc" => "https://" . $arr["domain"] . "/about.html",
            "changefreq" => "monthly",
            "priority" => "0.8"
        ], [
            "loc" => "https://" . $arr["domain"] . "/contact.html",
            "changefreq" => "monthly",
            "priority" => "0.8"
        ], [
            "loc" => "https://" . $arr["domain"] . "/cookie-policy.html",
            "changefreq" => "monthly",
            "priority" => "0.7"
        ], [
            "loc" => "https://" . $arr["domain"] . "/privacy-policy.html",
            "changefreq" => "monthly",
            "priority" => "0.7"
        ],
    ];

    foreach ($siteMapSectorLinks as $sectorLinks) {
        $siteMapLinks[] = [
            "loc" => "https://" . $arr["domain"] . "/" . $sectorLinks . ".html",
            "changefreq" => "monthly",
            "priority" => "1.0"
        ];
    }

    siteMapCreate([
        "links" => $siteMapLinks,
        "domain-replace" => $arr["domain-replace"],
        "file-name" => "index"
    ]);

    $siteMapLinks = [];
    if (!isset($siteMapCityLinks)) {
        $arrSiteMapCityLinks = [];
        foreach ($dataCities as $city) {
            $arrSiteMapCityLinks[] = $city["slug"];
        }
    } else {
        $arrSiteMapCityLinks = $siteMapCityLinks;
    }
    $siteMapXmlLinks = [
        ["xml-links" => "index-sitemap.xml"]
    ];
    foreach ($arrSiteMapCityLinks as $cityLinks) {
        $siteMapLinks[] = [
            "loc" => "https://" . $arr["domain"] . "/" . $cityLinks . "/index.html",
            "changefreq" => "monthly",
            "priority" => "0.9"
        ];
        $siteMapXmlLinks[] = ["xml-links" => $cityLinks . '-sitemap.xml'];
    }

    siteMapCreate([
        "links" => $siteMapLinks,
        "domain-replace" => $arr["domain-replace"],
        "xml-links" => true
    ]);

    
    $robotsTxtContent = 'User-agent: *
Allow: /
Sitemap: /sitemap.xml';

    $createRobotsTxt = file_put_contents(__DIR__ . '/../sites/' . $arr["domain-replace"] . '/robots.txt' , $robotsTxtContent);

    if(!$createRobotsTxt){
        msg('Robots.txt dosyası oluşturulurken bir hata oluştu!');
    }


    echo zipper($arr["domain-replace"]);
};
