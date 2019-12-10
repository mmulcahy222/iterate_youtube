<?php 
require_once ('google-api-php-client/vendor/autoload.php');
// require_once ('/google-api-php-client/src/Google_Client.php');  
// require_once ('/google-api-php-client/src/contrib/Google_YouTubeService.php');  

$url = ($_GET['url']) ? $_GET['url'] : 'https://www.youtube.com/watch?v=H-54cTOEW9A';
parse_str(parse_url($url)['query'],$query_arr);
$video_id = $query_arr['v'];

$DEVELOPER_KEY = '';  
$client = new Google_Client();  
$client->setDeveloperKey($DEVELOPER_KEY);  
$youtube = new Google_Service_YouTube($client);  
$query = ($_GET['query']) ? $_GET['query'] : 'ZANNwxeO4kA';
//IF URL IS SEND AS QUERY, GET VIDEO ID
if(preg_match('/youtube\.com/',$query))
{
	$query = parse_url($query)['query'];
	parse_str($query,$chunks);
	$query = $chunks['v'];
}
$maxResults = ($_GET['maxResults']) ? $_GET['maxResults'] : 50;
$next = $_GET['next'];
$params = array( 
	'relatedToVideoId' => $query,  
	'maxResults' => $maxResults,
	'pageToken'=> $next,
	'type'=>'video',
	);
$total_results = 0;
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
?>




<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="icon" href="">
	<title>Good Practice</title>
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
	      font-family: verdana;
	      font-size: 14px;
	    }
		#container
		{
			padding:10px;
		}
		#screenshots_button
		{
			background-color: transparent;
			border: 1px solid #ED2734;
		}
		#title
		{
			padding: 10px;
		}
		a
		{
			text-decoration: none;
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
	<div id="container">
		<div id="title" style="position: absolute; top: 0px; font-size: 40px">
			<?php echo $count; ?>
		</div>
		<div id="pictures">
			
		</div>
	</div>
	<script>

	all_thread_links = <?php echo $javascript_representation; ?>;

	


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
		window.scrollTo(0,$(window).scrollTop() + 350)
	});

	
	</script>
</body>
</html>