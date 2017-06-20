<?php
/**
	* egret_res_tool 
	* @author Jet QQ:832606
  * @description ��������Egret �� default.res.json
  * V0.1 ����groups�Զ������,ȥ���Զ����ɵ�preload��,����������Դ�ļ�
  *
  */
error_reporting(E_ALL ^ E_NOTICE);
//////////////////////���������޸������ļ�////////////////
$resFile="..\\..\\resource\\resource.json";
$resDir="..\\..\\resource\\assets\\";
//////////////////////////////////////


$resDir2=realpath(dirname($resDir))."\\"; 
echo "START\n";
$jsons = json_decode(file_get_contents($resFile) ,true ) ;
$groups=array();
foreach($jsons['groups'] as  $key => $val) {
	//������preload�飬�Լ��齨preload
    if($val['name'] !="preload") {
        $groups[]= $val;
    }
}
$res2=array();
$res=$jsons['resources'];
$reservedRes=array();
//������ԭres�е�key:��name:cache  url:xxx/xxx.cache
$reserved=array('global','version','cache','language','eui_json');
foreach ($res as $val) {
    if( in_array( $val['name']  ,$reserved)) {
        $res2[]= $val;
    }
    $nameNew = preg_replace('/\s/', '_', $val['name']);
    $reservedRes[$nameNew]= $val['name'];
}


function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);
    foreach($files as $key => $value) {
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results[] = $path;//str_replace($resDir,"",$path);
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            //$results[] = $path;
        }
    }
    return $results;
}
$files=array();
$files = getDirContents($resDir,$files);

foreach($files as $filename) {
    $ext =  substr(strrchr($filename, "."), 1);
    //���˵������ĵ�
    $file_new = preg_replace('/\s/', '_', $filename);
    if(!preg_match("/^[a-zA-Z0-9\\\:\-{}\_\.\(\)]{1,200}$/" ,$filename)) {
        echo  "Invalid Filename : �ļ����Ǳ�׼{$filename}\n";
        //rename($filename,$file_new);
        continue;
    }  
    $url = str_replace(DIRECTORY_SEPARATOR,"/",preg_replace('/'.str_replace("\\","\\\\",$resDir2).'/i',"",$filename));
    $sp= explode(DIRECTORY_SEPARATOR, $filename);
    $name  = end($sp);
    $name = substr($name,0,-1-strlen($ext))."_".$ext;
    if(array_key_exists($name,$reservedRes)) {
        $name = $reservedRes[$name];
    }
    if($ext == 'png') {
        $res2[]= array('name'=>  $name,'type'=>'image','url'=>$url);
    } else if($ext == 'fnt') {
        $res2[]= array('name'=>  $name,'type'=>'font','url'=>$url);
    } else if($ext == 'mp3' || $ext =="wav") {
        $res2[]= array('name'=>  $name,'type'=>'sound','url'=>$url);
    } else if($ext =="json") {
        $jsonData = json_decode(file_get_contents($filename) ,true ) ;
        $subKeys =array();
        if($jsonData && $jsonData['file'] && $jsonData['frames']) {
            $frames = $jsonData['frames'];
            foreach($frames as $key => $v1) {
                $subKeys[]=$key;
            }
        }
        if(count($subKeys)>0) {
            $res2[]= array('name'=>  $name,'subkeys'=>implode(",",$subKeys)  ,'type'=>'sheet','url'=>$url);
        } else {
            $res2[]= array('name'=>  $name,'type'=>'json','url'=>$url);
        }
    }
}
file_put_contents($resFile ,str_replace("\\/","/",json_encode(array('groups'=> $groups , 'resources'=>$res2 ) ,JSON_PRETTY_PRINT )));
echo "\nDONE...";