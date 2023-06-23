(function() { // 写真
	const token = $("meta[name='token']").attr("content");

	function href(link) {
		history.pushState({}, "", window.location.href);
		window.location.replace(link);
	}

	$("#video").prop({
		volume: 0.5
	});

	$("#sidebar").on("click", ".link", function() {
		href($(this).attr("data-link"))
	});

	$("#folio").on("click", "div.img", function() {
		href($(this).attr("data-link"))
	});

	$("#imageview").on("click", "img.thumb-left, img.thumb-right", function() {
		href($(this).attr("data-link"))
	});

	$("#imageview").on("click", "img.medium", function() {
		var t = $(this);
		var id = t.attr("data-link");
		$.post("/ajax/Show_original.php", {
			original_id: id
		}).done(function(data) {
			$("#original").html(data);
		});
	});

	$("#original").on("click", function() {
		$("#original").html('');
	});

	$(document).on("keyup", function(e) {
		e.preventDefault();
		var tl = $(".thumb-left").attr("data-link");
		var tr = $(".thumb-right").attr("data-link");
		if (tr && e.which == 39) href(tr);
		else if (tl && e.which == 37) href(tl);
		else if (e.which == 27 || e.which == 32) $("#original").html('');
	});

	// ===== login =====

	$("#admin_login_password").on("keydown", function(e) {
		if (e.which == 13) $("#admin_login_password_submit").click();
	});

	$("#admin_login_password_submit").on("click", function() {
		var password = $("#admin_login_password").val();
		if (password) $.post("/ajax/Check_password.php", {
			password: password,
			token: token
		}).done(function(response) {
			if (response && response.length === 64) {
				set_cookie('admin', response, 1);
				href('/');
			} else {
				$("#admin_login_password").val('');
				alert("Wrong password");
			}
		});
	});

	// ===== cookies =====

	function set_cookie(name, value, days = 365) {
		const d = new Date();
		d.setTime(d.getTime() + (days * 86400000)); //24 d * 60 h * 60 m * 1000 s
		var expires = "expires=" + d.toUTCString();
		document.cookie = name + "=" + value + ";" + expires + ";samesite=strict;path=/";
	}

})(); // 写真
