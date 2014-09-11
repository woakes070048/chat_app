<?php
class Profile extends Model {
	public function queryid($user) {
		$stmt = $this->_dbh->prepare("SELECT user_id FROM auths WHERE username = :username");
		$stmt->bindParam(':username',$user, PDO::PARAM_STR, 32);

		$stmt->execute();
		$count = $stmt->rowCount();
		if ($count != 1)
		{ 
			return "Invalid username";
		}
		else 
		{
			$uid = $stmt->fetchColumn();
			return $uid;
		}
}


public function queryfriend($userid) {

		 $stmt = $this->_dbh->prepare("SELECT f_id, r_id FROM friends WHERE u_id = :userid");
		 $stmt->bindParam(':userid',$userid, PDO::PARAM_INT);
		 $stmt->execute();

		 $friends1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

		 $stmt = $this->_dbh->prepare("SELECT u_id, r_id FROM friends WHERE f_id = :uid");
		 $stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
		 $stmt->execute();
		 $friends2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

	         $stmt = $this->_dbh->prepare("SELECT user_id, username, dir_path, profilePic, status FROM auths WHERE user_id = :userid");

		 $friendsname = array();
		 $friendsname[0] = $friends1;
		 $friendsname[1] = $friends2;
		 $i=2;
		 foreach ($friends1 as $friend1) {
		 $stmt->bindParam(':userid', $friend1['f_id'] ,PDO::PARAM_INT);                    
		 $stmt->execute();
		 $friendsname[$i] = $stmt->fetch(PDO::FETCH_ASSOC);
		 $i++;
		  }
		

		 foreach($friends2 as $friend2) {
			 $stmt->bindParam(':userid', $friend2['u_id'], PDO::PARAM_INT);
			 $stmt->execute();
			 $friendsname[$i] = $stmt->fetch(PDO::FETCH_ASSOC);
			 $i++;
		 }
		 return $friendsname;
        }       

public function queryName($uid) {
		
		$stmt = $this->_dbh->prepare("SELECT username FROM auths WHERE user_id = :userid");

		$stmt->bindParam(':userid',$uid, PDO::PARAM_STR, 32);
		$stmt->execute();
		$username = $stmt->fetchColumn();
		return $username;

}

public function queryDir($uid) {
	$stmt = $this->_dbh->prepare("SELECT dir_path FROM auths WHERE user_id= :userid");
	$stmt->bindParam(':userid', $uid, PDO::PARAM_INT);

	$stmt->execute();
	$userdir = $stmt->fetchColumn();
	return $userdir;
}

public function updateProfilePic($name,$uid) {
	$stmt = $this->_dbh->prepare("UPDATE auths SET profilePic = :name WHERE user_id = :userid");
	$stmt->bindParam(':name', $name, PDO::PARAM_STR, 32);
	$stmt->bindParam(':userid', $uid, PDO::PARAM_INT);

	$stmt->execute();

}


public function queryProfPic($uid) {
	$stmt = $this->_dbh->prepare("SELECT profilePic FROM auths WHERE user_id = :userid");
	$stmt->bindParam(':userid', $uid, PDO::PARAM_INT);

	$stmt->execute();
	$image = $stmt->fetchColumn();
	return $image;
}


public function getMessages($rid) {
	$stmt = $this->_dbh->prepare("SELECT msg, u_id, timestmp FROM chat_hist WHERE r_id = :rid");
	$stmt->bindParam(':rid', $rid, PDO::PARAM_INT);

	$stmt->execute();
	$msg = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $msg;
}


public function submitMessage($msg, $rid, $uid) {
        $date = date('Y-m-d H:i:s');
	$stmt = $this->_dbh->prepare("INSERT INTO chat_hist(r_id, msg, u_id, timestmp) VALUES (:rid, :msg, :uid, :date)");
	$stmt->bindParam(':rid', $rid, PDO::PARAM_INT);
	$stmt->bindParam(':msg', $msg, PDO::PARAM_STR, 50);
	$stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
	$stmt->bindParam(':date', $date, PDO::PARAM_STR);

	$stmt->execute();

}

public function queryRID($rid) {
	$stmt = $this->_dbh->prepare("SELECT u_id, f_id FROM friends WHERE r_id = :rid");
	$stmt->bindParam(':rid', $rid);

	$stmt->execute();

	$ids = $stmt->fetch(PDO::FETCH_ASSOC);
	return $ids;
}

public function searchFriend($search, $uid) {
	$search1 = "%".$search."%";
	$stmt = $this->_dbh->prepare("SELECT user_id, username, dir_path, profilePic FROM auths WHERE username LIKE :user");
	$stmt->bindParam(':user', $search1, PDO::PARAM_STR);

	$stmt->execute();

	$s_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$f_ids = $this->queryfriend($uid);
	array_shift($f_ids);
	array_shift($f_ids);
	$friend = array();
	$i = 0;

	for(reset($s_ids); key($s_ids) !== null; next($s_ids)) {
		
		$s_id = current($s_ids);	
		if ($s_id['user_id'] == $uid){
			$id = key($s_ids);
			unset($s_ids["$id"]);
			}
		


		}
	
  
	 for(reset($s_ids); key($s_ids) !== null; next($s_ids)) {		

		 $s_id = current($s_ids);
		 foreach($f_ids as $f_id) {
			 if($s_id['user_id'] == $f_id['user_id']){
				 $id = key($s_ids);
				 $friend[$i++] = $s_ids["$id"];
				 $ids[] = $id;
			 }
		 } 

	 }	
	if(isset($ids) && !empty($ids)) {
	foreach($ids as $id1) {
		unset($s_ids[$id1]);
	}
	}
	$sentRequests = $this->queryRequest($uid);

	$i = 0;

	for(reset($s_ids); key($s_ids)!==null; next($s_ids)) {
		$s_id = current($s_ids);

		foreach($sentRequests as $sentRequest) {
			if($s_id['user_id'] == $sentRequest['f_id']){
				$sentid = key($s_ids);
				$sent[$i++] = $s_ids["$sentid"];
				$sentids[] = $sentid;
			}
		}
	}
		
		if(isset($sentids) && !empty($sentids)) {
		foreach($sentids as $id2) {
			unset($s_ids[$id2]);
		}
		}


	$sentRequest = $this->queryRecvRequest($uid);

	$i = 0;

	for(reset($s_ids); key($s_ids)!==null; next($s_ids)) {
		$s_id = current($s_ids);

		foreach($sentRequest as $sentRquest) {
			if($s_id['user_id'] == $sentRquest['u_id']){
				$sentid = key($s_ids);
				$sent[$i++] = $s_ids["$sentid"];
				$sentids[] = $sentid;
			}
		}
	}
		
		if(isset($sentids) && !empty($sentids)) {
		foreach($sentids as $id2) {
			unset($s_ids[$id2]);
		}
		}

	if(isset($sent) && !empty($sent)) {
		$s_ids['suggestions'] = $sent;
	}
	$s_ids['friends'] = $friend;
	return $s_ids;
}

	
	public function unFriend($fid, $uid) {

		$stmt = $this->_dbh->prepare("DELETE FROM friends WHERE u_id = :uid AND f_id = :fid");
		$stmt->bindParam(':uid', $uid);
		$stmt->bindParam(':fid', $fid);

		$stmt->execute();

		$stmt = $this->_dbh->prepare("DELETE FROM friends WHERE u_id = :fid AND f_id = :uid");
		$stmt->bindParam(':uid', $uid);
		$stmt->bindParam(':fid', $fid);

		$stmt->execute();
	}


