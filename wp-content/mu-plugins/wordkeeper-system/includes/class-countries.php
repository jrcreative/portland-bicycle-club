<?php

namespace WordKeeper\System;

/**
 * Country restriction class
 */
class Countries {

	/**
	 * The current settings of the plugin
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Define the core functionality of the plugin
	 */
	public function __construct(){
		$this->settings = Settings::get_instance()->get_settings();
	}

	/**
	 * Wakeup magic method.
	 */
	public function __wakeup(){
		throw new \LogicException(__CLASS__ . ' should not be unserialized');
	}

	/**
	 * Validate IP country (for logins)
	 * @param null|WP_User|WP_Error $user
	 * @param string $password
	 * @return WP_User|WP_Error
	 */
	public function handle_login($user, $password){
		if(!$this->valid_login_country()){
			$error = new \WP_Error('authentication_failed', __('Forbidden'));
			return $error;
		}
		return $user;
	}

	/**
	 * Validate IP country (for password resets)
	 * @param bool $allow
	 * @param string $user_id
	 * @return bool|WP_Error
	 */
	public function handle_reset($allow, $user_id){
		if(!$this->valid_reset_country()){
			$error = new \WP_Error('authentication_failed', __('Forbidden'));
			return $error;
		}
		return $allow;
	}

	/**
	 * Validate IP country (for registrations)
	 * @param \WP_Error $errors
	 * @param string $user
	 * @param string $user_id
	 * @return WP_Error
	 */
	public function handle_registration($errors, $sanitized_user_login, $user_email){
		if(!$this->valid_registration_country()){
			$errors = new \WP_Error('authentication_failed', __('Forbidden'));
		}
		return $errors;
	}

	/**
	 * Validate IP country (for comments)
	 * @param array $commentdata
	 * @return array
	 */
	function handle_comments($commentdata){
		if(!$this->valid_comment_country()){
			// Display a message to the user
			wp_die(
				__('Forbidden'),
				__('Forbidden'),
				array('response' => 403)
			);
		}
		return $commentdata;
	}

