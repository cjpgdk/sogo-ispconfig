NOTE* this is just a set of commands but here for my own reference

add `deb-src http://inverse.ca/debian/ jessie jessie` to apt source list

import key `sudo apt-key adv --keyserver keys.gnupg.net --recv-key 0x810273C4`

`apt-get update`

`apt-get upgrade`

```
##############################
# Build ""SOPE""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/sope
cd /usr/local/src/sope

# build-dep will fetch and install the required packages for you
# and tell you what packages are not available via apt-get
apt-get build-dep sope

apt-get source sope

cd sope-4.9*

dch --local ~i386deb8+ --distribution testing "Build SOPE for Debian jessie i386"

cd ../sope-4.9*

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
	## you see a long list of the newly built packages.., install the required packages
```


```
##############################
# Build ""libwbxml2""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/libwbxml2
cd /usr/local/src/libwbxml2

# build-dep will fetch and install the required packages for you
# and tell you what packages are not available via apt-get
apt-get build-dep libwbxml2-dev

apt-get source libwbxml2-dev

cd libwbxml2*

dch --local ~i386deb8+ --distribution testing "Build libwbxml2 for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
	## you see a list of the newly built packages.., install the required packages
```

```
##############################
# Build ""talloc""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/talloc
cd /usr/local/src/talloc

apt-get build-dep talloc

apt-get source talloc

dch --local ~i386deb8+ --distribution testing "Build talloc for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
    ## you see a list of the newly built packages.., install the required packages
```

```
##############################
# Build ""python-rpclib""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/python-rpclib
cd /usr/local/src/python-rpclib

apt-get build-dep python-rpclib

apt-get source python-rpclib

dch --local ~i386deb8+ --distribution testing "Build python-rpclib for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
    ## you see a list of the newly built packages.., install the required packages
```

```
##############################
# Build ""python-sievelib""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/python-sievelib
cd /usr/local/src/python-sievelib

apt-get build-dep python-sievelib

apt-get source python-sievelib

dch --local ~i386deb8+ --distribution testing "Build python-sievelib for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
    ## you see a list of the newly built packages.., install the required packages
```

```
##############################
# Build ""samba""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/samba
cd /usr/local/src/samba

apt-get build-dep samba

apt-get source samba

dch --local ~i386deb8+ --distribution testing "Build samba for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
    ## you see a list of the newly built packages.., install the required packages
```

```
##############################
# Build ""openchange""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/openchange
cd /usr/local/src/openchange

# build-dep will fetch and install the required packages for you
# and tell you what packages are not available via apt-get
apt-get build-dep openchange

apt-get source openchange

cd openchange*

dch --local ~i386deb8+ --distribution testing "Build openchange for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
	## you see a list of the newly built packages.., install the required packages
	
	# you must install the following packages
	# libmapi0 
	# libmapi-dev
	# libmapiproxy0
	# libmapiproxy-dev
	# libmapistore0
	# libmapistore-dev
```

```
##############################
# Build ""SOGo""
##############################

# you only need to do this once
apt-get install dh-make debhelper devscripts fakeroot build-essential binutils dpkg-dev

mkdir -p /usr/local/src/sogo
cd /usr/local/src/sogo

# build-dep will fetch and install the required packages for you
# and tell you what packages are not available via apt-get
# if you forgot to install some of the required SOPE packages you will be reminded here
# **NOTE**
#     i use the current version number of sogo, 
#     this way the build-dep command check and install packages 
#     to use for the sogo version we are building.
apt-get build-dep sogo=2.3.0-1

apt-get source sogo

dch --local ~i386deb8+ --distribution testing "Build SOGo for Debian jessie i386"

dpkg-buildpackage -us -uc -rfakeroot -d -D

ls -la ../
	## you see a list of the newly built packages.., install the required packages
```