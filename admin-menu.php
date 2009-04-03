<?php
// Hook for adding admin menus
add_action('admin_menu', 'add_admin_menu');

// action function for above hook
function add_admin_menu() {
    // Add a new submenu under Options:
    add_submenu_page('options-general.php', __('Notify Settings'), __('Notify Settings'), 'manage_options', 'notify', 'notify_options_page');
}

// notify_options_page() displays the page content for the Notify Options submenu
function notify_options_page() {

    // variables for the field and option names (not change!)
    $opt_name = 'email_new_comment';
    $opt_name2 = 'email_moderated_comment';
    
    $hidden_field_name = 'email_submit_hidden';
    $data_field_name = 'email_new_comment';
    $data_field_name2 = 'email_moderated_comment';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $opt_val2 = get_option( $opt_name2 );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];
        $opt_val2 = $_POST[ $data_field_name2 ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $opt_name2, $opt_val2 );

        // Put an options updated message on the screen

?>
        <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php

    }

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header
    echo "<h2>" . __( 'Notify Settings' ) . "</h2>";

    // options form
    
    ?>

    
    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
    
    <table>
    <tr>
    <td><p><?php _e("Email for new comments notification:"); ?> </p></td>
    <td><input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $opt_val; ?>" size="30"></td>
    </tr>
    
    <tr>
    <td><p><?php _e("Email for approved comments notification:"); ?> </p></td>
    <td><input type="text" name="<?php echo $data_field_name2; ?>" value="<?php echo $opt_val2; ?>" size="30"></td>
    </tr>
    </table>
    <hr />
    
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Update Options' ) ?>" />
    </p>
    
    </form>
    
    </div>

<?php
}
?>
