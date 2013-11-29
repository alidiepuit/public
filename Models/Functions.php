<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

function pr($data, $die = true)
{
	$trace = debug_backtrace();
	$caller = array_shift($trace);
	echo '<pre>';
	echo "called by [" . $caller['file'] . "] line: " . $caller['line'] . "\n";
	print_r($data);
	if ($die) {
		exit;
	}
}
class Log {
	public static function write($content) {
		$handle = fopen('log.txt', 'a+');
		fwrite($handle, $content . "\n");
		fclose($handle);
	}
}
class Admin_Model_SeoLink
{
	function vietnamese_permalink ($title, $replacement = '-')
    {
        /* 	Replace with "-"
		*/
        //$replacement = '-';
        $map = array ();
        $quotedReplacement = preg_quote( $replacement, '/' );
        $default = array (
				'/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
                '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
                '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
                '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
                '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
                '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
                '/đ|Đ/' => 'd',
                '/ç/' => 'c',
                '/ñ/' => 'n',
                '/ä|æ/' => 'ae',
                '/ö/' => 'oe',
                '/ü/' => 'ue',
                '/Ä/' => 'Ae',
                '/Ü/' => 'Ue',
                '/Ö/' => 'Oe',
                '/ß/' => 'ss',
                '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
                '/\\s+/' => $replacement,
                sprintf( '/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement ) => ''
        );
        //Some URL was encode, decode first
        $title = urldecode( $title );
        $map = array_merge( $map, $default );
        return strtolower( preg_replace( array_keys( $map ), array_values( $map ), $title ) );
    }
}

class Admin_Model_DbTable_GameManufacturer
{
	public function findManufacturer($name)
	{
		return $name;
	}
}

class Admin_Model_DbTable_GameCategory
{
	public function findCategory($name)
	{
		$a = array();
		$a['giai-tri'] = 2;
		$a['hanh-dong-nhap-vai'] = 3;
		$a['tri-tue'] = 5;
		$a['danh-bai'] = 14;
		$a['dua-xe'] = 12;
		$a['the-thao'] = 12;
		$a['chay-vo-tan'] = 20;
		$a['chien-luoc-mo-phong'] = 21;
		return isset($a[$name]) ? $a[$name] : 100;
	}
}

class Admin_Model_Tool
{
    public function createFile($link, $filename)
    {
        $ch = curl_init();
        $url = $link;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)');
        curl_setopt($ch, CURLOPT_COOKIE, "HstCfa1430924=1369822842086; HstCmu1430924=1369822842086; __gads=ID=7c87fd1e3ff7a917:T=1369822914:S=ALNI_MZ0U_ux9RRBjUAzZbiMEsHTVOor1Q; MLRV_71430924=1369879567951; MLR71430924=1369880032000; btpop2=btpop2 Popunder; btpop1=btpop1 Popunder; HstCla1430924=1369884230479; HstPn1430924=2; HstPt1430924=6; HstCnv1430924=3; HstCns1430924=3; __unam=fe1c7ee-13eefceab83-3a198ad7-5; __utma=3646965.144129990.1369822836.1369879520.1369884223.3; __utmb=3646965.3.10.1369884223; __utmc=3646965; __utmz=3646965.1369822836.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __qca=P0-853667030-1369884232031");
        $output = curl_exec($ch);

        $handle = fopen($filename, 'w');
        fwrite($handle, $output);
        fclose($handle);

        $info = curl_getinfo($ch);
        curl_close($ch);

        $fileSize = array();
        if (file_exists($filename)) {
            $fileSize = getimagesize($filename);
        }
        if (empty($fileSize)) {
            return false;
        }
        return true;
    }

