<?php 
if (isset($_GET['page_number'])) {
  $pnum = intval($_GET['page_number']);
  $tweets = twitter_reaper_get_harvest_in_range(($pnum * 100), 100);
} else {
  $tweets = twitter_reaper_get_harvest_in_range(0, 100);
}

foreach($tweets as $tweet) { ?>
  <div class="single-tweet">
    <div class="delete"><a href="<?php echo admin_url();?>admin.php?page=twitter_reaper/lib/twitter_reaper_results.php&delete=<?php echo $tweet['id']; ?>">X</a></div>
    <p><?php echo twitter_reaper_tweet_time($tweet['date_created']) . ' ' . twitter_reaper_tweet_date($tweet['date_created']); ?></p>
     <?php echo $tweet['tweet']; ?>
  </div>
<?php } ?>
