jQuery(function ($) {
	$('#generate').click(function () {
		$('#xml_output').text(wpmlData.wpml_config);
	});

	$('#copy').click(function () {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($('#xml_output').text()).select();
		document.execCommand("copy");
		$temp.remove();
	});
});
