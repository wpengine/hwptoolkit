document.addEventListener('DOMContentLoaded', function() {
	const sanitizationMethodSelect = document.querySelector("#data_sanitization_method");
	if (!sanitizationMethodSelect) {
		return;
	}

	function toggleCustomFields() {
		const isCustom = sanitizationMethodSelect.value === 'custom';
		const customElements = document.querySelectorAll('.wpgraphql-logging-custom');

		customElements.forEach((el) => {
			if (isCustom) {
				el.classList.add('block');
			} else {
				el.classList.remove('block');
			}
		});
	}

	toggleCustomFields();
	sanitizationMethodSelect.addEventListener('change', toggleCustomFields);
});
