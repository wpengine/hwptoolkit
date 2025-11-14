document.addEventListener("DOMContentLoaded", function () {
	function listenForSanitizationMethodChange() {
		const sanitizationMethodSelect = document.querySelector(
			"#data_sanitization_method"
		);

		if (!sanitizationMethodSelect) {
			return;
		}

		function toggleCustomFields() {
			const isCustom = sanitizationMethodSelect.value === "custom";
			const customElements = document.querySelectorAll(
				".wpgraphql-logging-custom"
			);

			customElements.forEach((el) => {
				if (isCustom) {
					el.classList.add("block");
				} else {
					el.classList.remove("block");
				}
			});
		}

		toggleCustomFields();
		sanitizationMethodSelect.addEventListener("change", toggleCustomFields);
	}

	function listenForUnsavedChanges() {
		const formElement = document.querySelector("form");

		function getFormState(form) {
			const data = new FormData(form);
			return JSON.stringify(Array.from(data.entries()));
		}

		// Save the initial state of the form for later comparison
		const initialState = getFormState(formElement);

		// Warn the user if they try to leave with unsaved changes
		function beforeUnload(e) {
			const formState = getFormState(formElement);

			if (formState !== initialState) {
				e.preventDefault();
				e.returnValue = true;
			}
		}

		window.addEventListener("beforeunload", beforeUnload);

		// Remove the warning on submit so it doesn't appear when saving
		formElement.addEventListener("submit", function () {
			window.removeEventListener("beforeunload", beforeUnload);
		});
	}

	function listenForLogPointsSelection() {
		const logPointsInput = document.querySelector("#event_log_selection");
		const enableCheckbox = document.querySelector(
			"input[name='wpgraphql_logging_settings[basic_configuration][enabled]']"
		);

		function checkLogPointsSelection() {
			const descriptionId = "log-points-description";
			const anyLogPointsSelected = logPointsInput.selectedOptions.length > 0;

			const existingDescription =
				logPointsInput.parentElement.querySelector(`#${descriptionId}`);
			if (existingDescription) {
				existingDescription.remove();
			}

			// If the logging is enabled and no log points are selected, show a description
			if (enableCheckbox?.checked && !anyLogPointsSelected) {
				const description = document.createElement("p");

				if (!logPointsInput.parentElement.querySelector(`#${descriptionId}`)) {
					description.className = "description";
					description.id = descriptionId;
					description.textContent =
						"If you don't select any log points, no data will be logged.";
					description.style.marginLeft = "25px";
					logPointsInput.parentElement.appendChild(description);
				}
			}
		}

		logPointsInput.addEventListener("change", checkLogPointsSelection);
		enableCheckbox.addEventListener("change", checkLogPointsSelection);
		checkLogPointsSelection();
	}

	listenForSanitizationMethodChange();
	listenForUnsavedChanges();
	listenForLogPointsSelection();
});
