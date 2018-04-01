<?php


class Security_defaults
{
    // траслитерация
    function GetInTranslit($string) 
    {
        $replace=array(
        "'"=>"",
        "`"=>"",
        "а"=>"a","А"=>"a",
        "б"=>"b","Б"=>"b",
        "в"=>"v","В"=>"v",
        "г"=>"g","Г"=>"g",
        "д"=>"d","Д"=>"d",
        "е"=>"e","Е"=>"e",
        "ж"=>"zh","Ж"=>"zh",
        "з"=>"z","З"=>"z",
        "и"=>"i","И"=>"i",
        "й"=>"y","Й"=>"y",
        "к"=>"k","К"=>"k",
        "л"=>"l","Л"=>"l",
        "м"=>"m","М"=>"m",
        "н"=>"n","Н"=>"n",
        "о"=>"o","О"=>"o",
        "п"=>"p","П"=>"p",
        "р"=>"r","Р"=>"r",
        "с"=>"s","С"=>"s",
        "т"=>"t","Т"=>"t",
        "у"=>"u","У"=>"u",
        "ф"=>"f","Ф"=>"f",
        "х"=>"h","Х"=>"h",
        "ц"=>"c","Ц"=>"c",
        "ч"=>"ch","Ч"=>"ch",
        "ш"=>"sh","Ш"=>"sh",
        "щ"=>"sch","Щ"=>"sch",
        "ъ"=>"","Ъ"=>"",
        "ы"=>"y","Ы"=>"y",
        "ь"=>"","Ь"=>"",
        "э"=>"e","Э"=>"e",
        "ю"=>"yu","Ю"=>"yu",
        "я"=>"ya","Я"=>"ya",
        "і"=>"i","І"=>"i",
        "ї"=>"yi","Ї"=>"yi",
        "є"=>"e","Є"=>"e"
        );
        return $str=iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
    }
    
    public function getDefaultRazdel()
    {        
        // массив допустимых параметров ввода 
        $array_razdels = array(
            0 => 'lookbook',
            1 => 'dress-kod',
            2 => 'legkaya-odezhda',
            3 => 'verhnyaya-odezhda',
            4 => 'vechernyaya-i-svadebnaya-moda',
            5 => 'obuv-i-aksessuary',

        );
        return $array_razdels;
    }   
    
    public function getDefaultPosts()
    {
        // массив допустимых параметров ввода
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql = "SELECT `statyi`.`m4_urlname` as `name`
               FROM `statyi`";
        $res = $db->query($sql)->fetchall();

        if (is_array($res)) {
            foreach ($res as $key => $value) {
                $array_posts[] = $value['name'];
            }
        }



//        $array_posts = [
//            0  => 'jenskie-ukrasheniya-dlya-leta-neobichnie-idei',
//            1  => 'jenstvenaya-odejda-dlya-ofica',
//            2  => 'jenstvenaya-odejda-dlya-ofica-chasty-2',
//            3  => 'chernie-vechernie-platya-iziskanya-roskoshy-dlya-osobix-sluchaev',
//            4  => 'cherno-belie-vechernie-obrazi-naryadniye-bluzki-bruki-i-ubki',
//            5  => 'vse-otenki-zelenogo-v-odejde',
//            6  => 'roskoshnie-platya-s-cvetami-dlya-sozdaniya-jenstvenogo-obraza',
//            7  => 'nejnosty-vesni-v-vashem-garderobe-s-chem-sochetaty-zelenie-platya-bluzki-ubki',
//            8  => 'ottenki-sinego-v-vashem-garderobe',
//            9  => 'platya-rubashka-2014-idealynoe-reshenie-dlya-leta',
//            10 => 'yarkie-akcenti-leta-modniye-playtya-i-mnogoe-drugoe-dlya-vashego-jenstvenogo-obraza',
//            11 => 'svadebnie-platya-v-stile-retro-vintajniy-shik-dlya-vashego-prazdnika',
//            12 => 'oseny-v-ofise-jenstvenie-pritalinie-platya',
//            13 => 'modnie-pritalinie-platya-actualnie-modely-oseny-2014',
//            14 => 'modniy-pritalinie-platya-oseny-2014-s-cem-nosity',
//            15 => 'pritalennye-vechernie-platya-dlya-zhenstvennyh-krasavic',
//            16 => 'pritalennye-svadebnye-platya-aktualnye-fasony-dlya-modnoy-nevesty',
//            17 => 'zhenskiy-tvidovyy-kostyum-izyskannaya-klassika-snova-v-mode',
//            18 => 'osenniy-obraz-2014-zhenstvennyy-casual',
//            19 => 'osenniy-obraz-2014-notki-zhenstvennosti',
//            20 => 'osenniy-obraz-2014-uyutnyy-kantristil',
//            21 => 'odezhda-pastelnyh-tonov-nezhnye-ottenki-vesny-v-vashem-garderobe',
//            22 => 's-chem-nosit-lyusitovyy-zelenyy-2015',
//            23 => 'modnye-vesennie-palto-2016-kak-pravilno-vybrat',
//            24 => 's-chem-nosit-polosku-letom',
//            25 => 'modnyy-trend-letnie-platya-s-otkrytymi-plechami',
//            26 => 'stilnyy-rozovyy-cvet-v-odezhde',
//            27 => 's-chem-nosit-krasnyy-v-odezhde-yarkie-obrazy-na-kazhdyy-den',
//            28 => 's-chem-nosit-kozhanuyu-kurtku-idei-dlya-modnic',
//            29 => 's-chem-nosit-vysokie-sapogi',
//            30 => 'uteplyaemsya-s-umom-kak-vybrat-udachnyy-sviter',
//            31 => 'kak-vybrat-sviter-dlya-polnyh',
//            32 => 'krossovki-pod-yubku-stilnyy-vybor-vesny',
//            33 => 'kak-vybrat-bomber-dlya-zhenstvennogo-obraza',
//            34 => 'nebanalnye-sovety-s-chem-nosit-bomber-devushke',
//            35 => 'svadebnoe-plate-v-stile-minimalizm',
//            36 => 'kak-vybrat-idealnoe-svadebnoe-plate-dlya-polnyh',
//        ];

        return $array_posts;
    }
    