	public function sendRequest($uid, $fid) {
		$stmt = $this->_dbh->prepare("INSERT INTO friend_req(u_id, f_id) VALUES(:uid, :fid)");
		$stmt->bindParam(':uid',$uid);
		$stmt->bindParam(':fid', $fid);
		$stmt->execute();
}

	public function acceptRequest($uid, $fid) {
	
		$stmt = $this->_dbh->prepare("INSERT INTO friends(u_id, f_id) VALUES(:uid, :fid)");
		$stmt->bindParam(':uid', $uid);
		$stmt->bindParam(':fid', $fid);
		$stmt->execute();	


		$stmt = $this->_dbh->prepare("DELETE FROM friend_req WHERE u_id = :uid AND f_id = :fid");
		$stmt->bindParam(':uid', $uid);
		$stmt->bindParam(':fid', $fid);
		$stmt->execute();
} 


	public function queryRequest($uid) {
		$stmt = $this->_dbh->prepare("SELECT f_id FROM friend_req WHERE u_id = :uid");
		$stmt->bindParam(':uid', $uid);
		$stmt->execute();

		$rids = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $rids;
	}


	public function queryRequestDetails($requestArray) {
		$i = 0;
		$stmt = $this->_dbh->prepare("SELECT user_id, username, profilePic, dir_path FROM auths WHERE user_id = :uid");
		foreach($requestArray as $request){
		$stmt->bindParam(':uid', $request['f_id']);
		$stmt->execute();

		$result[$i++] = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		return $result;
	
}	

	public function queryRecvRequest($uid) {
		$stmt = $this->_dbh->prepare("SELECT u_id FROM friend_req WHERE f_id = :uid");
		$stmt->bindParam(':uid', $uid);
		$stmt->execute();

		$sugIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $sugIds;

}

	public function queryRecdRequestDetails($recdRequests) {
		$i = 0;
		$stmt = $this->_dbh->prepare("SELECT user_id, username, profilePic, dir_path FROM auths WHERE user_id = :uid");
		foreach($recdRequests as $recdRequest) {
			$stmt->bindParam(':uid', $recdRequest['u_id']);
			$stmt->execute();

			$results[$i++] = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		return $results;

}
		
}
