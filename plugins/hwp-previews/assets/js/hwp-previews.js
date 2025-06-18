document.addEventListener("DOMContentLoaded", () => {
	// --------------------------------------------------------
	// Parameter buttons
	// --------------------------------------------------------

	const buttons = document.querySelectorAll(".hwp-previews-insert-tag");
	const input = document.querySelector(".hwp-previews-url");

	function setTouched() {
		this.setAttribute("data-touched", "true");
		this.removeEventListener("focus", setTouched);
	}

	function insertTag() {
		const tag = this.textContent.trim();
		const isTouched = input.dataset.touched;

		if (!input) return;

		// Get cursor position
		let cursorPos = input.selectionEnd;

		// If cursor position is at the start, set it to the end of the input value
		if (cursorPos === 0 && !isTouched) cursorPos = input.value.length;

		// Split text at cursor position
		const textBefore = input.value.substring(0, cursorPos);
		const textAfter = input.value.substring(cursorPos);

		// Insert link text at cursor position
		input.value = textBefore + tag + textAfter;

		// Set cursor position after inserted text
		const newCursorPos = cursorPos + tag.length;
		input.setSelectionRange(newCursorPos, newCursorPos);

		// Focus the input
		input.focus();
	}

	// Mark the input as touched to prevent inserting at the start
	input.addEventListener("focus", setTouched);

	for (const button of buttons) {
		button.addEventListener("click", insertTag);
	}

	// --------------------------------------------------------
	// Unsaved changes warning
	// --------------------------------------------------------

	// Select the form to monitor for changes
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
});
