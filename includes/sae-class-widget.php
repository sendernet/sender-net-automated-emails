<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Sender.net Form Widget
 * Version  1.0.0
 * Date     05-12-2017
 * 
 * 
 */

// Add function to widgets_init that'll load our widget.
if(get_option('sender_automated_emails_allow_forms')) {
    add_action( 'widgets_init', 'Sender_Automated_Emails_Widget' );
}

// Register widget.
function Sender_Automated_Emails_Widget() {
	register_widget( 'Sender_Automated_Emails_Widget' );
}

// Widget class.
class Sender_Automated_Emails_Widget extends WP_Widget {
	
/*-----------------------------------------------------------------------------------*/
/*	Widget Setup
/*-----------------------------------------------------------------------------------*/
	
	function __construct() {
        
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'sae_sender_form', 'description' => __('Add Sender.net form to your website.', 'framework') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'sender_automated_emails_widget' );

		/* Create the widget. */
		parent::__construct( 'sender_automated_emails_widget', __('Sender.net Form', 'framework'), $widget_ops, $control_ops );
	}

/*-----------------------------------------------------------------------------------*/
/*	Display Widget
/*-----------------------------------------------------------------------------------*/
	
	function widget( $args, $instance ) {
        
		extract( $args );
		
		$title = apply_filters('widget_title', $instance['title'] );
        
        // Get latest form version
        $forms = get_option('sender_automated_emails_forms_list');
		/* Before widget (defined by themes). */
		echo $before_widget;
        
		/* Display Widget */
        if(!$forms) {

            // Maybe show to user if error loading form

        } else if(isset ($forms[$instance['form']])) {
            
           if ( $title ) {
               echo $before_title . esc_html($title) . $after_title;
           }
           
          
           // Display form
           echo '<script type="text/javascript" src="' . esc_url($forms[$instance['form']]) . '"></script>';

       }
    
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
/*-----------------------------------------------------------------------------------*/
/*	Update Widget
/*-----------------------------------------------------------------------------------*/
	
	function update( $new_instance, $old_instance ) {
		
        $sender_api = new Sender_Automated_Emails_Api();
		$instance = $old_instance;
		
		/* Strip tags to remove HTML (important for text inputs). */
		$instance['title'] = sanitize_title( $new_instance['title'] );
		$instance['form'] = preg_replace('~\D~', '', $new_instance['form']);

		return $instance;
	}	

/*-----------------------------------------------------------------------------------*/
/*	Widget Settings
/*-----------------------------------------------------------------------------------*/
	 
	function form( $instance ) {
        // Get sender API Helper instance
        $sender_api = new Sender_Automated_Emails_Api();
        
        // Retrieve all forms
        $forms = $sender_api->getAllForms();
        
        // Set defaults
		$defaults = array(
            'title' => 'Sender.net Form',
            'form' => 120,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
        
        ?>
            
        <?php if(!isset($forms->error)) { ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php _e('Title:', 'framework') ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id( 'form' )); ?>">Select form</label> 
            <select id="<?php echo esc_attr($this->get_field_id( 'form' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'form' )); ?>" class="widefat" style="width:100%;">
                <option disabled selected>Select your form</option>
                <?php
                    foreach($forms as $form) {
                        ?>
                      <option <?php echo 'value="'.esc_attr($form->id).'" '; if ( $form->id == $instance['form'] ) echo 'selected = "selected"' ; ?>><?php echo esc_html($form->title); ?></option>
                    <?php 
                    }
                ?>
            </select>
        </p>
        <p>
            <i class="zmdi zmdi-info"></i> You can create form at Sender.net, then select it here and it will appear on the screen!
        </p>
        
        <?php } else { ?>
        <center>
            <p><strong>No forms found</strong></p>
            <p>Please create a form and refresh page</p>
        </center>
        <?php } ?>
        <center>
            <p>
                <a target="_BLANK" href="<?php echo get_admin_url().'options-general.php?page=sender-net-automated-emails#!forms'; ?>">Manage forms</a> | <a target="_BLANK" href="<?php echo $sender_api->getBaseUrl(); ?>/forms/add">Create new form </a>
            </p>
        </center>
	<?php
	}	
}