	/**
	 * Detect Contact Form 7 form submissions
	 * @param array $contact_form
	 * @return void
	 */
	function handle_cf7($contact_form, &$abort, $submission){
		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			// Skip sending the mail
			$abort = true; // Stop submission process

			// Set a custom response message
			$submission->set_response(__('Forbidden'));

			// Set a custom property for further handling
			$contact_form->set_properties(array('additional_setting' => 'rejected'));
		}
		return $contact_form;
	}

	/**
	 * Handle WPForms form submissions
	 * @param array $fields
	 * @param array $entry
	 * @param array $form_data
	 *
	 * @return array
	 */
	function handle_wpforms($fields, $entry, $form_data){
		// WPForms treats the "Name" field as a single field internally with one ID, but renders it as multiple subfields
		// (e.g. First Name, Last Name) in the UI. Errors assigned to the Name field's ID typically appear under the
		// Last Name subfield, which may look awkward in the form. We prioritize the first valid non-Name field (with a
		// non-empty value) for error display to avoid this issue. If no other valid field is found, we fall back to the
		// Name field, accepting that the error will show under Last Name.

		// Find the first valid non-Name field (non-empty value) or fallback to Name field
		$first_valid_field = null;
		$first_valid_field_id = null;
		$fallback_name_field = null;
		$fallback_name_field_id = null;

		foreach($fields as $field_id => $field){
			if(!empty($field['value'])){
				if(isset($field['type']) && $field['type'] === 'name'){
					// Store Name field as fallback
					$fallback_name_field = $field;
					$fallback_name_field_id = $field_id;
				}
				elseif(!$first_valid_field){
					// Use first non-Name field with non-empty value
					$first_valid_field = $field;
					$first_valid_field_id = $field_id;
					break; // Stop after finding first non-Name valid field
				}
			}
		}

		// Fallback to Name field if no other valid field found
		if(!$first_valid_field && $fallback_name_field){
			$first_valid_field = $fallback_name_field;
			$first_valid_field_id = $fallback_name_field_id;
		}

		// If no valid field found, skip
		if(!$first_valid_field){
			return $fields; // Return unmodified fields
		}

		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			// Stop the form submission and add a custom error message
			wpforms()->process->errors[$form_data['id']][$first_valid_field_id] = esc_html__(__('Forbidden'), 'wpforms');
		}
		return $fields;
	}

	/**
	 * Detect Gravity Forms spam submissions
	 * @param array $validation_result
	 */
	function handle_gravity_forms($validation_result){
		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			$validation_result['is_valid'] = false;
			rgar($validation_result, 'form')['validation_message'] = 'Submission rejected. Please check your input.';
		}

		return $validation_result;
	}

	/**
	 * Handle Formidable Forms submissions
	 * @param array $errors
	 * @param \stdClass $field
	 * @param string $value
	 * @param array $args
	 * @return array An array of validation errors if any errors occurred, empty array otherwise
	 */
	function handle_formidable_forms($errors, $field, $value, $args){
		// Use array_key_exists because isset is also going to return false if the string is empty
		// We cannot set it to a non-empty string OR true/false because then it gets output on the frontend
		if(array_key_exists('wordkeeper_block', $errors)){
			return $errors;
		}

		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			// Add an error
			$errors['wordkeeper_block'] = ''; // set to empty string, so it is not output on the frontpage
			$errors[$field->id] = __('Forbidden');
		}

		return $errors;
	}

	/**
	 * Handle Forminator Forms submissions
	 * @param array $errors
	 * @param int $form_id
	 * @param array $value
	 * @return array An array of validation errors if any errors occurred, empty array otherwise
	 */
	function handle_forminator_forms($errors, $form_id, $form_data){
		if(isset($form_data['wordkeeper_block'])){
			return $errors;
		}

		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			// Add an error
			$field = current($form_data);
			$form_data['wordkeeper_block'] = true;
			$errors[][$field['name']] = __('Forbidden');
		}

		return $errors;
	}

	/**
	 * Handles Ninja Forms submissions
	 * @param array $form_data
	 * @return array An array of validation errors if any errors occurred, empty array otherwise
	 */
	function handle_ninja_forms($form_data){
		if(isset($form_data['wordkeeper_block'])){
			return $form_data;
		}

		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			$field_id = key($form_data['fields']);
			$form_data['wordkeeper_block'] = true;
			$form_data['errors']['fields'][$field_id] = __('Forbidden');
		}

		return $form_data;
	}

	/**
	 * Detects Fluent Forms spam submissions
	 * @param array $insertData
	 * @param array $form_data
	 * @param \FluentForm\App\Models\Form $form
	 */
	function handle_fluent_forms($errors, $formData, $form){
		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			$error = __('Forbidden');
			if(isset($errors['general'])){
				$errors['general'][] = $error;
			}
			else{
				$errors['general'] = [$error];
			}
		}
		return $errors;
	}

	/**
	 * Handles MailChimp Forms submissions
	 * @param string[] $error_keys
	 * @param \MC4WP_Form $form
	 *
	 * @return string[]
	 */
	function handle_mailchimp($error_keys, $form){
		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			$error_keys[] = __('Forbidden');
		}

		return $error_keys;
	}

	/**
	 * Handle bbPress Forum submissions
	 * @param int $forum_id
	 *
	 * @return void
	 */
	function handle_bbpress_forum($forum_id){
		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			// Output an error
			bbp_add_error('spam_error', __('Forbidden'));
		}
	}

	/**
	 * Handle Elementor Form submissions
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 *
	 * @return \ElementorPro\Modules\Forms\Classes\Form_Record
	 */
	function handle_elementor_pro_form($record, $ajax_handler){
		// Get all submitted fields in order (preserves form builder sequence).
		$formatted_fields = $record->get_formatted_data();
		if(empty($formatted_fields)){
			return;
		}

		// see if we can the first field id
		$first_field_id = null;

		$raw_fields = $record->get('fields'); // Raw data for field type.

		// Loop to find the first available (non-empty) field for validation.
		foreach($raw_fields as $field_id => $field_data){
			$field_value = $field_data['raw_value'];
			$field_type = $field_data['type'];
			// Skip hidden fields and empty fields.
			if('hidden' === $field_type || empty($field_value)){
				continue;
			}
			$first_field_id = $field_id;
			break;
		}

		// Check if it is being submitted from a valid country
		if(!$this->valid_form_country()){
			if(!is_null($first_field_id)){
				$ajax_handler->add_error(
					$first_field_id,
					__('Forbidden')
				);
			}
			else{
				$ajax_handler->add_error_message(__('Forbidden'));
			}
		}
		return $record; // Stop after first error – rejects the form.
	}

	/**
	 * Validates if the request is from a valid country (for logins)
	 * @return bool
	 */
	private function valid_login_country(){
		if(isset($_SERVER['IP_COUNTRY_CODE'])
			&& isset($this->settings['login/restrict']) && $this->settings['login/restrict'] === true
			&& isset($this->settings['login/whitelist'])){
			if(!in_array($_SERVER['IP_COUNTRY_CODE'], $this->settings['login/whitelist'])){
				// Send block reason
				if(!headers_sent()){
					header('X-Blocked: Country/Login');
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Validates if the request is from a valid country (for password resets)
	 * @return bool
	 */
	private function valid_reset_country(){
		if(isset($_SERVER['IP_COUNTRY_CODE'])
			&& isset($this->settings['reset/restrict']) && $this->settings['reset/restrict'] === true
			&& isset($this->settings['reset/whitelist'])){
			if(!in_array($_SERVER['IP_COUNTRY_CODE'], $this->settings['reset/whitelist'])){
				// Send block reason
				if(!headers_sent()){
					header('X-Blocked: Country/Password');
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Validates if the request is from a valid country (for registrations)
	 * @return bool
	 */
	private function valid_registration_country(){
		if(isset($_SERVER['IP_COUNTRY_CODE'])
			&& isset($this->settings['register/restrict']) && $this->settings['register/restrict'] === true
			&& isset($this->settings['register/whitelist'])){
			if(!in_array($_SERVER['IP_COUNTRY_CODE'], $this->settings['register/whitelist'])){
				// Send block reason
				if(!headers_sent()){
					header('X-Blocked: Country/Account');
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Validates if the user is from a valid country
	 * @return bool
	 */
	private function valid_comment_country(){
		if(isset($_SERVER['IP_COUNTRY_CODE'])
			&& isset($this->settings['comment/restrict']) && $this->settings['comment/restrict'] === true
			&& isset($this->settings['comment/whitelist'])){
			if(!in_array($_SERVER['IP_COUNTRY_CODE'], $this->settings['comment/whitelist'])){
				// Send block reason
				if(!headers_sent()){
					header('X-Blocked: Country/Comment');
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Validates if the user is from a valid country
	 * @return bool
	 */
	private function valid_form_country(){
		if(isset($_SERVER['IP_COUNTRY_CODE'])
			&& isset($this->settings['forms/restrict']) && $this->settings['forms/restrict'] === true
			&& isset($this->settings['forms/whitelist'])){
			if(!in_array($_SERVER['IP_COUNTRY_CODE'], $this->settings['forms/whitelist'])){
				// Send block reason
				if(!headers_sent()){
					header('X-Blocked: Country/Form');
				}

				return false;
			}
		}
		return true;
	}
}