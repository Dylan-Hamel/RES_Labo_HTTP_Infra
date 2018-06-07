<?php
  	$ip_static = getenv('STATIC_APP');
	$ip_static2 = getenv('STATIC_APP2');
	$ip_dyn = getenv('DYNAMIC_APP');
	$ip_dyn2 = getenv('DYNAMIC_APP2');
?>

<VirtualHost *:80>
        ServerName demo.res.ch

	<Proxy "balancer://apache-php">
		# Static
                BalancerMember 'http://<?php print "$ip_static"?>' route=1
		# Static
                BalancerMember 'http://<?php print "$ip_static2"?>' route=2
	</Proxy>


	<Proxy "balancer://express-dyn">
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn"?>'
		# Dynamic
                BalancerMember 'http://<?php print "$ip_dyn2"?>'
        </Proxy>
	
	ProxyPass /api/students/ balancer://express-dyn
	ProxyPassReverse /api/students/ balancer://express-dyn

	Header add Set-Cookie "ROUTEID=.%{BALANCER_WORKER_ROUTE}e; path=/" env=BALANCER_ROUTE_CHANGED	

	ProxyPass / balancer://apache-php stickysession=ROUTEID
	ProxyPassReverse / balancer://apache-php

        <Location "/balancer-manager">
                SetHandler balancer-manager
                Require host demo.res.ch
        </Location>

</VirtualHost>
