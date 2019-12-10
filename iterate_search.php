<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="">
	<title><?php 	echo $_GET['query']; ?></title>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif] -->
	<!-- styles -->
	<link rel="stylesheet" type="text/css" href="../web_essentials/bootstrap/css/bootstrap.min.css">
	<!-- scripts -->
	<script src="../web_essentials/jquery-3.1.1.min.js"></script>
	<script src="../web_essentials/bootstrap/js/bootstrap.min.js"></script>
	<script src="../web_essentials/jquery-ui-1.12.1/jquery-ui.min.js"></script>
	<script src="../web_essentials/jquery-ui-1.12.1/jquery.ui.autocomplete.scroll.min.js"></script>
	<style> 
		body
		{
			background-color: #35346D;
			color: #ED2734;
		}
		a.picture img
		{
			width: 50%;
		}
		@media only screen and (max-width: 683px) {
			a.picture img {
				width: 100%;
			}
		}
	</style>
</head>
<body>
	<div id="title" style="position: fixed">
		<?php //echo @date( "Y-m-d\TH:i:sP", time()); ?>
		<?php
		// ini_set('error_reporting', E_ALL);
		require_once ('google-api-php-client/vendor/autoload.php');
		// require_once ('/google-api-php-client/src/Google_Client.php');  
		// require_once ('/google-api-php-client/src/contrib/Google_YouTubeService.php');  
		$DEVELOPER_KEY = '';  
		$client = new Google_Client();  
		$client->setDeveloperKey($DEVELOPER_KEY);  
		$youtube = new Google_Service_Youtube($client);  
		$query = ($_GET['query']) ? $_GET['query'] : '';
		//handle season
		if(substr($query,0,2)=='s-')
		{
			$e = explode('-',$query);
			$query = '';
			$episode = $e[1];
			for ($i=1; $i < 15; $i++) {
				$query .= sprintf('s%02d', $i) . sprintf('e%02d', $episode) . " OR ";
			}
		}
		$maxResults = ($_GET['maxResults']) ? $_GET['maxResults'] : 50;
		$next = $_GET['next'];
		$after = (isset($_GET['after'])) ? $_GET['after'] : strval(date("Y-m-d",time() - (60 * 60 * 24))) . "T00:00:00-00:00";
		$before = (isset($_GET['before'])) ? $_GET['before'] : strval(date("Y-m-d",time()))  . "T00:00:00-00:00";
	//NEW FUNCTION
		$days = floatval($_GET['days']);
		if(isset($days)&&$days>0)
		{
			echo 1;
			$after = date( "Y-m-d\TH:i:sP", strtotime($_GET['before']));
			$before = date( "Y-m-d\TH:i:sP", strtotime($_GET['before']) + (60 * 60 * 24 * $days));
		}
		else if(isset($days)&&$days<0)
		{
			echo 2;
			$after = date( "Y-m-d\TH:i:sP", strtotime($_GET['after']) + (60 * 60 * 24 * $days));
			$before = date( "Y-m-d\TH:i:sP", strtotime($_GET['after']));
		}
		$after_plus_one_day = date( "Y-m-d\TH:i:s", strtotime($_GET['after']) + 24*60*60 );
		$before_plus_one_day = date( "Y-m-d\TH:i:s", strtotime($_GET['before']) + 24*60*60);
		$after_minus_one_day = date( "Y-m-d\TH:i:s", strtotime($_GET['after']) - 24*60*60 );
		$before_minus_one_day = date( "Y-m-d\TH:i:s", strtotime($_GET['before'])  - 24*60*60 );
		?>
		<form>
			<input type="input" name="query" id="query" value='<?php echo $query; ?>'></input>
			<input type="input" id="after" name="after" value="<?php echo $after;  ?>"></input>
			<input type="input" id="before" name="before" value="<?php echo $before;  ?>"></input>
			<input type="input" name="days" value="<?php echo $_GET['days'];  ?>"></input>
			<input type="button" id="today" value="Today"></input>
			<input type="checkbox" name="long" <?php if(isset($_GET['long'])){ echo 'checked';} ?>>Long
			<input type="checkbox" id="all" name="all">All</input><br>
			<input type="submit" action="http://localhost/iterate_youtube/iterate_search.php"></input>
		</form>
		<?php
		if(!empty($_GET))
		{
			$params = array( 
				'q' => $query,  
				'maxResults' => $maxResults,
				'publishedBefore'=> $before,
				'publishedAfter'=> $after,
				// 'pageToken'=> $next,
				'part'=>'id,snippet',
				'type'=>'video'
				);
			//SO IS LONG CHECKBOX CHECKED???
			if(isset($_GET['long']))
			{
				$params['videoDuration'] = 'long';
				$params['type'] = 'video';
			}
			$total_results = 0;
			// var_export($params);
			// exit();
			$response = $youtube->search->listSearch('id,snippet', $params);
			do
			{
				$count += $maxResults;
				$video_ids_response = $youtube->search->listSearch('id,snippet', $params);
				$total_results = $video_ids_response['pageInfo']['totalResults'];
				$next_page_token = $video_ids_response['nextPageToken'];
				$params['pageToken'] = $next_page_token;
				// var_export($video_ids_response);
				// var_export($count);
				foreach ($video_ids_response['items'] as $item) {
					$video_ids[] = 'https://www.youtube.com/watch?v=' . $item['id']['videoId'];
				}
			//echo $next_page_token . "\n";
			}while(!preg_match('/^\s*$/',$next_page_token)&&($count < 1000));
			$javascript_representation = "['" . implode("','", $video_ids) . "']";
		//debug
		//echo "<pre>" . var_export($response,1) . "</pre>";
		}
		?>
		<div id="total_results_div"><?php echo sizeof($video_ids); ?></div> 
	</div> 
	<div id="pictures"></div>
	<script>
		all_thread_links = <?php echo (isset($javascript_representation)) ? $javascript_representation : '\'\''; ?>;
		$("#pictures").html('');
		for (var page_count = 0; page_count < all_thread_links.length; page_count++) 
		{   
			/*AJAX & get image links in image page*/
			argument_url = all_thread_links[page_count]; 
			$.get('iterate_bookmarklet.php',{'url':argument_url},function(response)
			{		
				$("#pictures").append(response);			
			});
		} 
		$( document ).keypress(function(event) {
			var key_code = event.keyCode;
			/*if Q*/
			if(key_code == 113)
			{
				link = document.querySelector('a:hover').href;
				if(link !== null)
				{
					all_videos_user_link = 'http://localhost/iterate_youtube/iterate_youtube.php?url=' + link;
					window.open(all_videos_user_link, '_blank');
				}
				return;
			}
			window.scrollTo(0,$(window).scrollTop() + 175);
		});
		$('#today').click(function(){
			d = new Date().toISOString();
			after_date_string = d.match(/.*(?=T)/)[0] + 'T00:00:00-00:00'
			$('#after').val(after_date_string);
			$('#before').val('');
			delete d;
		});
		////////////////////
		//
		//  ALL
		//
		////////////////////
		$('#all').change(function() {
			//CLICKED HAS TURNED IT INTO A CHECKMARK
			if($(this).is(":checked")) {
				$('a.picture').each(function(a) {
					youtube_url = $(this).attr('href');
					$(this).attr('youtube_url',youtube_url);
					all_url = 'http://localhost/iterate_youtube/iterate_youtube.php?url=' + youtube_url;
					$(this).attr('href',all_url);
				})
			}
			//REMOVING CHECKMARK
			else
			{
				$('a.picture').each(function(a) {
					youtube_url = $(this).attr('youtube_url');
					$(this).attr('href',youtube_url);
				});	
			}      
		});
		$("#query").autocomplete({
			source: choices
		 });
		$("input").on('keypress',e=>e.stopPropagation());
	</script>
</body>