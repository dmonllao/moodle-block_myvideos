<?php // $Id: access.php,v 1.2 2010/07/06 07:33:36 davmon Exp $


$block_myvideos_capabilities = array(

    'block/myvideos:manage' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),

    'block/myvideos:uploadvideo' => array(
        'riskbitmask' => RISK_SPAM,
    
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),

    'block/myvideos:linkvideo' => array(
        'riskbitmask' => RISK_SPAM,
    
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),
 
    'block/myvideos:favoritevideo' => array(
    
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),
       
    'block/myvideos:selectquality' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    ),

    'block/myvideos:publicvideo' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'admin' => CAP_ALLOW
        )
    )

);

?>
