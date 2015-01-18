plugin.loadLang();
plugin.loadMainCSS();

theWebUI.getFtpLink = function(id) {
    if (this.torrents[id].base_path) {
        var ftpRelativeDir = this.torrents[id].base_path.replace(plugin.ftpBaseDirectory, '');
        var ftpUri = plugin.ftpLink+ftpRelativeDir;
        $('#ftp_url').html('<a href="'+ftpUri+'">'+ftpUri+'</a>');
        theDialogManager.toggle('tgetftplink');
    }
}

if(plugin.canChangeMenu())
{
	plugin.createMenu = theWebUI.createMenu;
	theWebUI.createMenu = function(e, id) 
	{
		plugin.createMenu.call(this,e,id);
		if(plugin.enabled && plugin.allStuffLoaded)
		{
			var el = theContextMenu.get(theUILang.Properties);
			if(el) {
                var el = theContextMenu.get(theUILang.Properties);
			if(el)
				theContextMenu.add(el,[theUILang.GetFTPLink, (this.getTable("trt").selCount == 1) && (theWebUI.dID.length==40) ? "theWebUI.getFtpLink('"+id+"')" : null]);
            }
		}
	}
}

plugin.onLangLoaded = function()
{
	this.addButtonToToolbar("ftplink",theUILang.mnu_ftpopen,"window.open('"+plugin.ftpLink+"')","help");
	this.addSeparatorToToolbar("help");
    
    theDialogManager.make("tgetftplink", theUILang.FTPLink,
		"<div class='cont fxcaret' id='ftp_url'></div>"+
		"<div class='aright buttons-list'><input type='button' class='Cancel Button' value='"+theUILang.Cancel+"'/></div>"
    );
};

plugin.onRemove = function()
{
    theDialogManager.hide('tgetftplink');
	this.removeSeparatorFromToolbar("help");
	this.removeButtonFromToolbar("ftplink");
	if(plugin.timeout)
	{
		window.clearTimeout(plugin.timeout);
		plugin.timeout = null;
	}
}
