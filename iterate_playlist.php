<?php 
require_once ('google-api-php-client/vendor/autoload.php');  
$url = ($_GET['url']) ? $_GET['url'] : 'https://www.youtube.com/watch?v=H-54cTOEW9A';
parse_str(parse_url($url)['query'],$query_arr);
$video_id = $query_arr['v'];

$DEVELOPER_KEY = '';  
$client = new Google_Client();  
$client->setDeveloperKey($DEVELOPER_KEY);  
$youtube = new Google_Service_Youtube($client);  
$query = ($_GET['query']) ? $_GET['query'] : 'PL9JY7lcjw0fjxT1frrPJJUBUMcujPku1l';
$maxResults = ($_GET['maxResults']) ? $_GET['maxResults'] : 50;
$next = $_GET['next'];
//GET VIDEOS FROM PLAYLIST
$params = array(
        'playlistId' => $query,
        'maxResults' => $maxResults
    );
if(isset($_GET['search']))
{
	$params['q'] = $_GET['search'];
}
do
{	
	$playlist_list_response = $youtube->playlistItems->listPlaylistItems('snippet,contentDetails',$params);
	$next_page_token = $playlist_list_response['nextPageToken'];
	$params['pageToken'] = $next_page_token;
	foreach ($playlist_list_response['items'] as $node) 
	{
		$video_ids[] = 'https://www.youtube.com/watch?v=' . $node['contentDetails']['videoId'];
	}
	$count = count($video_ids);
}while(!preg_match('/^\s*$/',$next_page_token)&&($count <= 1000));
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