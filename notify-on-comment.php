<?php
/*
Plugin Name: Notificacion de comentario
Description: Envia una notificacion de comentario a un correo determinado y una vez aprobado envia una notificacion de comentario aprobado a otro correo distinto.
Version: 1.0
Author: Victor Cruz
*/

require('admin-menu.php');

function email_new_comment_send($commentID){
	global $wpdb;
	
	$comment_db = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = '$commentID'");
	if ( $comment_db->comment_approved ) // it is approved (comment from admin?)
  	return;
  		
	$comment = get_comment($commentID);
	$post = get_post($comment->comment_post_ID);
	
  // Get the email to notify new comment to moderate
	$to = get_option( 'email_new_comment' );
	if( empty($to) ){
    //Send to the post author
    $user = get_userdata($post->post_author);
    $to = $user->user_email;
  }
	
	// Set the send from
	$admin_email = get_option('admin_email');
	$headers= "From:$admin_email\r\n";
	$headers .= "Reply-To:$admin_email\r\n";
	$headers .= "X-Mailer: PHP/".phpversion();
	
	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is waiting for your approval'), $post->ID, $post->post_title ) . "\r\n";
	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
	$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
	$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
	$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
	$notify_message .= sprintf( __('Approve it: %s'),  get_option('siteurl')."/wp-admin/comment.php?action=mac&c=$commentID" ) . "\r\n";
	$notify_message .= sprintf( __('Delete it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&c=$commentID" ) . "\r\n";
	$notify_message .= sprintf( __('Spam it: %s'), get_option('siteurl')."/wp-admin/comment.php?action=cdc&dt=spam&c=$commentID" ) . "\r\n";
	$notify_message .= sprintf( __('Currently %s comments are waiting for approval. Please visit the moderation panel:'), $comments_waiting ) . "\r\n";
	$notify_message .= get_option('siteurl') . "/wp-admin/moderation.php\r\n";

	$subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), get_option('blogname'), $post->post_title );

	@wp_mail($to, $subject, $notify_message, $headers);

	return true;
}

function email_approved_comment_send($commentID){

  	global $wpdb;

  	$comment_db = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID = '$commentID'");
  	if ( !$comment_db ) // it was deleted
  		return;
  	if ( !$comment_db->comment_approved ) // it is not approved
  		return;
  	
  	$comment = get_comment($commentID);
  	$post = get_post($comment->comment_post_ID);
  	
    // Get the email to notify new approved comment
  	$to = get_option( 'email_moderated_comment' );
  	if( empty($to) ){
      //Send to the post author
      $user = get_userdata($post->post_author);
      $to = $user->user_email;
    }
  	
  	// Set the send from
  	$admin_email = get_option('admin_email');
  	$headers= "From:$admin_email\r\n";
  	$headers .= "Reply-To:$admin_email\r\n";
  	$headers .= "X-Mailer: PHP/".phpversion();
  	
  	$comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
  	$comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");
  
  	$notify_message  = sprintf( __('A new comment on the post #%1$s "%2$s" is approved'), $post->ID, $post->post_title ) . "\r\n";
  	$notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
  	$notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
  	$notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
  	$notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
  	$notify_message .= sprintf( __('Whois  : http://ws.arin.net/cgi-bin/whois.pl?queryinput=%s'), $comment->comment_author_IP ) . "\r\n";
  	$notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
  
    $subject = sprintf( __('[%1$s] New comment: "%2$s"'), get_option('blogname'), $post->post_title );
  
  	@wp_mail($to, $subject, $notify_message, $headers);
  
  	return true;
}

add_action('comment_post', 'email_new_comment_send');
add_action('wp_set_comment_status', 'email_approved_comment_send');

?>
