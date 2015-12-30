ContentPassword Plugin for Joomla 3.XX

This is just a modification to the original plugin written by progandy http://progandy.de/contentpassword.html

But the original developer never made it compatible with joomla 3 so I just modified it to make it compatible with joomla 3 and uploading it here so that everyone can use.


The purpose of this plugin is to password protect joomla article.

To use this plugin.

Download

Install it using joomla extension installer.

Go to plugins and find this plugin and enable it.

ContentPasword is activated by a simple plug-in code contribution. The code is: {password} PARAMETERS. As a parameter, the following values ​​are possible:

pass = "password": sets a password
title = "title": sets the title of the password dialogue
desc = "description": sets the description of the dialogue
sql = "SQL query": sets a SQL query to verify the password. 
{password} is replaced with the entered password.
group = "group name". assigns the contribution of a group 
will contribute unlocked with a password of this group, all from this group are automatically accessible.
allowgroup = "Group Name": After successful password entry, all contributions to the specified group unlocked.
Pass Group = "Group Name": allows the activation of the contribution with the passwords of the specified group. The other contributions of the group will not be approved.
All the parameters except "title" and "desc" may be repeated and combined as often. The post will be the password entered by any parameter accepts, then unlocked.

Note: Blank passwords are not allowed and never lead to release.
