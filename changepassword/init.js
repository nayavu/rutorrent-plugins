plugin.loadLang();
plugin.loadMainCSS();

theWebUI.checkPassword = function() {
    if (!$.trim($("#new_password").val())) {
        alert(theUILang.ErrorPasswordEmpty);
    } else if ($.trim($("#new_password").val()) !== $.trim($("#password_confirm").val())) {
        alert(theUILang.ErrorPasswordDontMatch);
    } else {
        plugin.enableFormControls(false);
        theWebUI.requestWithoutTimeout("?action=changepassword",[plugin.start,plugin]);
    }
};

theWebUI.showChangePassword = function() {
	theDialogManager.toggle('tchangepassword');
};

plugin.start = function(data) {
    $('#new_password').empty();
    $('#password_confirm').empty();
    plugin.enableFormControls(true);
    console.log("got data:");
    console.log(data);
	if (data.errors.length) {
		noty(data.errors[0],"error");
    } else {
		plugin.task = "";
		theDialogManager.hide("tchangepassword");
        window.location.reload();
	}
};

rTorrentStub.prototype.changepassword = function() {
    this.content = "password="+encodeURIComponent($.trim($("#new_password").val()));
    this.contentType = "application/x-www-form-urlencoded";
    this.mountPoint = "plugins/changepassword/save.php";
    this.dataType = "json";
};

plugin.enableFormControls = function( enable ) {
    $('#new_password').attr('disabled',!enable);
    $('#password_confirm').attr('disabled',!enable);
    $('#savePassword').attr('disabled',!enable);
}


plugin.onLangLoaded = function()
{
	this.addButtonToToolbar("changepassword",theUILang.mnu_changepassword,"theWebUI.showChangePassword()","help");
	this.addSeparatorToToolbar("help");

	theDialogManager.make("tchangepassword", theUILang.ChangePassword,
		"<div class='cont fxcaret'>"+
            "<fieldset>"+
                "<label>"+theUILang.NewPassword+": </label>"+
                "<input type='password' id='new_password' name='new_password' class='TextboxLarge'/>"+
                "<br/>"+
                "<label>"+theUILang.PasswordConfirm+": </label>"+
                "<input type='password' id='password_confirm' name='password_confirm' class='TextboxLarge'/>"+
			"</fieldset>"+
		"</div>"+
		"<div class='aright buttons-list'><input type='button' id='savePassword' value='"+theUILang.SavePassword+"' class='Button' onclick='theWebUI.checkPassword()'/><input type='button' class='Cancel Button' value='"+theUILang.Cancel+"'/></div>"
    );
};

plugin.onRemove = function()
{
	theDialogManager.hide("tchangepassword");
	this.removeSeparatorFromToolbar("help");
	this.removeButtonFromToolbar("changepassword");
	if(plugin.timeout)
	{
		window.clearTimeout(plugin.timeout);
		plugin.timeout = null;
	}
}
