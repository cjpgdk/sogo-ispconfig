#### TODO 
*****

~~@todo add check if auto create domain is allowed?~~ [removed all auto create](#todoremoved-autocreate)

~~@todo create setting to set auto create allowed or not~~ [removed all auto create](#todoremoved-autocreate)

~~@todo in multi server setups maybe use domain to SOGo server map~~ [removed all auto create](#todoremoved-autocreate)

@todo maybe allow dedicated SOGo server like ISPConfig do with web and dns, will make the auto create config more doable

@todo sogo_plugin::onLoad(): 
        add a more reliable configuration check

@todo sogo_plugin::update_sogo_mail_user_alias(): 
        if destination changes add function to double check the alias counts on table and in ISPConfig

@todo sogo_plugin::update_sogo_mail_user_alias(): 
        might need to add some restriction to this method to prevent constenly updating the db.
        make single query to remove aliases (this lightens the load and the amount of sql queries).

@todo sogo_plugin::insert_sogo_mail_user_alias() #36
        this method should check if the destination email address is allowed imap access and if 
        the mail domain table exists before trying to create the new alias column.

@todo sogo_plugin::remove_sogo_mail_user_alias() #35
        this method should check the old array and not the new array. :angry:,
        but check new if old is empty. currently only works if called from remote actions!

@todo sync mail users when user updates 'Name' or 'Disable IMAP' #34
        missing sync mail users if a mail user updates the 'Name' or 'Disable IMAP' field of the email address

@todo sync mail users if config change #33
        Sync mail users if IMAP Server, SMTP Server and / or Sieve Server is change in the SOGo sever configuration.

@todo just a thought on Address Book #19
        maybe add this as an option to enable or disable this, makes it easy to se what users exists in a domain
        also if using aliases searching for the alias name will bring up the user whom this alias belongs to



#### TODO list with low very low priority
*****

###### install sql permissions when installing on none master.
```
it will require some if not alot of testing to avoid killing ispconfig default permissions.
so this is for the future
```



#### TODO list with a maybe i will add/fix it
*****

```
Nothing here!
```



##### <a name="todoremoved-autocreate"></a>removed all auto create
*****

```
I removed all auto create of SOGo configurations for domains
this means that an administrator (with access to admin module)
must create the config before user's/reseller's can edit it from
the mail module

this is done to better allow multi server configuration where
SOGo server IS NOT installed on the mail server

but i kept the todo line as a reminder to maybe add this later on
```