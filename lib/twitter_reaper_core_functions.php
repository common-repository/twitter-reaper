<?php 

function twitter_reaper_client_data_valid() {
  $twitter_reaper_client_data = get_option('twitter_reaper_client_data');
  if (
      isset($twitter_reaper_client_data['api_key']) && 
      isset($twitter_reaper_client_data['api_secret']) &&
      isset($twitter_reaper_client_data['access_token']) &&
      isset($twitter_reaper_client_data['access_token_secret'])
    ) {
    return true;
  } else {
    return false;
  }
}

function save_twitter_reaper_credentials() {
  $twitter_reaper_client_data = array();
  if (!isset($_POST['api_key']) || !isset($_POST['api_secret']) || !isset($_POST['access_token']) || !isset($_POST['access_token_secret']) ) {
    return false;
  }
  $twitter_reaper_client_data['api_key'] = $_POST['api_key'];
  $twitter_reaper_client_data['api_secret'] = $_POST['api_secret'];
  $twitter_reaper_client_data['access_token'] = $_POST['access_token'];
  $twitter_reaper_client_data['access_token_secret'] = $_POST['access_token_secret'];
  update_option('twitter_reaper_client_data', $twitter_reaper_client_data);
  return true;
}

function save_twitter_reaper_options() {
  $twitter_reaper_options = get_option('twitter_reaper_options');
  $twitter_reaper_options['chron'] = true;
  $twitter_reaper_options['query'] = $_POST['query'];
  $twitter_reaper_options['hashtag'] = $_POST['hashtag'];
  $twitter_reaper_options['username'] = $_POST['username'];
  $twitter_reaper_options['count'] = $_POST['count'];
  $twitter_reaper_options['recurrence'] = $_POST['recurrence'];
  update_option('twitter_reaper_options', $twitter_reaper_options);
  wp_clear_scheduled_hook('twitter_reaper_event');
  wp_schedule_event(time(), $twitter_reaper_options['recurrence'], 'twitter_reaper_event');
}

function twitter_reaper_chron_running() {
  if (!wp_next_scheduled('twitter_reaper_event')) {
    return false;
  } else {
    return true;
  }
}

function stop_twitter_reaper_chron() {
  wp_clear_scheduled_hook('twitter_reaper_event');
  $twitter_reaper_options = get_option('twitter_reaper_options');;
  $twitter_reaper_options['chron'] = false;
  update_option('twitter_reaper_options', $twitter_reaper_options);
  return true;
}


// twitter api query functions //
function buildBaseString($baseURI, $method, $params) {
  $r = array();
  ksort($params);
  foreach($params as $key=>$value){
      $r[] = "$key=" . rawurlencode($value);
  }
  return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
}

function buildAuthorizationHeader($oauth) {
  $r = 'Authorization: OAuth ';
  $values = array();
  foreach($oauth as $key=>$value)
      $values[] = "$key=\"" . rawurlencode($value) . "\"";
  $r .= implode(', ', $values);
  return $r;
}
function twitter_reaper_get_tweets($array, $parse_data=true) {
  $twitter_reaper_client_data = get_option('twitter_reaper_client_data');
  $oauth_access_token = $twitter_reaper_client_data['access_token'];
  $oauth_access_token_secret = $twitter_reaper_client_data['access_token_secret'];
  $consumer_key = $twitter_reaper_client_data['api_key'];
  $consumer_secret = $twitter_reaper_client_data['api_secret'];

  $args = twitter_reaper_fill_in_missing_args($array);

  $url = $args['query'] == 'username' ? 
    "https://api.twitter.com/1.1/statuses/user_timeline.json" :
    "https://api.twitter.com/1.1/search/tweets.json";

  $url_with_query = $args['query'] == 'username' ? 
    $url . "?screen_name={$args['username']}&count={$args['count']}" :
    $url . "?q={$args['hashtag']}&count={$args['count']}";

  $oauth = array(
             'count' => $args['count'],
             'oauth_consumer_key' => $consumer_key,
             'oauth_nonce' => time(),
             'oauth_signature_method' => 'HMAC-SHA1',
             'oauth_token' => $oauth_access_token,
             'oauth_timestamp' => time(),
             'oauth_version' => '1.0'
           );
  if ($args['query'] == 'username') {
    $oauth['screen_name'] = $args['username'];
  } else {
    $oauth['q'] = $args['hashtag'];
  }
  $base_info = buildBaseString($url, 'GET', $oauth);
  $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
  $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
  $oauth['oauth_signature'] = $oauth_signature;
  // Make requests
  $header = array(buildAuthorizationHeader($oauth), 'Expect:');
  $options = array(
               CURLOPT_HTTPHEADER => $header,
               //CURLOPT_POSTFIELDS => $postfields,
               CURLOPT_HEADER => false,
               CURLOPT_URL => $url_with_query,
               CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false
             );
  $feed = curl_init();
  curl_setopt_array($feed, $options);
  $json = curl_exec($feed);
  curl_close($feed);
  $twitter_data = json_decode($json);
  if ($parse_data == false) {
    return $twitter_data;
  } else {
    // todo: build data return for custom tw queries
    return;
  }
}


// twitter data parsing functions //
function twitter_reaper_parse_tweet_body($str) {
  $str = twitter_reaper_parse_tweet_emojis($str);
  $str = twitter_reaper_parse_tweet_urls($str);
  $str = twitter_reaper_parse_tweet_hashtags($str);
  $str = twitter_reaper_parse_tweet_usernames($str);
  return $str;
}

include('twitter_reaper_emoji.php');

function twitter_reaper_parse_tweet_emojis($str) {
  $str = emoji_unified_to_html($str);
  $str = iconv("UTF-8", "ASCII//IGNORE", $str);

  return $str;
}

