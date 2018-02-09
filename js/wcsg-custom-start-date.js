jQuery(function($) {
	var $customStartDateFieldset = $( '.wcsg-custom-start-date-fields' );
	var $customStartDateCheckbox;
	var $customStartDateInput;

	if ( 0 === $customStartDateFieldset.length ) {
		return;
	}

	$customStartDateCheckbox = $customStartDateFieldset.find( 'input[type="checkbox"]#wcsg_custom_start_date_enabled_checkbox' );
	$customStartDateInput    = $customStartDateFieldset.find( '.wcsg-custom-start-date-input' );
	$customStartDateField    = $customStartDateInput.find( 'input[type="text"]' );

	$customStartDateCheckbox.change(function() {
		if ( $( this ).is( ':checked' ) ) {
			$customStartDateInput.slideDown( 'fast', function() {
				$customStartDateField.datepicker( 'show' );
			} );
		} else {
			$customStartDateInput.slideUp( 'fast' );
		}
	});

	$customStartDateField.datepicker({
		minDate: '+1 D',
		dateFormat: 'yy-mm-dd'
	});

});
