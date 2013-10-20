<?php

namespace PhpCli;

class System {
	/**
	 * switch to $user / $group, throws exception if it fails to do so.
	 * @param string $user valid unix user
	 * @param string $group valid unix group
	 * @throws Exception
	 */
	public static function enforceUserAndGroup($user, $group, $umask = 0022){
		$user = posix_getpwnam( $user );
		$group = posix_getgrnam( $group);
		posix_setgid($group['gid']); // set gid first, the order matters!
		posix_setuid($user['uid']);
		umask($umask); 
		if ( posix_getuid() !== $user['uid'] || posix_getgid() !== $group['gid'] ){
			return false;
		}
		return true;
	}
}

class SystemException extends \Exception {}