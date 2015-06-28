sogo-ispconfig
==============
sogo-ispconfig is an attempt to integrate as much as possible of the configuration of [SOGo] into [ISPConfig]


##### [SOGo]
> SOGo is an open source groupware that offers multiple ways to access calendaring and messaging data. Your users can either use a web browser, Microsoft Outlook, Mozilla Thunderbird, Apple iCal, or a mobile device to access the same information.
> Read more at their site http://www.sogo.nu/english/about/why_use_sogo.html

##### [ISPConfig]
> ISPConfig is an all you can think of manage your servers online control panel, whether you're using multiple servers or just one, ISPconfig makes it easy, and with their large community you are only a search away from almost any setups or customization you may need, just take a look at their support forum http://www.howtoforge.com/forums/forumdisplay.php?f=23 

Installation
--------------
Simply run this, But ISPConfig version must be higher or equal to 3.0.5

```
cd /tmp
wget https://github.com/cmjnisse/sogo-ispconfig/archive/master.tar.gz -O sogo-ispconfig.tar.gz
tar -xvf sogo-ispconfig.tar.gz
cd sogo-ispconfig-master
php install.php
```

for a more indept description read [Install Latest](https://github.com/cmjnisse/sogo-ispconfig/wiki/Install-Latest), it may be a bit outdated but it will most certainly work

Upgrading from "Update 9"
you need to run `php upgrade-u9.php` but read/follow this [guide](https://github.com/cmjnisse/sogo-ispconfig/wiki/Upgrading-from-%22update-9%22)

##### Note
since the update of [ISPConfig] to version 3.0.4.5p3 the old versions of this addon is not recommended to use, but as this is your choice have a look here [Update 9]

Since i moved it all into GitHub the old links in the installer do not work any more and the only way can install [Update 9] is by getting started here [Update 9]

Once i get finished with Update 10, you will be able to use it with only the files you download no out going links, and it will feature installing SOGo, updating from earlier version etc.. just like the old installer

License
----
sogo-ispconfig is licensed under, GNU General Public License version 3

---------------------------------------

the code inculdes [Sortable] version 0.5.0 created by GitHub user [RubaXa](https://github.com/RubaXa/) and is licensed under "MIT"

[Sortable] — is a minimalist JavaScript library for reorderable drag-and-drop lists on modern browsers and touch devices. No jQuery. Support AngularJS.

Copyright 2013 Lebedev Konstantin http://rubaxa.github.io/Sortable/

---------------------------------------

[Sortable] is present in the following files

```
interface/web/admin/templates/sogo_domains_user_edit.htm
interface/web/mail/templates/sogo_domains_edit.htm
interface/web/mail/templates/sogo_domains_reseller_edit.htm
interface/web/mail/templates/sogo_domains_user_edit.htm
```


##### HELP ME

use the issue system here at GitHub for help, questions, or suggestions


[SOGo]:http://sogo.nu/
[ISPConfig]:http://www.ispconfig.org
[Update 9]:https://github.com/cmjnisse/sogo-ispconfig/wiki/Install-Update-9
[Sortable]:http://rubaxa.github.io/Sortable/
