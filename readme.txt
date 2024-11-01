=== Twitter Reaper ===
Contributors: _qrrr
Donate link:
Tags: theme development, twitter, wp_cron
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for developers. Gets tweets by username or hashtag

== Description ==

Set up wp_cron jobs to occur every minute, half-hour, hour, daily, or weekly.  Saves tweet data to database for use in custom theme development.

Parses tweet bodies, replacing urls, hashtags and usernames with corresponding hrefs.  Replaces unicode emojis with image sprites.

`twitter_reaper_get_harvest()`
* returns array of tweet data for every tweet saved in the cron
  * id [the unique ID of the tweet in the Wordpress database]
  * tweet_id [the unique ID of the image as supplied by Twitter]
  * tweet
  * date_created [the date the tweet was made on Twitter]

example:

`
$tweets = twitter_reaper_get_harvest();
foreach ($tweets as $tweets) {
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`

`twitter_reaper_get_harvest_in_range($start, $stop)`
* returns array of image data for images in range supplied as arguments
* data return is the same as get_instagram_reap()
* handy for pagination

example: 

`
$images = twitter_reaper_get_harvest_in_range(0, 25);
foreach ($images as $image) { ?>
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`

`twitter_reaper_save_tweets()`
* this is the function called in the cron-job.  You can call it at your leasure if you want to update get new images outside of the schedule

`twitter_reaper_get_tweets($args)`
* query the Twitter API directly. This will not save in the database
* currently can query by Hashtag or Username

`$args:`
* query (required) - 'hashtag' or 'username'
* username - this will get the user ID by the username before querying
* hashtag - the hashtag for the query
* count - the number of images to return

example:

`
$args = array(
  'query' => 'username',
  'username' => 'dvl',
  'count' => '30'
);

$tweets = twitter_reaper_get_tweets($args);
foreach ($tweets as $tweet) { ?>
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`

== Installation ==

1. Upload this plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Provide Twitter API tokens and query options, and start the cron

== Changelog ==

== Code Snippets ==

`twitter_reaper_get_harvest()`
* returns array of tweet data for every tweet saved in the cron
  * id [the unique ID of the tweet in the Wordpress database]
  * tweet_id [the unique ID of the image as supplied by Twitter]
  * tweet
  * date_created [the date the tweet was made on Twitter]

example:

`
$tweets = twitter_reaper_get_harvest();
foreach ($tweets as $tweets) {
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`

`twitter_reaper_get_harvest_in_range($start, $stop)`
* returns array of image data for images in range supplied as arguments
* data return is the same as get_instagram_reap()
* handy for pagination

example: 

`
$images = twitter_reaper_get_harvest_in_range(0, 25);
foreach ($images as $image) { ?>
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`

`twitter_reaper_save_tweets()`
* this is the function called in the cron-job.  You can call it at your leasure if you want to update get new images outside of the schedule

`twitter_reaper_get_tweets($args)`
* query the Twitter API directly. This will not save in the database
* currently can query by Hashtag or Username

`$args:`
* query (required) ['hashtag' or 'username']
* username [this will get the user ID by the username before querying]
* hashtag [the hashtag for the query]
* count [the number of images to return]

example:

`
$args = array(
  'query' => 'username',
  'username' => 'dvl',
  'count' => '30'
);

$tweets = twitter_reaper_get_tweets($args);
foreach ($tweets as $tweet) { ?>
  <div class="single-tweet">
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php }
`