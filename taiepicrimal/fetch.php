<?php
	set_time_limit(0);
	function re_array($arr)
	{
		$new_arr = array();
		$key = 0;
		foreach($arr as $val)
		{
			$new_arr[$key] = $val;
			$key++;
		}
		return $new_arr;
	}
	$url_arr = array(
		'child'=>'http://data.taipei/opendata/datalist/apiAccess?scope=resourceAquire&rid=a25d2083-5742-45a4-bfb8-2eaf6dbd3f53',
		'bike'=>'http://data.taipei/opendata/datalist/apiAccess?scope=resourceAquire&rid=47c6fdfa-8849-4f73-badd-689d577ccb7e',
		'car'=>'http://data.taipei/opendata/datalist/apiAccess?scope=resourceAquire&rid=a590259c-b725-4e71-9a19-00bdd79db663',
		'home'=>'http://data.taipei/opendata/datalist/apiAccess?scope=resourceAquire&rid=876a83ac-c27a-457f-8d00-25751373a93c'
	);
	$total = new stdClass();
	$total->results = array();
	foreach($url_arr as $key=>$url)
	{
		$obj = new stdClass();
		$obj->results = array();		
		
		$source = json_decode(@file_get_contents($url),TRUE);
		if(!$source) continue;
		foreach($source['result']['results'] as $data)
		{
			$info = new stdClass();

			if($key=='child')
			{
				$info->info = $data['reason'];
				$addr = '台灣台北市'.$data['District'].'區'.$data['Location'];
			}
			else if($key=='bike'||$key=='car'||$key=='home')
			{
				$data = re_array($data);
				$info->info = '發生時間:'.$data[3].' '.$data[4];
				$addr = $data[5];
			}
			else
			{
				continue;
			}

			$lnglat = json_decode(@file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.$addr));
			if(count($lnglat->results)==0) continue;

			$info->lng = $lnglat->results[0]->geometry->location->lng;
			$info->lat = $lnglat->results[0]->geometry->location->lat;
			$total->results[] = array($info->lng,$info->lat);
			$obj->results[] = $info;
		}

		@file_put_contents('./data/'.$key.'.json',json_encode($obj));
	}
	@file_put_contents('./data/total.json',json_encode($total));
	echo 'OK!';
?>