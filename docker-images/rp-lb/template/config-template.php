<?php
  	$ip_static = getenv('STATIC_APP');
	$ip_static2 = getenv('STATIC_APP2');
	$ip_dyn = getenv('DYNAMIC_APP');
	$ip_dyn2 = getenv('DYNAMIC_APP2');
?>

<VirtualHost *:80>
	ProxyRequests off
        ServerName demo.res.ch

	<Proxy balancer://apache-php>
		# Static
                BalancerMember 'http://<?php print "$ip_static"?>/'
		# Static
                BalancerMember 'http://<?php print "$ip_static2"?>/'
	</Proxy>


	<Proxy balancer://express-dyn>
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn"?>/'
		# Dynamic
                BalancerMember 'http://<?php print "$ip_dyn2"?>/'
        </Proxy>


	<Location "/balancer-manager">
		SetHandler balancer-manager
		Require host demo.res.ch
	</Location>

	

        #ErrorLog ${APACHE_LOG_DIR}/error.log
        #CustomLog ${APACHE_LOG_DIR}/access.log combined

	ProxyPass "/api/students/" "balancer://express-dyn"
	ProxyPassReverse "/api/students/" "balancer://express-dyn"

	ProxyPass "/" "balancer:/apache-php"
	ProxyPassReverse "/" "balancer://apache-php"


</VirtualHost>
