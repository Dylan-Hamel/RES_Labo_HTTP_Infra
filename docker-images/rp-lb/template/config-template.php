<?php
  	$ip_static = getenv('STATIC_APP');
	$ip_dyn = getenv('DYNAMIC_APP');
?>

<VirtualHost *:80>
        ServerName demo.res.ch

	<Proxy balancer://mycluster>
                # Express Dynamic
                BalancerMember http://<?php print "$ip_dyn";?>/
                # Static Apache
                BalancerMember http://<?php print "$ip_static";?>

                # Security "technically we aren't blocking
                # anyone but this is the place to make
                # those changes.
                Require all granted
                # In this example all requests are allowed.

                # Load Balancer Settings
                # We will be configuring a simple Round
                # Robin style load balancer.  This means
                # that all webheads take an equal share of
                # of the load.
                ProxySet lbmethod=byrequests

        </Proxy>

        # balancer-manager
        # This tool is built into the mod_proxy_balancer
        # module and will allow you to do some simple
        # modifications to the balanced group via a gui
        # web interface.
        <Location /balancer-manager>
                SetHandler balancer-manager

                # I recommend locking this one down to your
                # your office
                Require host example.org

        </Location>

        # Point of Balance
        # This setting will allow to explicitly name the
        # the location in the site that we want to be
        # balanced, in this example we will balance "/"
        # or everything in the site.
        ProxyPass /balancer-manager !
        ProxyPass / balancer://mycluster/
	

        #ErrorLog ${APACHE_LOG_DIR}/error.log
        #CustomLog ${APACHE_LOG_DIR}/access.log combined

        ProxyPass '/api/students/' 'http://<?php print "$ip_dyn";?>/'
        ProxyPassReverse '/api/sutdents' 'http://<?php print "$ip_dyn";?>/'

        ProxyPass '/static/' 'http://<?php print "$ip_static";?>/'
        ProxyPassReverse '/static/' 'http://<?php print "$ip_static";?>/'

</VirtualHost>