    function getContent($link)
    {
        $link = trim($link);
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $link);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 200);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        $headers = array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.8) Gecko/20061025 Firefox/1.5.0.8");
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);
    
        return $query;
    }
    
    public function createFileZip($filename)
    {
        $basename = basename($filename);
        $filearray = explode(".",$basename);
        $count = count($filearray);
        $pos = $count - 1;
        $filearray[$pos] = strtolower($filearray[$pos]);
        $filearray[$count] = 'zip';
        $file = implode(".",$filearray);
        $folder = uniqid('ios_');

        if (!file_exists(STATIC_PATH . '/files/extract/' . $folder)) {
            mkdir(STATIC_PATH . '/files/extract/' . $folder, 0777, true);
        }
        $dstfile = STATIC_PATH . '/files/extract/' . $folder  . '/' . $file;
        copy($filename, $dstfile);
        return $dstfile;
    }

    public function extractZip($file)
    {
        $folder = uniqid('ios_');
        $des = STATIC_PATH . '/files/extract/' . $folder;
        $zip = new ZipArchive;
        if ($zip->open($file) === TRUE) {
            $zip->extractTo($des);
            $zip->close();
            return $des;
        }
        return false;
    }

    private $_listFile = null;
    public function findPList($folder)
    {
    	if ($handle = opendir($folder)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && is_dir($folder . '/' . $entry)) {
                	$this->findPList($folder . '/' . $entry);
                };
                if (preg_match('/.plist/', $entry)) {
                    $this->_listFile[] = $folder . '/' . $entry;
                }
            }
