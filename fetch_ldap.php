<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Get a list of users from Active Directory.
 */
$ldap_password = 'sym#456789';
$ldap_username = 'symantec@mediassistindia.com';
$ldap_connection = ldap_connect('192.168.1.248');
if (FALSE === $ldap_connection){
    // Uh-oh, something is wrong...
    echo "Error in connection";
}

// We have to set this option for the version of Active Directory we are using.
ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

// Create connection
$conn = new mysqli('localhost', 'ostdbusr', 'ostdbpass', 'osticket');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
if (TRUE === ldap_bind($ldap_connection, $ldap_username, $ldap_password)){
    $ldap_base_dn = 'DC=mediassistindia,DC=com';
    $search_filter = '(&(objectCategory=person)(!(objectclass=computer))(givenname=*))'; //(objectclass=user)
    $attributes = array();
    $attributes[] = 'givenname';
    $attributes[] = 'mail';
    $attributes[] = 'samaccountname';
    $attributes[] = 'sn';
    #$attributes[] = 'type'; 
    #ldap_control_paged_result($ldap_connection, 2);
    $result = ldap_search($ldap_connection, $ldap_base_dn, $search_filter, $attributes,0,0);
    if (FALSE !== $result){
        $entries = ldap_get_entries($ldap_connection, $result);echo'<pre />';print_r($entries);
        for ($x=0; $x<$entries['count']; $x++){
            if (!empty($entries[$x]['samaccountname'][0])){ //!empty($entries[$x]['givenname'][0]) && !empty($entries[$x]['mail'][0]) &&
                //$ad_users[strtoupper(trim($entries[$x]['samaccountname'][0]))] = array('email' => strtolower(trim($entries[$x]['mail'][0])),'first_name' => trim($entries[$x]['givenname'][0]),'last_name' => trim($entries[$x]['sn'][0]));
                print($entries[$x]['samaccountname'][0]).'<br />';
                
                $userName = $entries[$x]['samaccountname'][0];
                $firstName = $entries[$x]['givenname'][0];
                $lastName = isset($entries[$x]['sn'])?$entries[$x]['samaccountname'][0]:'';
                $backend = 'ldap';
                $email = $entries[$x]['mail'][0];
                $isactive = 1;
                $isadmin = 0;
                $sql = "INSERT INTO ost_staff (group_id, dept_id,timezone_id,username,firstname,lastname,backend, email,isactive,isadmin,created)
                VALUES ('4','1','22', '$userName','$firstName','$lastName','$backend','$email','$isactive','$isadmin','now()')";
                #echo $sql;die;
                if ($conn->query($sql) === TRUE) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        }
    }
    ldap_unbind($ldap_connection); // Clean up after ourselves.
}

$message .= "Retrieved ". count($ad_users) ." Active Directory users\n";   
echo $message;
?>