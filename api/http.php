<?php
/*********************************************************************
    http.php

    HTTP controller for the osTicket API

    Jared Hancock
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require 'api.inc.php';
# Include the main api urls
require_once INCLUDE_DIR."class.dispatcher.php";
$dispatcher = patterns('',
        /*
        url_post("^/tickets\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','create')),
        url('^/tasks/', patterns('',
                url_post("^cron$", array('api.cron.php:CronApiController', 'execute'))
         ))
        */

        // Get help topic info using parameter (help topic id)
        url_get('^/tickets/helpTopicInfo$', array('api.tickets.php:TicketApiController', 'getHelpTopicInfo')),

        // Get public sub-departments using parameter (parent department id)
        url_get('^/tickets/publicChildDepartments$', array('api.tickets.php:TicketApiController', 'getPublicChildDepartments')),

        // Get department help topics using two parameters (department id and includeParentsHelpTopics)
        url_get('^/tickets/departmentHelpTopics$', array('api.tickets.php:TicketApiController', 'getDepartmentHelpTopics'))

        );

// Send api signal so backend can register endpoints
Signal::send('api', $dispatcher);
# Call the respective function
print $dispatcher->resolve(Osticket::get_path_info());
?>
