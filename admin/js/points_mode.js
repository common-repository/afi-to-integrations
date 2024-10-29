function points_mode(data) {

	let div_fix = document.getElementById('fixed_points_input');
	let div_part = document.getElementById('order_percent_input');

	if (data === "fix") {
		div_fix.removeAttribute('class');
		div_part.setAttribute("class", "hidden");
	} else if (data === "order_percent") {
		div_part.removeAttribute('class');
		div_fix.setAttribute("class", "hidden");
	}
}