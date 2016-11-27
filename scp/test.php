<?php

\\format i used = 'microsoft.com'

ldap = ldap_connect('agni.mediassistindia.com');

//format = domain\username (ex. 'microsoft\bgates')
username='mediassistindia.com\vipin.kodakkadan';
password='Davinchi#12q';

if($bind = ldap_bind($ldap, $username,$password ))
echo 'logged in';
else
echo 'fail';
echo '<br/>done';
?>
