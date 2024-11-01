<?php 
  if (isset($_GET['delete'])) {
    if(!current_user_can('delete_posts')) {
      return;
    }

    twitter_reaper_delete_tweet($_GET['delete']);

  }

?>
<?php
if (!twitter_reaper_client_data_valid()) { ?>
  <div class="error">
    <p>To use the Twitter Reaper, you must create an Twitter application and generate the access tokens.  Click <a href="https://apps.twitter.com/app/new" target="_blank">here</a> to get started.</p>
    <p>If you have an application ready to go, enter the credentials <a href="<?php echo admin_url();?>admin.php?page=twitter_reaper/lib/twitter_reaper_options.php">here</a> to make this message go away</p> 
  </div>
<?php } ?>
<h2>Reaped Tweets</h2>
<div class="wrap" data-next-page="1">
  <?php include('twitter_reaper_scroll.php'); ?>
</div>

<script>
  $ = jQuery;
  var busy = false;
  $(window).bind('scroll', function(){
    var bottom = $('.single-tweet').last().position().top + $('.single-image').last().height();
    var scroll = $(window).scrollTop() + $(window).height();
    if (scroll > bottom && busy == false) {
      loadMore();
    }

    function loadMore() {
      var nextPage = $('.wrap').data().nextPage;
      $.ajax({
        type: 'GET',
        datatype: 'html',
        beforeSend: function(){busy = true;},
        url: "<?php echo admin_url();?>admin.php?page=twitter_reaper/lib/twitter_reaper_results.php&page_number=" + nextPage,
        success: function(data){
          $('.wrap').data().nextPage++;
          $('.wrap').append($(data).find('.single-tweet'));
        }
      }).always(function(){busy = false;})
    }

  })
</script>