function twitter_reaper_parse_tweet_urls($str) {
  $pattern = '/(http:\/\/[a-z0-9\.\/]+)/i';
  $replacement = '<a class="twitter-url" href="$1"' . 'target="_blank">' . "$1" . '</a>';

  $str = preg_replace($pattern, $replacement, $str); 
  return $str;
}

function twitter_reaper_parse_tweet_hashtags($str) {

  $pattern = '/(#[a-z0-9]+)/i';
  $replacement = '<a class="twitter-hashtag" href="http://twitter.com/$1" target="_blank">$1</a>';

  $str = preg_replace($pattern, $replacement, $str);
  return $str;
}

function twitter_reaper_parse_tweet_usernames($str) {
  $pattern = '/(@[a-z0-9]+)/i';
  $replacement = '<a class="twitter-username" href="http://twitter.com/$1" target="_blank">$1</a>';

  $str = preg_replace($pattern, $replacement, $str);
  return $str;
}

function twitter_reaper_parse_db_tweet_date($str) {
  $str_parts = explode(' ', $str);
  $time_parts = explode(':', $str_parts[3]);
  $h = intval($time_parts[0]);
  $m = intval($time_parts[1]);
  $s = intval($time_parts[2]);
  $day = intval($str_parts[2]);
  $month = date('n', strtotime($str_parts[1]));
  $year = intval(end($str_parts));
  return mktime($h, $m, $s, $month, $day, $year);
}

function twitter_reaper_parse_site_tweet_date($int) {
  $date = date('M j, Y', intval($int));
  return strval($date);
}

function twitter_reaper_tweet_date($int) {
  $date = date('M j, Y', intval($int));
  return strval($date);
}

function twitter_reaper_tweet_time($int) {
  $time = date('g:ia e', intval($int));
  return strval($time);
}

function twitter_reaper_save_tweets() {
  $args = get_option('twitter_reaper_options');
  $twitter_data = twitter_reaper_get_tweets($args, false);
  $pretty = function($v='',$c="&nbsp;&nbsp;&nbsp;&nbsp;",$in=-1,$k=null)use(&$pretty){$r='';if(in_array(gettype($v),array('object','array'))){$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").'<br>';foreach($v as $sk=>$vl){$r.=$pretty($vl,$c,$in+1,$sk).'<br>';}}else{$r.=($in!=-1?str_repeat($c,$in):'').(is_null($k)?'':"$k: ").(is_null($v)?'&lt;NULL&gt;':"<strong>$v</strong>");}return$r;};
  if($args['query'] == 'username') {
    foreach ($twitter_data as $twitter_datum) {

      $id = $twitter_datum->id;
      $text = $twitter_datum->text;
      $date = $twitter_datum->created_at;
      $date = twitter_reaper_parse_db_tweet_date($date);
      $text = twitter_reaper_parse_tweet_body($text);
      twitter_reaper_insert_tweet($id, $text, $date);
    }
  } else {
    $id = $twitter_datum->id;
    $twitter_data = twitter_reaper_get_tweets($args, false);
    foreach ($twitter_data->statuses as $twitter_datum) {
      $id = $twitter_datum->id;
      $text = $twitter_datum->text;
      $date = $twitter_datum->created_at;
      $date = twitter_reaper_parse_db_tweet_date($date);
      $text = twitter_reaper_parse_tweet_body($text);
      twitter_reaper_insert_tweet($id, $text, $date);
    }
  }
}

// database functions //

function twitter_reaper_has_tweet($id) {
  global $wpdb;
  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT `tweet_id`
     FROM " . $wpdb->prefix . 'reaper_tweets' . "
     WHERE `tweet_id` = %s LIMIT 1",
     strval($id)
  ), ARRAY_A);
  if (count($rows) == 0) {
    return false;
  } else {
    return true;
  }
}

function twitter_reaper_insert_tweet($id, $text, $date) {
  $date = trim(stripslashes($date));
  global $wpdb;
  if (twitter_reaper_has_tweet($id)) {
    return;
  } else {
    $sql = "INSERT INTO " . $wpdb->prefix . 'reaper_tweets' . " (`tweet`, `tweet_id`, `date_created`) VALUES (%s, %s, %s)";
    $sql = $wpdb->prepare($sql, $text, strval($id), $date);
    $wpdb->query($sql);
    return true;
  }
}

function twitter_reaper_get_harvest() {
  global $wpdb;
  $results = $wpdb->get_results(
    "SELECT * FROM " . $wpdb->prefix . 'reaper_tweets' . " ORDER BY `date_created` DESC", ARRAY_A
  );
  return $results;
}

function twitter_reaper_get_harvest_in_range($start, $stop) {
  global $wpdb;
  $results = $wpdb->get_results(
    "SELECT * FROM " . $wpdb->prefix . 'reaper_tweets' . " ORDER BY `date_created` DESC LIMIT $start, $stop", ARRAY_A
  );
  return $results;
}

function twitter_reaper_delete_tweet($id) {
  global $wpdb;
  $results = $wpdb->delete($wpdb->prefix . 'reaper_tweets', array('id' => $id));
  return true;
}

function twitter_reaper_fill_in_missing_args($args) {
  $twitter_reaper_options = get_option('twitter_reaper_options');
  $new_args = array();
  if(!isset($args['query'])) {
    return false;
  }

  $new_args['query'] = $args['query'];
  $new_args['username'] = isset($args['username']) ? $args['username'] : $twitter_reaper_options['username'];
  $new_args['hashtag'] = isset($args['hashtag']) ? $args['hashtag'] : $twitter_reaper_options['hashtag'];
  $new_args['count'] = isset($args['count']) ? $args['count'] : $twitter_reaper_options['count'];
  return $new_args;
}

