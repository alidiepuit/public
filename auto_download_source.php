<?php
	include('/Models/Config.php');
	include('/Models/Functions.php');
	
	try {
		Log::write('start|' . date('d-m-Y H:i') . '|' . MAINLINK . '|' . OS);
		exe();
	} catch (Exception $e) {
		Log::write($e->getMessage());
	}
	Log::write('done|' . date('d-m-Y H:i'));
	pr('done');
	
	function exe() {
		$toolModel = new Admin_Model_Tool();
		$mainLink = MAINLINK;
		
		if (PHP_SAPI == 'apache2handler') {
			$progress = new ProgressBar();
			$progress->render();
		}
		
		$content = $toolModel->getContent($mainLink);
		
		//get list of page
		$page = new DOMDocument();
		@$page->loadHTML($content);
		$finder = new DomXPath($page);
		$classname="PageNavId";
		$nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@id), ' '), ' $classname ')]");
		$p = $nodesPage->item(0);
		$listPage = $p->getElementsByTagName('li');
		if (isset($listPage->item($listPage->length-3)->nodeValue)) {
			$totalPage = (int)$listPage->item($listPage->length-3)->nodeValue;
		} else {
			$totalPage = 1;
		}
		
		$formatLink = $mainLink . 'new/%d/';
		$osId = OS;
		$count = 0;
		$totalCount = 24*$totalPage;
		$totalSize = 0;
		//for each page
		for($i = PAGE_BEGIN; $i <= $totalPage; $i++) {
			$link = sprintf($formatLink, $i);
			
			$content = $toolModel->getContent($link);
		
			//get list of game
			$page = new DOMDocument();
			@$page->loadHTML($content);
			$finder = new DomXPath($page);
			$classname="app-box";
			$nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
			
			//for each game
			for($j = 0; $j < $nodesPage->length; $j++) {
				$node = $nodesPage->item($j);
				$listNodeA = $node->getElementsByTagName('a');
				$nodeA = $listNodeA->item(0);
				
				//link game
				$link = $nodeA->getAttribute('href');
				$info = getInfo($osId, $link);
				
				//pr($info);
				writeSql($info, $link);
				
				$count++;
				Log::write($info['gameName'] . '|page:' . $i);
				if (PHP_SAPI == 'apache2handler') {
					$progress->setProgressBarProgress($count*100/$totalCount, $info['gameName'] . ' ' . ' page:' . $i);
				}
				
				if (LIMIT && $count>=LIMIT) {
					break;
				}
			}
			if (LIMIT && $count>=LIMIT) {
				break;
			}
		}
		if (PHP_SAPI == 'apache2handler') {
			$progress->setProgressBarProgress(100);
		}
	}
	
	function writeSql($info, $link) {	
		$handle = fopen(FILENAME, 'a+');
		$str = 'INSERT INTO `game_temp` VALUES (\'\',';
		$str .= '\'' . $info['gameName'] . '\',';
		$str .= '\'' . $info['gameImage'] . '\',';
		$str .= '\'' . $info['description'] . '\',';
		$str .= '\'\',';
		$str .= $info['catGameId'] . ',';
		$str .= '\'' . $info['manufacturerId'] . '\',';
		$str .= '0,0,0,1,' . TIME . ',' . '\'' . $info['gameNameSlug'] . '\',' . TIME . ',1,\'\',\'\',\'\',\'\',\'\',0,' . $info['osId'] . ',';
		$str .= '\'' . $info['source'] . '\',';
		$str .= '\'' . $info['source'] . '\',';
		$str .= '\'' . $info['size'] . '\',';
		$str .= '\'' . $info['version'] . '\',';
		$str .= '\'\',\'\',' . '\'' . $link . '\',\'' . json_encode($info['screenShot']) . '\');' . "\n";
		fwrite($handle, $str);
		fclose($handle);
	}
	
	function getInfo($osId, $link)
    {
        $toolModel = new Admin_Model_Tool();
        $gameCateModel = new Admin_Model_DbTable_GameCategory();
        $gameManuModel = new Admin_Model_DbTable_GameManufacturer();
        $seo = new Admin_Model_SeoLink();
        
        $content = $toolModel->getContent($link);
        
        //get information of game
        $page = new DOMDocument();
        @$page->loadHTML($content);
        $finder = new DomXPath($page);
        $classname="app-info";
        $nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $p = $nodesPage->item(0);
        $listInfo = $p->getElementsByTagName('p');
        
        $info = array();
		$info['osId'] = $osId;
        //name
        $info['gameName'] = trim($listInfo->item(0)->nodeValue);
        $info['gameNameSlug'] = $seo->vietnamese_permalink($info['gameName']);
        //category
        $info['catGameName'] = trim($listInfo->item(3)->nodeValue);
        $info['catGameId'] = $gameCateModel->findCategory($seo->vietnamese_permalink($info['catGameName']));
        //manufacturer
        $info['manufacturer'] = trim($listInfo->item(4)->nodeValue);
        $a = explode(':', $info['manufacturer']);
        $info['manufacturerName'] = trim($a[1]);
        $info['manufacturerId'] = $gameManuModel->findManufacturer($info['manufacturerName']);
        //version
        $info['version'] = trim($listInfo->item(6)->nodeValue);
        $a = explode(':', $info['version']);
        $info['version'] = trim($a[1]);
        //size
        $info['size'] = trim($listInfo->item(7)->nodeValue);
        $a = explode(':', $info['size']);
        $info['size'] = trim($a[1]);
        
        //get description
        // description-detail
        $classname="description-detail";
        $nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@id), ' '), ' $classname ')]");
        $info['description'] = @trim($nodesPage->item(0)->nodeValue);
        $info['description'] = str_replace('choapp.vn', '<a target="_blank" href="http://game3.vn">game3.vn</a>', $info['description']);
        
        //screen shot
        //get information of game
        $classname="AsScreenshot";
        $nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $info['screenShot'] = array();
        $path = createFolderLeech('screenshot');
        $unique = uniqid();
        $i = 0;
        foreach($nodesPage as $node) {
            $link = $node->getAttribute('href');
            $fileName = $info['gameNameSlug'] . '_' . $unique . '_' . $i . '.jpg';
            $hasFile = $toolModel->createFile($link, $path . $fileName);
            if (isset($hasFile) && $hasFile) {
                $info['screenShot'][] = 'files/uploads/files/screenshot/' . date('Ymd') . '/' . $fileName;
                $i++;
            }
        }
        
        //game image
        $classname="app-image";
        $nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $src = $nodesPage->item(0)->getAttribute('src');
        $path = createFolderLeech('gameImage');
        $fileName = $info['gameNameSlug'] . '_' . $unique . '_avatar.jpg';
        $toolModel->createFile($src, $path . $fileName);
        $info['gameImage'] = 'files/uploads/files/gameImage/' . date('Ymd') . '/' . $fileName;
        
        //game source
        $classname="download-by-com Download";
        $nodesPage = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $id = $nodesPage->item(0)->getAttribute('id');
        $link = getLinkSource($id);
        $path = createFolderLeech('source_cpu');
        $ext = $osId == ANDROID ? '.apk' : '.ipa';
        $fileName = $info['gameNameSlug'] . $ext;
        $toolModel->createFile($link, $path . $fileName);
        $info['source'] = 'files/uploads/files/source_cpu/' . date('Ymd') . '/' . $fileName;
		
        return $info;
    }

    function createFolderLeech($type)
    {
        $path = 'public/static/files/uploads/files/' . $type . '/';
        if (!file_exists($path)) {
            mkdir($path, 0771, true);
        }
        $path .= date('Ymd') . '/';
        if (!file_exists($path)) {
            mkdir($path, 0771, true);
        }
        return $path;
    }
    
    function getLinkSource($id) {
        $link = 'http://choapp.vn/mg/download/check?version_id=' . $id;
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $link);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 200);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        $headers = array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.8) Gecko/20061025 Firefox/1.5.0.8");
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $headers);
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);
        $data = json_decode($query, true);
        return 'http://choapp.vn/mg/download/downloadfile/source/' . $data['source'];
    }
	
	class ProgressBar {
        var $percentDone = 0;
        var $pbid;
        var $pbarid;
        var $tbarid;
        var $textid;
        var $decimals = 2;
 
        function __construct($percentDone = 0) {
                ob_end_clean();
                ini_set('output_buffering', '0');
                $this->pbid = 'pb';
                $this->pbarid = 'progress-bar';
                $this->tbarid = 'transparent-bar';
                $this->textid = 'pb_text';
                $this->percentDone = $percentDone;
        }
 
        function render() {
                print($this->getContent());
                $this->flush();
                $this->setProgressBarProgress(0);
        }
 
        function getContent() {
                $this->percentDone = floatval($this->percentDone);
                $percentDone = number_format($this->percentDone, $this->decimals, '.', '') .'%';
                $content = '<div id="'.$this->pbid.'" class="pb_container">
                        <div id="'.$this->textid.'" class="'.$this->textid.'">'.$percentDone.'</div>
                        <div class="pb_bar">
                                <div id="'.$this->pbarid.'" class="pb_before"
                                style="width: '.$percentDone.';"></div>
                                <div id="'.$this->tbarid.'" class="pb_after"></div>
                        </div>
                        <br style="height: 1px; font-size: 1px;"/>
                </div>
                <style>
                        .pb_container {
                                position: relative;
                        }
                        .pb_bar {
                                width: 100%;
                                height: 1.3em;
                                border: 1px solid silver;
                                -moz-border-radius-topleft: 5px;
                                -moz-border-radius-topright: 5px;
                                -moz-border-radius-bottomleft: 5px;
                                -moz-border-radius-bottomright: 5px;
                                -webkit-border-top-left-radius: 5px;
                                -webkit-border-top-right-radius: 5px;
                                -webkit-border-bottom-left-radius: 5px;
                                -webkit-border-bottom-right-radius: 5px;
                        }
                        .pb_before {
                                float: left;
                                height: 1.3em;
                                background-color: #43b6df;
                                -moz-border-radius-topleft: 5px;
                                -moz-border-radius-bottomleft: 5px;
                                -webkit-border-top-left-radius: 5px;
                                -webkit-border-bottom-left-radius: 5px;
                        }
                        .pb_after {
                                float: left;
                                background-color: #FEFEFE;
                                -moz-border-radius-topright: 5px;
                                -moz-border-radius-bottomright: 5px;
                                -webkit-border-top-right-radius: 5px;
                                -webkit-border-bottom-right-radius: 5px;
                        }
                        .pb_text {
                                padding-top: 0.1em;
                                position: absolute;
                                left: 48%;
                        }</style>';
                return $content;
        }
 
        function setProgressBarProgress($percentDone, $text = '') {
                $this->percentDone = number_format($percentDone, 2, '.', '').'%';
                // $text = $text ? $text : number_format($this->percentDone, $this->decimals, '.', '').'%';
                print('
                <script type="text/javascript">
                if (document.getElementById("'.$this->pbarid.'")) {
                        document.getElementById("'.$this->pbarid.'").style.width = "'.$percentDone.'%";');
                
                print('document.getElementById("'.$this->tbarid.'").style.width = "'.(100-$percentDone).'%";');
                if ($text) {
                        print('document.getElementById("'.$this->textid.'").innerHTML = "'.$this->percentDone.' '.htmlspecialchars($text).'";');
                }
                print('}</script>'."\n");
                $this->flush();
        }
        
        function setProgressBarCommandLine($percentDone) {
            echo "\r" . $percentDone;
        }
 
        function flush() {
                print str_pad('', intval(ini_get('output_buffering')))."\n";
                @ob_end_flush();
                flush();
        }
 
	}