jQuery(document).ready(function($) {
	$(".wpgraphql-logging-datepicker").datetimepicker({
		dateFormat: "yy-mm-dd",
		timeFormat: "HH:mm:ss"
	});
});

document.addEventListener("DOMContentLoaded", function () {
	function formatGraphQLQuery(query, indentSize = 2) {
		if (!query || typeof query !== "string") return "";

		try {
			let formatted = query.trim().replace(/\s+/g, " ");
			let indentLevel = 0,
				result = "",
				inString = false,
				stringChar = null,
				afterClosingParen = false;
			const operationTypes = ["query", "mutation", "subscription", "fragment"];
			const indent = () => "\n" + " ".repeat(indentLevel * indentSize);

			for (let i = 0; i < formatted.length; i++) {
				const char = formatted[i],
					prev = formatted[i - 1],
					next = formatted[i + 1];

				// Track string literals
				if ((char === '"' || char === "'") && prev !== "\\") {
					if (!inString) {
						inString = true;
						stringChar = char;
					} else if (char === stringChar) {
						inString = false;
						stringChar = null;
					}
				}

				if (inString) {
					result += char;
					continue;
				}

				// Handle braces and parentheses
				if (char === "{" || char === "(") {
					result = result.trimEnd() + (char === "{" ? " {" : "(");
					if (next !== (char === "{" ? "}" : ")")) {
						indentLevel++;
						result += indent();
					}
					afterClosingParen = false;
				} else if (char === "}" || char === ")") {
					if (prev !== (char === "}" ? "{" : "(")) {
						indentLevel--;
						result = result.trimEnd() + indent();
					}
					result += char;
					afterClosingParen = char === ")";
				} else if (char === ",") {
					result += "," + indent();
					afterClosingParen = false;
				} else if (char === " ") {
					const trimmed = result.trimEnd();
					const lastWord =
						trimmed
							.split(/[\s\n{}(),]/)
							.filter((w) => w)
							.pop() || "";
					const lastChar = trimmed.slice(-1);

					if (
						(afterClosingParen && next === "@") ||
						trimmed.endsWith("...") ||
						lastWord === "on" ||
						operationTypes.includes(lastWord) ||
						lastChar === ":" ||
						lastChar === "@" ||
						trimmed.match(/@\w+$/)
					) {
						result += char;
					} else {
						const isBetweenFields =
							lastChar &&
							/[a-zA-Z0-9_]/.test(lastChar) &&
							next &&
							/[a-zA-Z_]/.test(next) &&
							!["{", "}", "(", ")", ",", ":", "\n", "@", "."].includes(next);

						if (isBetweenFields) {
							result = result.trimEnd() + indent();
							afterClosingParen = false;
						} else if (!["\n", " "].includes(result.slice(-1))) {
							result += char;
						}
					}
				} else {
					if (
						afterClosingParen &&
						char !== "@" &&
						char !== "{" &&
						char !== "}"
					) {
						result = result.trimEnd() + " ";
					}
					result += char;
					afterClosingParen = false;
				}
			}

			return result
				.split("\n")
				.map((line) => line.trimEnd())
				.join("\n");
		} catch (error) {
			console.error("GraphQL formatting error:", error);
			return query;
		}
	}

	const queryElements = document.querySelectorAll(
		"pre.wpgraphql-logging-query"
	);

	if (queryElements.length > 0) {
		queryElements.forEach(function (element) {
			const rawQuery = element.textContent;
			const formattedQuery = formatGraphQLQuery(rawQuery);
			element.textContent = formattedQuery;
		});
	}
});
