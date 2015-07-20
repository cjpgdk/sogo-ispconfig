#### TODO 
*****

~~@todo add check if auto create domain is allowed?~~ [removed all auto create](#todoremoved-autocreate)

~~@todo create setting to set auto create allowed or not~~ [removed all auto create](#todoremoved-autocreate)

~~@todo in multi server setups maybe use domain to SOGo server map~~ [removed all auto create](#todoremoved-autocreate)


@todo maybe allow dedicated SOGo server like ISPConfig do with web and dns, will make the auto create config more doable



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
On SOGo config insert..?
run rebuild SOGO config.. not sure if you install this and create the configs you are properply 
going over all options and checking your domains, and in the end you trigger and update that will call the rebuild
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

but i kept the todo line as a reminder to many add this later on
```