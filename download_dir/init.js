plugin.loadLang();

if (plugin.canChangeMenu()) {
	theWebUI.downloadDir = function(format) {

		const selectedRows = this.getTable("trt").rowSel;

		var hash;
		for (const row in selectedRows) {
			if (selectedRows[row] && row.length == 40) {
				hash = row;
				break;
			}
		}

		$("#dldir_hash").val(hash);
		$("#dldir_format").val(format);
		$("#dldir_").submit();
	};

	plugin.createMenu = theWebUI.createMenu;
	theWebUI.createMenu = function (e, id) {
		plugin.createMenu.call(this, e, id);
		if (!plugin.enabled) {
			return true;
		}

		const table = this.getTable("trt");
		theContextMenu.add([ theUILang.downloadTar, table.selCount == 1 ? 'theWebUI.downloadDir("tar")' : null ]);
		theContextMenu.add([ theUILang.downloadZip, table.selCount == 1 ? 'theWebUI.downloadDir("zip")' : null ]);
	};
}

plugin.onLangLoaded = function () {
	$(document.body).append(
		$("<iframe name='dldir_frm'/>")
			.css({ visibility: "hidden" })
			.attr({ name: "dldir_frm", id: "dldir_frm" })
			.width(0).height(0)
			.load(
				function() {
					$("#dldir_hash").val('');
					const d = this.contentDocument || this.contentWindow.document;
					if (d && d.location.href != "about:blank") {
						try {
							eval(d.body.textContent ? d.body.textContent : d.body.innerText);
						} catch(e) {
						}
					}
				}
			)
	);
	$(document.body).append(
		$('<form action="plugins/download_dir/action.php" id="dldir_" method="post" target="dldir_frm">' +
			'<input type="hidden" name="hash" id="dldir_hash" value="">' +
			'<input type="hidden" name="format" id="dldir_format" value="">' +
			'</form>'
		).width(0).height(0)
	);
};

plugin.onRemove = function()
{
	$('#dldir_frm').remove();
	$('#dldir_').remove();
};