//            close($handle);
        }
    }

    function deleteDirectory($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!$this->deleteDirectory($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!$this->deleteDirectory($dir . "/" . $item)) return false;
            };
        }
        return @rmdir($dir);
    }

    public function getInfo($folder)
    {
        include_once(LIBRARY_PATH . '/CFPropertyList/CFPropertyList.php');
        $this->_listFile = array();
        $this->findPList($folder);
        foreach($this->_listFile as $file) {
            $plist = new CFPropertyList($file);
            $plist_data = $plist->toArray();

            $bundleId = $this->findKey('bundleid', $plist_data);
            $bundleVersion = $this->findKey('bundleversion', $plist_data);

            if (!empty($bundleId) && !empty($bundleVersion)) {
            	return array('bundleId' => $bundleId, 'bundleVersion' => $bundleVersion);
            }
        }
        return null;
    }

    private function findKey($pattern, $arr)
    {
    	foreach($arr as $key => $val) {
            $key = strtolower($key);
    		if (preg_match('/' . $pattern . '/', $key)) {
    			return $val;
    		}
    	}
        return '';
    }

    public function getBundleInfo($filename)
    {
        $filename = STATIC_PATH . '/' . urldecode($filename);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $info = array();
        if ($ext == 'ipa') {
        	$folder = self::extractZip($filename);
            $info = self::getInfo($folder);

            //delete tree after execute
            $this->deleteDirectory($folder);
        }
        return $info;
    }

    public function contentPlist($game, $nameGame, $bundleId = '', $bundleVersion = '')
    {
		$helper = new My_Helper_View_Download();
		$url = $helper->Download($game);
        $plist = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">' .
            '<plist version="1.0">' .
            '<dict>' .
                '<key>items</key>' .
                '<array>' .
                    '<dict>' .
                        '<key>assets</key>' .
                        '<array>' .
                            '<dict>' .
                                '<key>kind</key>' .
                                '<string>software-package</string>' .
                                '<key>url</key>' .
                                '<string>' . $url . '</string>' .
                            '</dict>' .
                        '</array>' .

                        '<key>metadata</key>' .
                        '<dict>' .
                            '<key>bundle-identifier</key>' .
                            '<string>' . $bundleId . '</string>' .
                            '<key>bundle-version</key>' .
                            '<string>' . $bundleVersion  . '</string>' .
                            '<key>kind</key>' .
                            '<string>software</string>' .
                            '<key>subtitle</key>' .
                            '<string>' . $nameGame . '</string>' .
                            '<key>title</key>' .
                            '<string>' . $nameGame . '</string>' .
                        '</dict>' .
                    '</dict>' .
                '</array>' .
            '</dict>' .
            '</plist>';

        return $plist;
    }

	/**
	 * Add watermark to image
	 * @param pos array: position of stamp (top, bottom)
	 * @param image link to image which want to add watermark
	 * @return void
	 */
	public function addWatermark($pos, $image)
	{
		//init valid image
		$image = trim($image);
		$pathImg = PUBLIC_PATH . '/static/' . $image;
		
		if (!file_exists($pathImg)) {
			return 0;
		}
		
        //init image
        $src = $this->initImage($pathImg);
		if (empty($src)) {
			return 0;
        }
		
		// Copy the stamp image onto our photo using the margin offsets and the photo 
		// width to calculate positioning of the stamp. 
		if (isset($pos['bottom'])) {
			$marge_right = 0;
			$marge_bottom = 10;
			
			// Load the stamp and the photo to apply the watermark to
			$stamp = imagecreatefromjpeg(PUBLIC_PATH . '/static/image/default/images/watermark_220x70.jpg');
			
			$sx = imagesx($stamp);
			$sy = imagesy($stamp);
			
			$x = imagesx($src) - $sx - $marge_right;
			$y = imagesy($src) - $sy - $marge_bottom;
			
			imagecopy($src, $stamp, $x, $y, 0, 0, $sx, $sy);
		}
		
		if (isset($pos['top'])) {
			$marge_left = 10;
			$marge_top = 10;
			
			// Load the stamp and the photo to apply the watermark to
			$stamp = imagecreatefromjpeg(PUBLIC_PATH . '/static/image/default/images/watermark_160x40.jpg');
			
			$sx = imagesx($stamp);
			$sy = imagesy($stamp);
			
			$x = $marge_left;
			$y = $marge_top;
			
			imagecopy($src, $stamp, $x, $y, 0, 0, $sx, $sy);
		}
		
		$this->saveImage($src, $pathImg);
		
		// Output and free memory
		imagedestroy($src);
	}

	public function addWatermarkLogo($image)
	{
		//path image
		$image = trim($image);
		$pathImg = PUBLIC_PATH . '/static/' . $image;
		if (!file_exists($pathImg)) {
			return 0;
		}
		
		//init image
        $src = $this->initImage($pathImg);
		if (empty($src)) {
			return 0;
        }

		$marge_left = 3;
		$marge_bottom = 0;
		
		// Load the stamp and the photo to apply the watermark to
		$stamp = imagecreatefrompng(PUBLIC_PATH . '/static/image/default/images/watermark_logo.png');
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
		
		$x = $marge_left;
		$y = imagesy($src) - $sy - $marge_bottom;
		
		imagecopy($src, $stamp, $x, $y, 0, 0, $sx, $sy);
		
		$this->saveImage($src, $pathImg);
		
		// Output and free memory
		imagedestroy($src);
	}

	public function initImage($pathImg)
	{
		// $type = exif_imagetype($pathImg);
		$info = getimagesize($pathImg);
		$type = $info['mime'];
		// pr($type);
		//png
        if ($type == 'image/png') {
            return imagecreatefrompng($pathImg);
        }
		
    	//jpeg || jpg
		if ($type == 'image/jpeg') {
            return imagecreatefromjpeg($pathImg);
        }

		//bmp
		if ($type == 'image/bmp') {
            return imagecreatefromwbmp($pathImg);
        }
		
		//gif
		if ($type == 'image/gif') {
            return imagecreatefromgif($pathImg);
        }
		
		return null;
	}
	
	public function saveImage($src, $pathImg)
	{
		$info = getimagesize($pathImg);
		$type = $info['mime'];
		
		//png
        if ($type == 'image/png') {
            imagepng($src, $pathImg);
        }
		
    	//jpeg || jpg
		if ($type == 'image/jpeg') {
            imagejpeg($src, $pathImg);
        }
		
		//bmp
		if ($type == 'image/bmp') {
            imagewbmp($pathImg);
        }
		
		//gif
		if ($type == 'image/gif') {
            imagegif($src, $pathImg);
        }
	}
}