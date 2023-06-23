(function() { // 写真 admin
	const token = $("meta[name='token']").attr("content");

	function fade(t) {
		t.fadeTo("slow", 0.5).fadeTo("slow", 1.0);
	}

	// ===== modify text data (images, sets, pages, themes) =====

	function update_db_text(table, type) {
		$("#current-" + table).on("change", "[name='" + type + "']", function() {
			var t = $(this);
			$.post("/ajax/Modify_text.php", {
				post_id: t.closest("tr").attr("data-id"),
				type: type,
				table: table,
				value: t.val(),
				token: token
			}).done(function(title) {
				t.val(title);
				fade(t);
			});
		});
	}

	update_db_text("images", "title");
	update_db_text("images", "slug");
	update_db_text("images", "description");

	update_db_text("sets", "title");
	update_db_text("sets", "slug");
	update_db_text("sets", "description");

	update_db_text("pages", "title");
	update_db_text("pages", "slug");

	update_db_text("themes", "name");
	update_db_text("themes", "slug");


	// ===== item order (images, sets, pages) =====

	function hide_end_buttons(table) {
		var e1 = "#current-" + table + " span.up";
		var e2 = "#current-" + table + " span.down";
		$(e1).eq(0).css("display", "none");
		$(e1).eq(1).css("display", 'inline-block');
		$(e2).eq(-1).css("display", "none");
		$(e2).eq(-2).css("display", 'inline-block');
	}

	function change_order(table) {
		hide_end_buttons(table);

		$("#current-" + table).on("click", "span.up", function() {
			var t = $(this);
			var tr = t.closest("tr");
			var prev_tr = tr.prev();
			prev_tr.before(tr);
			$.post("/ajax/Modify_order.php", {
				post_id: tr.attr("data-id"),
				prev_id: prev_tr.attr("data-id"),
				table: table,
				token: token
			}).done(function() {
				hide_end_buttons(table);
			});
		});

		$("#current-" + table).on("click", "span.down", function() {
			var t = $(this);
			var tr = t.closest("tr");
			var next_tr = tr.next();
			next_tr.after(tr);
			$.post("/ajax/Modify_order.php", {
				post_id: tr.attr("data-id"),
				next_id: next_tr.attr("data-id"),
				table: table,
				token: token
			}).done(function() {
				hide_end_buttons(table);
			});
		});
	}

	change_order("images");
	change_order("sets");
	change_order("pages");

	// ===== delete (image, set, page, theme) =====

	function delete_item(table) {
		$("#current-" + table).on("click", "span.delete", function() {
			var t = $(this);
			var tr = t.closest("tr");
			if (confirm('❌?')) {
				$.post("/ajax/Delete_item.php", {
					post_id: tr.attr("data-id"),
					table: table,
					token: token
				}).done(function() {
					tr.remove();
					hide_end_buttons(table);
				});
			}
		});
	}

	delete_item("images");
	delete_item("sets");
	delete_item("pages");
	delete_item("themes");

	// ===== set image poster =====

	$("#current-images img.set-poster").on("click", function() {
		var t = $(this);
		var tr = t.closest("tr");
		$.post("/ajax/Modify_image.php", {
			post_id: tr.attr("data-id"),
			set_id: $("#current-images").attr("data-id"),
			set_poster: true,
			token: token
		}).done(function(e) {
			if (e) {
				$("#current-images img.poster").removeClass("poster");
				t.addClass("poster");
			}
		});
	});

	// ===== upload images =====

	$("#dropimage").dropzone({
		init: function () {
			this.on("queuecomplete", function() {
				window.location.replace('/admin/images/');
			});
		},
		url: "/ajax/Upload_image.php",
		acceptedFiles: "image/avif,image/webp,image/jpeg,image/png",
		clickable: false,
		success: function(file) {
			this.removeFile(file);
		},
		params: () => ({
			token: token,
		}),
	});

	// ===== set image prefs =====

	$("#image_format").on("change", function() {
		var t = $(this);
		$.post("/ajax/Handle_image.php", {
			image_format: t.val(),
			token: token
		}).done(function() {
			fade(t);
			t.blur();
		});
	});

	$("#image_quality").on("change", function() {
		var t = $(this);
		$.post("/ajax/Handle_image.php", {
			image_quality: t.val(),
			token: token
		}).done(function() {
			fade(t);
			t.blur();
		});
	});

	$("#current_set_id").on("change", function() {
		var t = $(this);
		$.post("/ajax/Handle_image.php", {
			current_set_id: t.val(),
			token: token
		}).done(function() {
			window.location.replace('/admin/images/');
		});
	});

	// ===== move image =====

	$("#current-images span.move-image").on("click", function() {
		reset_dialogue();
		$(this).css("display", "none").next().css("display", 'inline-block');
	});

	$("#current-images select.move-image-set-list").on("change", function() {
		var t = $(this);
		var tr = t.closest("tr");
		$.post("/ajax/Modify_image.php", {
			post_id: tr.attr("data-id"),
			move_to_set_id: t.val(),
			token: token
		}).done(function() {
			tr.remove();
		});
	});

	// ===== attach media =====

	$("#current-images span.attach-media").on("click", function() {
		reset_dialogue();
		$(this).css("display", "none").next().css("display", 'inline-block');
	});

	drop_upload("#current-images div.drop-attach", "/ajax/Attach.php");

	// ===== delete attachment =====

	$("#current-images span.delete-attachment").on("click", function() {
		var t = $(this);
		var tr = t.closest("tr");
		if (confirm('Erase attachment?')) {
			$.post("/ajax/Delete_attachment.php", {
				post_id: tr.attr("data-id"),
				token: token
			}).done(function(e) {
				if (e) t.remove();
			});
		}
	});

	// ===== replace image =====

	$("#current-images span.replace-image").on("click", function() {
		reset_dialogue();
		$(this).css("display", "none").next().css("display", 'inline-block');
	});

	drop_upload("#current-images div.drop-replace", "/ajax/Replace_image.php");

	function drop_upload(selector, url) {
		$(selector).on("dragenter", function (e) {
			e.preventDefault();
			e.stopPropagation();
		});

		$(selector).on("dragover", function (e){
			e.preventDefault();
			$(this).css("opacity", "80%");
		});

		$(selector).on("drop", function (e) {
			e.preventDefault();
			var t = $(this);
			var tr = t.closest("tr");
			var post_id = tr.attr("data-id");
			send_files(t, post_id, e, url);
		});
	}

	function send_files(t, post_id, e, url) {
		var formdata = new FormData();
		formdata.append("token", token);
		formdata.append("post_id", post_id);

		var files = e.originalEvent.dataTransfer.files;
		for (let i = 0; i < files.length; i++) {
			formdata.append(files[i].name, files[i]);
		}

		$.ajax({
			url: url,
			method: "POST",
			data: formdata,
			contentType: false,
			cache: false,
			processData: false,
			success: function(data) {
				t.html(data);
			}
		});
	}

	reset_dialogue();
	function reset_dialogue() {
		$("#current-images select.move-image-set-list").css("display", "none");
		$("#current-images span.move-image").css("display", 'inline-block');
		$("#current-images div.dialogue-replace").css("display", "none");
		$("#current-images span.replace-image").css("display", 'inline-block');
		$("#current-images div.dialogue-attach").css("display", "none");
		$("#current-images span.attach-media").css("display", 'inline-block');
	}

	// ===== create set =====

	$("#create_new_set").on("click", function() {
		$.post("/ajax/New_set.php", {
			new_set: $("#new-set").serialize(),
			token: token
		}).done(function() {
			window.location.replace('/admin/sets/');
		});
	});

	// ===== create page =====

	$("#create_new_page").on("click", function() {
		$.post("/ajax/New_page.php", {
			new_page: $("#new-page").serialize(),
			token: token
		}).done(function() {
			window.location.replace('/admin/pages/');
		});
	});

	$("#current-pages .edit-page").on("click", function() {
		var t = $(this);
		var tr = t.closest("tr");
		$.post("/ajax/Modify_page.php", {
			post_id: tr.attr("data-id"),
			edit_page: true,
			token: token
		}).done(function(data) {
			$("#edit-page-area").replaceWith(data);
			update_pages();
		});
	});

	function update_pages() {
		escape_modal("#edit-page-area");
		$("#update-page").on("click", function() {
			var t = $(this);
			$.post("/ajax/Modify_page.php", {
				post_id: t.attr("data-id"),
				content: $("#page-content").val(),
				update_page: true,
				token: token
			}).done(function() {
				window.location.replace('/admin/pages/');
			});
		});
	}

	// ===== modify theme data =====

	$("#select_theme").on("change", function() {
		var t = $(this);
		$.post("/ajax/Modify_theme.php", {
			post_id: 1,
			select_theme: t.val(),
			token: token
		}).done(function() {
			window.location.replace('/admin/themes/');
		});
	});

	$("#current-themes .edit-theme").on("click", function() {
		var t = $(this);
		var tr = t.closest("tr");
		$.post("/ajax/Modify_theme.php", {
			post_id: tr.attr("data-id"),
			post_slug: tr.attr("data-slug"),
			edit_theme: true,
			token: token
		}).done(function(data) {
			$("#edit-theme-area").replaceWith(data);
			update_theme();
		});
	});

	function update_theme() {
		escape_modal("#edit-theme-area");
		$("#update-theme").on("click", function() {
			var t = $(this);
			$.post("/ajax/Modify_theme.php", {
				post_id: t.attr("data-id"),
				post_slug: t.attr("data-slug"),
				content: $("#theme-content").val(),
				update_theme: true,
				token: token
			}).done(function() {
				window.location.replace('/admin/themes/');
			});
		});
	}

	function escape_modal(sel) {
		$(document).on("keyup", function(e) {
			e.preventDefault();
			if (e.which == 27) $(sel).text('').removeClass("edit-modal")
		});
	}

	// ===== upload theme =====

	$("#droptheme").dropzone({
		init: function () {
			this.on("queuecomplete", function() {
				window.location.replace('/admin/themes/');
			});
		},
		url: "/ajax/Upload_theme.php",
		acceptedFiles: "application/zip,application/x-zip-compressed,multipart/x-zip",
		clickable: false,
		success: function(file) {
			this.removeFile(file);
		},
		params: () => ({
			token: token,
		}),
	});


	// ===== upload banner image =====

	$("#droplogo").dropzone({
		init: function () {
			this.on("queuecomplete", function() {
				window.location.replace('/admin/');
			});
		},
		url: "/ajax/Upload_logo.php",
		acceptedFiles: "image/avif,image/webp,image/jpeg,image/png",
		clickable: false,
		success: function(file) {
			this.removeFile(file);
		},
		params: () => ({
			token: token,
		}),
	});

	// ===== edit footer =====

	$("#footer").on("change", "#edit-footer", function() {
		$.post("/ajax/Modify_admin.php", {
			footer: $(this).val(),
			token: token
		}).done(function(data) {
			$("#footer").html(data);
			fade($("#footer"));
		});
	});

	$("#footer").on("click", ".delete", function() {
		$.post("/ajax/Modify_admin.php", {
			delete_footer: true,
			token: token
		}).done(function(data) {
			window.location.replace('/admin/');
		});
	});

	$("#banner").on("click", ".delete", function() {
		$.post("/ajax/Modify_admin.php", {
			delete_banner: true,
			token: token
		}).done(function(data) {
			window.location.replace('/admin/');
		});
	});

	// ===== sitemap =====

	$("#generate-sitemap").on("click", function() {
		$.post("/ajax/Modify_admin.php", {
			generate_sitemap: true,
			token: token
		}).done(function() {
			fade($("#generate-sitemap"));
		});
	});

	// ===== logout =====

	$("#admin_logout").on("click", function() {
		$.post("/ajax/Logout.php", {
			logout: true,
			token: token
		}).done(function() {
			delete_cookie('admin');
			window.location.replace('/');
		});
	});

	// ===== cookies =====
	function set_cookie(name, value, days = 365) {
		const d = new Date();
		d.setTime(d.getTime() + (days * 86400000)); //24 d * 60 h * 60 m * 1000 s
		var expires = "expires=" + d.toUTCString();
		document.cookie = name + "=" + value + ";" + expires + ";samesite=strict;path=/";
	}

	function delete_cookie(name) {
		document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:01 GMT;samesite=strict;path=/";
	}

})(); // 写真
