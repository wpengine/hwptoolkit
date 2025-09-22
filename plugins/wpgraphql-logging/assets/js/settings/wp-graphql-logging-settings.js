// Add this to your admin JavaScript file
document.addEventListener('DOMContentLoaded', function() {

    const sanitizationMethodSelect = document.querySelector("#data_sanitization_method");
	if (! sanitizationMethodSelect || sanitizationMethodSelect.length === 0) {
		return;
	}

	function toggleCustomFields() {
        const isCustom = sanitizationMethodSelect.value === 'custom';

		if (isCustom) {
			document.querySelectorAll('.wpgraphql-logging-custom').forEach((el) => {
				el.classList.add('block');
			});
		} else {
			document.querySelectorAll('.wpgraphql-logging-custom').forEach((el) => {
				el.classList.remove('block');
			});
		}
    }

	// Initial check on page load
	toggleCustomFields();

	// Listen for changes
	sanitizationMethodSelect.addEventListener('change', toggleCustomFields);
});
