a few helper scripts, need exec permissions

chmod +x script-name.sh


-- initialize locale repository in /usr/local/src/sogo-ispconfig/
sogo-ispconfig-clone.sh

-- update locale git from remote
sogo-ispconfig-pull.sh

-- update locale git from remote, and exec php update script 
sogo-ispconfig-full-update.sh

-- run php update script 
sogo-ispconfig-update.sh

--  switch branch master|testing
sogo-ispconfig-checkout-master.sh
sogo-ispconfig-checkout-testing.sh