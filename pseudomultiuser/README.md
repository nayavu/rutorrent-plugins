# pseudo-multiuser modification
A simply and dirty way of extending ruTorrent with multiuser support. 
The main idea of this mod is to allow each user (user db is stored in .htpasswd) seeing only his added torrents, not everything. While torrent is added, it's metadata is populated with current username (in terms of rtorrent, a custom field d.set_custom=x-username), and then when torrents are queried they are filtered by current username.
Since rtorrent xmlrpc is like a mess, this mod uses implementation of xpath (so there could be performance issues, but I haven't faced them yet).