    public function getDefaultTegs()
    {
        // массив допустимых параметров ввода
        $tegs = new Application_Model_Tegs();
        $rawData = $tegs->fetchall();

        $tegsList = array();
        if ($rawData === (array)$rawData && count($rawData) > 0) {
            foreach ($rawData as $key => $value) {
                $tempMass = explode(';', $value['tegs_id']);
                foreach ($tempMass as $index => $name) {
                    if ($name) {
                        $tegsList[] = $name;
                    }
                }

            }
        }
        $tegsList = array_unique($tegsList);

        return $tegsList;
    }
    
    public function checkRazdelName($str){
        $defaultRazdesl = self::getDefaultRazdel();
        
        if (in_array($str, $defaultRazdesl)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function checkPostName($str){
        $defaultPosts = self::getDefaultPosts();
        
        if (in_array($str, $defaultPosts)) {
            return true;
        } else {
            return false;
        }
    }

    public function addTegsInfoFromPost() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("SET NAMES 'utf8'");
        $sql="SELECT `m5`, `tegs_id` FROM `statyi`";
        $ress =  $db->query($sql)->fetchAll();
        foreach ($ress as $k => $v) {
            $massName[] = $v['m5'];
            $massUrl[] = $v['tegs_id'];
        }
        $uniq = array();
        foreach ($massName as $k => $v) {
            $strMass = explode(',', $v);
            $urlMass = explode(';', $massUrl[$k]);
            unset($urlMass[0]);
            array_pop($urlMass);
            foreach ($strMass as $in => $va) {
                $uniq[$urlMass[++$in]] = $va;
            }
        }
        $uniq = array_unique($uniq);
        $sql = 'INSERT INTO `new_tegi` (`teg`,`teg_name`) VALUES ';
        foreach ($uniq as $k => $v) {
            $sql .= "('" . $v ."','" . $k . "'),";
        }
        $sql .= ';';

        die(var_dump($sql));
    }

    /**
     * Get header menu - razdels with sub razdels
     *
     * @return array
     */
    public function getHeaderMenu()
    {
        $table = new Zend_Db_Table('razdels_info');
        $select = $table->select()
            ->from('razdels_info')
            ->setIntegrityCheck(false)
            ->joinLeft(
                'sub_razdels',
                'razdels_info.id = sub_razdels.parent_id',
                [
                    'parent_id as parent_id',
                    'name as sub_name',
                    'url as sub_url',
                    'seo_description as sub_seo_description',
                    'seo_keywords as sub_seo_keywords',
                ]
            )
        ;

        $result = $table->fetchAll($select);
        if ($result) {
            $result = $result->toArray();
        }

        return $result;
    }

    public function getCurrentRazdelAndPostName()
    {
        $result = [];
        $uriParam = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $uriArray = explode('/', $uriParam);
        if (is_array($uriArray)) {
            foreach ($uriArray as $key => $value) {
                if ($value) {
                    $result[] = $value;
                }
            }
        }

        $data = [
            'razdelUrl' => array_shift($result),
            'postUrl' => array_shift($result),
        ];

        return $data;
    }

    public function getCurrentRazdelTags($razdelname)
    {
        $result = [];
        if ($razdelname) {
            $table = new Zend_Db_Table('razdels_info');
            $select = $table->select()
                ->from('razdels_info', ['url as currenRazdelUrl'])
                ->setIntegrityCheck(false)
                ->joinRight(
                    'sub_razdels',
                    'razdels_info.id = sub_razdels.parent_id',
                    [
                        'name as tagName',
                        'url as  tagUrl',
                    ]
                )
                ->where('razdels_info.url =?', $razdelname)
            ;
            $result = $table->fetchAll($select);
            if ($result) {
                $result = $result->toArray();
            }
        }


        return $result;
    }
}

