jQuery(document).ready(function ($) {
	$('#wpml-cf-form').on('submit', function (e) {
		e.preventDefault();

		var data = $(this).serialize();

		$.post(ajaxurl, data, function (response) {
			// Decode the response before setting it as the textarea value
			var decodedResponse = $('<div/>').html(response).text();
			$('#xml-output').text(decodedResponse);

			// Enable the copy button if the textarea is not empty
			if (decodedResponse.trim() !== '') {
				$('#copy-xml').prop('disabled', false);
			}
		});
	});

	$('#copy-xml').on('click', function (e) {
		e.preventDefault();

		// Select the contents of the textarea
		var xmlOutput = document.getElementById('xml-output');
		xmlOutput.select();

		try {
			// Copy the selected text to the clipboard
			document.execCommand('copy');
			alert('XML copied to clipboard!');
		} catch (err) {
			console.log('Oops, unable to copy');
		}
	});
});
