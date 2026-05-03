<?php

class Session {

  private $user_id;
  public $username;
  private $last_login;
  private $roles = [];

  public const MAX_LOGIN_AGE = 60 * 60 * 24; // 1 day

  /**
   * Start the session and check for stored login data.
   */
  public function __construct() {
    session_start(); // turn on sessions if needed
    $this->check_stored_login();
  }

  /**
   * Log in a user and store their account data in the session.
   * 
   * @param User $user - user object to log in
   * 
   * @return bool true after login is handled
   */
  public function login($user) {
    if($user) {
      // prevent session fixation attacks
      session_regenerate_id();

      $this->user_id = $_SESSION['user_id'] = $user->id_usr;
      $this->username = $_SESSION['username'] = $user->username_usr;
      $this->last_login = $_SESSION['last_login'] = time();

      $this->roles = $_SESSION['roles'] = $user->get_role_names();
    }
    return true;
  }

  /**
   * Check whether a user is currently logged in.
   * 
   * @return bool true if the user is logged in
   */
  public function is_logged_in() {
    return isset($this->user_id) && $this->last_login_is_recent();
  }

  /**
   * Check whether an admin user is currently logged in.
   * 
   * @return bool true if an admin user is logged in
   */
  public function is_admin_logged_in() {
    return $this->is_logged_in() && $this->is_admin();
  }

  /**
   * Check whether a super admin user is currently logged in.
   * 
   * @return bool true if a super admin user is logged in
   */
  public function is_super_admin_logged_in() {
    return $this->is_logged_in() && $this->is_super_admin();
  }

  /**
   * Check whether the current user has an admin role.
   * 
   * @return bool true if the user is an admin
   */
  private function is_admin() {
    if($this->has_role('admin') || $this->has_role('super admin')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Check whether the current user has a super admin role.
   * 
   * @return bool true if the user is a super admin
   */
  private function is_super_admin() {
    if($this->has_role('super admin')) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Log out the current user and clear their session data.
   * 
   * @return bool true after logout is handled
   */
  public function logout() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['last_login']);
    unset($_SESSION['roles']);
    unset($this->user_id);
    unset($this->username);
    unset($this->last_login);
    unset($this->roles);
    return true;
  }

  /**
   * Get the current user's ID.
   * 
   * @return int|null current user ID or null if not logged in
   */
  public function get_user_id() {
    return $this->user_id ?? null;
  }

  /**
   * Get the current user's role names.
   * 
   * @return array current user role names
   */
  public function get_roles() {
    return $this->roles ?? [];
  }

  /**
   * Check whether the current user has a specific role.
   * 
   * @param string $role_name - role name to check
   * 
   * @return bool true if the user has the role
   */
  public function has_role($role_name) {
    $role_name = strtolower($role_name);
    $roles = array_map('strtolower', $this->get_roles());
    return in_array($role_name, $roles, true);
  }

  /**
   * Load stored login data from the session.
   */
  private function check_stored_login() {
    if(isset($_SESSION['user_id'])) {
      $this->user_id = $_SESSION['user_id'];
      $this->username = $_SESSION['username'];
      $this->last_login = $_SESSION['last_login'];
      $this->roles = $_SESSION['roles'] ?? [];
    }
  }

  /**
   * Check whether the last login time is still recent.
   * 
   * @return bool true if the last login is recent
   */
  private function last_login_is_recent() {
    if(!isset($this->last_login)) {
      return false;
    } elseif(($this->last_login + self::MAX_LOGIN_AGE) < time()) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Set or get a flash message.
   * 
   * @param string $msg - message to store
   * 
   * @return bool|string true when setting a message or stored message when getting
   */
  public function message($msg="") {
    if(!empty($msg)) {
      // Then this is a "set" message
      $_SESSION['message'] = $msg;
      return true;
    } else {
      // Then this is a "get" message
      return $_SESSION['message'] ?? '';
    }
  }

  /**
   * Clear the stored flash message.
   */
  public function clear_message() {
    unset($_SESSION['message']);
  }
}
