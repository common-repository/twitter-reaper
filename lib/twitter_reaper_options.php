<?php 
if ( isset( $_POST['twitter_reaper_stop_nonce'] ) && wp_verify_nonce( $_POST['twitter_reaper_stop_nonce'], 'twitter_reaper_stop_nonce' ) ) { stop_twitter_reaper_chron(); }
if ( isset( $_POST['twitter_reaper_options_nonce'] ) && wp_verify_nonce( $_POST['twitter_reaper_options_nonce'], 'twitter_reaper_options_nonce' ) ) { 
  save_twitter_reaper_credentials();
  save_twitter_reaper_options(); 
  twitter_reaper_save_tweets();
}

if (!twitter_reaper_client_data_valid()) { ?>
  <div class="error">
    <p>To use the Twitter Reaper, you must create an Twitter application and generate the access tokens.  Click <a href="https://apps.twitter.com/app/new" target="_blank">here</a> to get started.</p>
    <p>If you have an application ready to go, enter the credentials below to make this message go away</p> 
  </div>
<?php }

$twitter_reaper_options = get_option('twitter_reaper_options'); 
$twitter_reaper_cred = get_option('twitter_reaper_client_data');
?>


<div class="wrap">
  <h2>Twitter Reaper Settings</h2>

  <?php if (twitter_reaper_chron_running()) { ?>
    <p>Chron is currently running</p>
    <form action="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" method="post">
      <?php wp_nonce_field( 'twitter_reaper_stop_nonce', 'twitter_reaper_stop_nonce' ); ?>
      <input type="submit" name="submit" class="button button-warning" value="Stop Chron">
    </form>
  <?php } else { ?>
    <p>Chron is not currently running</p>
  <?php } ?>

  <form action="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" method="post">
    <?php wp_nonce_field('twitter_reaper_options_nonce', 'twitter_reaper_options_nonce'); ?>
    <table class="form-table">

      <!-- api credentials form fields -->
      <tr>
        <th scope="row">API Key</th>
        <td><input type="text" name="api_key" class="full-text" value="<?php echo $twitter_reaper_cred['api_key']; ?>" <?php echo current_user_can('activate_plugins') ? '' : 'disabled'; ?>></td>
      </tr>
      <tr>
        <th scope="row">API Secret</th>
        <td><input type="text" name="api_secret" class="full-text" value="<?php echo $twitter_reaper_cred['api_secret']; ?>" <?php echo current_user_can('activate_plugins') ? '' : 'disabled'; ?>></td>
      </tr>
      <tr>
        <th scope="row">Access Token</th>
        <td><input type="text" name="access_token" class="full-text" value="<?php echo $twitter_reaper_cred['access_token']; ?>" <?php echo current_user_can('activate_plugins') ? '' : 'disabled'; ?>></td>
      </tr>
      <tr>
        <th scope="row">Access Token Secret</th>
        <td><input type="text" name="access_token_secret" class="full-text" value="<?php echo $twitter_reaper_cred['access_token_secret']; ?>" <?php echo current_user_can('activate_plugins') ? '' : 'disabled'; ?>></td>
      </tr>
      <!-- end api credentials form fields -->

      <tr>
        <th scope="row">Query By</th>
        <td>
          <select name="query" id="query">
            <option value="hashtag" <?php echo $twitter_reaper_options['query'] == 'hashtag' ? 'selected' : ''; ?>>Hashtag</option>
            <option value="username" <?php echo $twitter_reaper_options['query'] == 'username' ? 'selected' : ''; ?>>Username</option>
          </select>
        </td>
      </tr>
      <?php 
        if ($twitter_reaper_options == false) {
          $un_style = 'style="display: none;"';
          $ht_style = '';
        } else if (array_key_exists('query', $twitter_reaper_options)) {
          if ($twitter_reaper_options['query'] == 'username') {
            $un_style = '';
            $ht_style = 'style="display: none;"';
          } else if ($twitter_reaper_options['query'] == 'hashtag') {
            $ht_style = '';
            $un_style = 'style="display: none;"';
          } else {
            $un_style = 'style="display: none;"';
            $ht_style = '';
          }
        } else {
          $un_style = 'style="display: none;"';
          $ht_style = '';
        }
      ?>
      <tr id="hashtag" <?php echo $ht_style; ?>>
        <th scope="row">Hashtag</th>
        <td><input type="text" name="hashtag" class="full-text" value="<?php echo $twitter_reaper_options['hashtag']; ?>"></td>
      </tr>
      <tr id="username" <?php echo $un_style; ?>>
        <th scope="row">Username</th>
        <td><input type="text" name="username" class="full-text" value="<?php echo $twitter_reaper_options['username']; ?>"></td>
      </tr>
      <script>
        $ = jQuery;
        $('#query').on('change', function(){
          var query = $(this).val();
          if (query == 'hashtag') {
            $('#hashtag').css('display', '');
            $('#username').css('display', 'none');
          } else {
            $('#username').css('display', '');
            $('#hashtag').css('display', 'none');
          }
        });
      </script>
        <tr>
          <th scope="row">Number of Tweets to Get</th>
          <td>
            <select name="count">
              <option default <?php echo $twitter_reaper_options['count'] == '' ? 'selected' : ''; ?>></option>
              <option value="10" <?php echo $twitter_reaper_options['count'] == '10' ? 'selected' : ''; ?>>10</option>
              <option value="20" <?php echo $twitter_reaper_options['count'] == '20' ? 'selected' : ''; ?>>20</option>
              <option value="30" <?php echo $twitter_reaper_options['count'] == '30' ? 'selected' : ''; ?>>30</option>
              <option value="40" <?php echo $twitter_reaper_options['count'] == '40' ? 'selected' : ''; ?>>40</option>
              <option value="50" <?php echo $twitter_reaper_options['count'] == '50' ? 'selected' : ''; ?>>50</option>
              <option value="60" <?php echo $twitter_reaper_options['count'] == '60' ? 'selected' : ''; ?>>60</option>
              <option value="70" <?php echo $twitter_reaper_options['count'] == '70' ? 'selected' : ''; ?>>70</option>
              <option value="80" <?php echo $twitter_reaper_options['count'] == '80' ? 'selected' : ''; ?>>80</option>
              <option value="90" <?php echo $twitter_reaper_options['count'] == '90' ? 'selected' : ''; ?>>90</option>
              <option value="100" <?php echo $twitter_reaper_options['count'] == '100' ? 'selected' : ''; ?>>100</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row">Recurrence</th>
          <td>
            <select name="recurrence">
              <option default <?php echo $twitter_reaper_options['recurrence'] == '' ? 'selected' : ''; ?>></option>
              <option value="minutely" <?php echo $twitter_reaper_options['recurrence'] == 'minutely' ? 'selected' : ''; ?>>Every Minute</option>
              <option value="half_hour" <?php echo $twitter_reaper_options['recurrence'] == 'half_hour' ? 'selected' : ''; ?>>Every 30 min</option>
              <option value="hourly" <?php echo $twitter_reaper_options['recurrence'] == 'hourly' ? 'selected' : ''; ?>>Every Hour</option>
              <option value="twicedaily" <?php echo $twitter_reaper_options['recurrence'] == 'twicedaily' ? 'selected' : ''; ?>>Twice Daily</option>
              <option value="daily" <?php echo $twitter_reaper_options['recurrence'] == 'daily' ? 'selected' : ''; ?>>Every Day</option>
              <option value="weekly" <?php echo $twitter_reaper_options['recurrence'] == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
            </select>
          </td>
        </tr>
    </table>
    <p class="submit"><input type="submit" name="submit" class="button button-primary" value="<?php echo $twitter_reaper_options['chron'] == true ? 'Save Changes' : 'Start Chron'; ?>"></p>
  </form>

</div>