<?php

function pwtc_members_strip_phone_number($phoneNumber) {
    $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);
    return $phoneNumber;
}

function pwtc_members_format_phone_number($phoneNumber) {
    $phoneNumber = pwtc_members_strip_phone_number($phoneNumber);
    if (strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber)-10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);
        $phoneNumber = '+'.$countryCode.' ('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if (strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);
        $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
    }
    else if (strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);
        $phoneNumber = $nextThree.'-'.$lastFour;
    }
    return $phoneNumber;
}

function pwtc_members_is_expired($membership) {
    $is_expired = false;
    $team = false;
    if (function_exists('wc_memberships_for_teams_get_user_membership_team')) {
        $team = wc_memberships_for_teams_get_user_membership_team($membership->get_id());
    }
    if ($team) {
        if ($team->is_membership_expired()) {
            $is_expired = true;
        }
    }
    else {
        if ($membership->is_expired()) {
            $is_expired = true;
        }
    }
    return $is_expired;
}

function pwtc_members_get_expiration_date($membership) {
    $team = false;
    if (function_exists('wc_memberships_for_teams_get_user_membership_team')) {
        $team = wc_memberships_for_teams_get_user_membership_team($membership->get_id());
    }
    if ($team) {
        $datetime = $team->get_local_membership_end_date('mysql');
        $pieces = explode(' ', $datetime);
        $exp_date = $pieces[0];
    }
    else {
        if ($membership->has_end_date()) {
            $datetime = $membership->get_local_end_date('mysql', false);
            $pieces = explode(' ', $datetime);
            $exp_date = $pieces[0];
        }
        else {
            $exp_date = '2099-01-01';
        }
    }
    return $exp_date;
}

function pwtc_members_lookup_user($rider_id, $lastname = '', $firstname = '', $email = '', $exact = true) {
    $compare = 'LIKE';
    if ($exact) {
        $compare = '=';
    }
    $query_args = [
        'meta_key' => 'last_name',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ];
    $query_args['meta_query'] = [];
    if (!empty($lastname)) {
        $query_args['meta_query'][] = [
            'key'     => 'last_name',
            'value'   => $lastname,
            'compare' => $compare   
        ];
    }
    if (!empty($firstname)) {
        $query_args['meta_query'][] = [
            'key'     => 'first_name',
            'value'   => $firstname,
            'compare' => $compare 
        ];
    }
    if (!empty($email)) {
        $str = $email;
        if (!$exact) {
            $str = '*' . $str . '*';
        }
        $query_args['search'] = $str;
        $query_args['search_columns'] = array( 'user_email' );
    }
    if (!empty($rider_id)) {
        $query_args['meta_query'][] = [
            'key'     => 'rider_id',
            'value'   => $rider_id,
            'compare' => $compare 
        ];
    }
    /*
    else if (empty($lastname) and empty($firstname)) {
        $query_args['meta_query'][] = [
            'relation' => 'OR',
            [
                'key'     => 'rider_id',
                'compare' => 'NOT EXISTS' 
            ],
            [
                'key'     => 'rider_id',
                'value'   => ''    
            ] 
        ];
    }
    */
    $user_query = new WP_User_Query( $query_args );
    $results = $user_query->get_results();
    return $results;
}

function pwtc_members_adjust_start_date($user_membership, $detect_only=false) {
    $user_id = $user_membership->get_user_id();
    $rider_id = get_field('rider_id', 'user_'.$user_id);
	if (!$rider_id or empty(trim($rider_id))) {
		return false;
	}
	if (preg_match('/^\d{5}$/', $rider_id) === 1) {
		$y = intval(substr($rider_id, 0, 2));
		$c = intval(substr(date('Y', current_time('timestamp')), 0, 2));
		if ($y > 50) {
			$year = strval((($c - 1) * 100) + $y);
		}
		else {
			$year = strval(($c * 100) + $y);
		}
		$start = $user_membership->get_start_date();
		if ($start and strncmp($start, $year, 4) !== 0) {
            if (!$detect_only) {
			    $user_membership->set_start_date($year . '-07-01 12:00:00');
			    $user_membership->add_note('PWTC Members plugin modified start date to match rider ID year.');
			    //$user_membership->add_note('PWTC Members plugin modified start date to match rider ID year. startdate=' . $start . ', riderid=' . $rider_id . ', rideryear=' . $year);
            }
            return true;
		}
	}
    return false;
}


	function pwtc_members_sync_team_member_end_times($team, $detect_only=false, $userid=0) {
		$count = 0;
		$now = current_time('timestamp', true);
		$team_end = $team->get_membership_end_date('timestamp');
		foreach ($team->get_user_memberships() as $user_membership) {
			if ($userid === 0 or $user_membership->get_user_id() === $userid) {
				$end = $user_membership->get_end_date('timestamp');
				if ($team_end and $end) {
					$diff = abs($end - $team_end);
					if ($diff > 86400) {
						$count++;
						if (!$detect_only) {
							$user_membership->set_end_date($team_end);
							$user_membership->add_note('PWTC Members plugin synced member end time with team end time.');
							if ($team_end > $now and $user_membership->is_expired()) {
								$user_membership->update_status( 'active' );
							} elseif ($team_end <= $now) {
								$user_membership->update_status( 'expired' );
							}
						}
					}
				}
			}
		}
		return $count;
	}
